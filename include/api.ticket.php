<?php

include_once "include/class.api.php";
include_once "include/class.ticket.php";

class TicketController extends ApiController {

    # Supported arguments -- anything else is an error. These items will be
    # inspected _after_ the fixup() method of the ApiXxxDataParser classes
    # so that all supported input formats should be supported
    function getRequestStructure($format, $data=null) {
        $supported = array(
            "alert", "autorespond", "source", "topicId",
            "phone", "phone_ext",
            "attachments" => array("*" => 
                array("name", "type", "data", "encoding")
            ), 
            "message", "ip", "priorityId"
        );
        # Fetch dynamic form field names for the given help topic and add
        # the names to the supported request structure
        if (isset($data['topicId'])) {
            $topic=Topic::lookup($data['topicId']);
            $formset=DynamicFormset::lookup($topic->ht['formset_id']);
            foreach ($formset->getForms() as $form)
                foreach ($form->getForm()->getFields() as $field)
                    $supported[] = $field->get('name');
        }

        if ($format == "xml") return array("ticket" => $supported);
        else return $supported;
    }

    function create($format) {
        $this->requireApiKey();

        # Parse request body
        $data = $this->getRequest($format);
        if ($format == "xml") $data = $data["ticket"];

        # Pull off some meta-data
        $alert = $data['alert'] ? $data['alert'] : true; 
        $autorespond = $data['autorespond'] ? $data['autorespond'] : true;
        $source = $data['source'] ? $data['source'] : 'API';

        $attachments = $data['attachments'] ? $data['attachments'] : array();

		# TODO: Handle attachment encoding (base64)
        foreach ($attachments as $filename=>&$info) {
            if ($info["encoding"] == "base64") {
                # XXX: May fail on large inputs. See
                #      http://us.php.net/manual/en/function.base64-decode.php#105512
                if (!($info["data"] = base64_decode($info["data"], true)))
                    Http::response(400, sprintf(
                        "%s: Poorly encoded base64 data",
                        $info['name']));
            }
            $info['size'] = strlen($info['data']);
        }

        # Create the ticket with the data (attempt to anyway)
        $errors = array();

        $topic=Topic::lookup($data['topicId']);
        $forms=DynamicFormset::lookup($topic->ht['formset_id'])->getForms();
        foreach ($forms as $idx=>$f) {
            $forms[$idx] = $form = $f->getForm()->instanciate();
            $form->set('sort', $f->get('sort'));
            # Collect name, email, and subject address for banning and such
            foreach ($form->getFields() as $field) {
                $fname = $field->get('name');
                if (isset($data[$fname]))
                    $field->value = $data[$fname];
            }
            if (!$form->isValid())
                $errors = array_merge($errors, $form->errors());
        }

        $ticket = Ticket::create($data, $errors, $source, $autorespond, 
            $alert);

        # Return errors (?)
        if (count($errors)) {
            Http::response(400, "Unable to create new ticket: validation errors:\n"
                . Format::array_implode(": ", "\n", $errors));
        } elseif (!$ticket) {
            Http::response(500, "Unable to create new ticket: unknown error");
        }

        # Save dynamic forms
        foreach ($forms as $f) {
            $f->set('ticket_id', $ticket->getId());
            $f->save();
        }

        # Save attachment(s)
        foreach ($attachments as &$info)
            $ticket->saveAttachment($info, $ticket->getLastMsgId(), "M");

        # All done. Return HTTP/201 --> Created
        Http::response(201, $ticket->getExtId());
    }
}

?>
