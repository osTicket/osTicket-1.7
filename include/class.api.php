<?php
/*********************************************************************
    class.api.php

    API

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
class API {

    var $id;

    var $ht;

    function API($id) {
        $this->id = 0;
        $this->load($id);
    }

    function load($id=0) {

        if(!$id && !($id=$this->getId()))
            return false;

        $sql='SELECT * FROM '.API_KEY_TABLE.' WHERE id='.db_input($id);
        if(!($res=db_query($sql)) || !db_num_rows($res))
            return false;

        $this->ht = db_fetch_array($res);
        $this->id = $this->ht['id'];

        return true;
    }

    function reload() {
        return $this->load();
    }

    function getId() {
        return $this->id;
    }

    function getKey() {
        return $this->ht['apikey'];
    }

    function getIPAddr() {
        return $this->ht['ipaddr'];
    }

    function getNotes() {
        return $this->ht['notes'];
    }

    function getHashtable() {
        return $this->ht;
    }

    function isActive() {
        return ($this->ht['isactive']);
    }

    function canCreateTickets() {
        return ($this->ht['can_create_tickets']);
    }

    function canExecuteCron() {
        return ($this->ht['can_exec_cron']);
    }

    function update($vars, &$errors) {

        if(!API::save($this->getId(), $vars, $errors))
            return false;

        $this->reload();

        return true;
    }

    function delete() {
        $sql='DELETE FROM '.API_KEY_TABLE.' WHERE id='.db_input($this->getId()).' LIMIT 1';
        return (db_query($sql) && ($num=db_affected_rows()));
    }

    /** Static functions **/
    function add($vars, &$errors) {
        return API::save(0, $vars, $errors);
    }

    function validate($key, $ip) {
        return ($key && $ip && self::getIdByKey($key, $ip));
    }

    function getIdByKey($key, $ip='') {

        $sql='SELECT id FROM '.API_KEY_TABLE.' WHERE apikey='.db_input($key);
        if($ip)
            $sql.=' AND ipaddr='.db_input($ip);

        if(($res=db_query($sql)) && db_num_rows($res))
            list($id) = db_fetch_row($res);

        return $id;
    }

    function lookupByKey($key, $ip='') {
        return self::lookup(self::getIdByKey($key, $ip));
    }

    function lookup($id) {
        return ($id && is_numeric($id) && ($k= new API($id)) && $k->getId()==$id)?$k:null;
    }

    function save($id, $vars, &$errors) {

        if(!$id && (!$vars['ipaddr'] || !Validator::is_ip($vars['ipaddr'])))
            $errors['ipaddr'] = 'Valid IP required';

        if($errors) return false;

        $sql=' updated=NOW() '
            .',isactive='.db_input($vars['isactive'])
            .',can_create_tickets='.db_input($vars['can_create_tickets'])
            .',can_exec_cron='.db_input($vars['can_exec_cron'])
            .',notes='.db_input($vars['notes']);

        if($id) {
            $sql='UPDATE '.API_KEY_TABLE.' SET '.$sql.' WHERE id='.db_input($id);
            if(db_query($sql))
                return true;

            $errors['err']='Unable to update API key. Internal error occurred';

        } else {
            $sql='INSERT INTO '.API_KEY_TABLE.' SET '.$sql
                .',created=NOW() '
                .',ipaddr='.db_input($vars['ipaddr'])
                .',apikey='.db_input(strtoupper(md5(time().$vars['ipaddr'].md5(Misc::randcode(16)))));

            if(db_query($sql) && ($id=db_insert_id()))
                return $id;

            $errors['err']='Unable to add API key. Try again!';
        }

        return false;
    }
}

/**
 * Controller for API methods. Provides methods to check to make sure the
 * API key was sent and that the Client-IP and API-Key have been registered
 * in the database, and methods for parsing and validating data sent in the
 * API request.
 */

class ApiController {

    var $apikey;

    function requireApiKey() {
        # Validate the API key -- required to be sent via the X-API-Key
        # header

        if(!($key=$this->getApiKey()))
            return $this->exerr(401, 'Valid API key required');
        elseif (!$key->isActive() || $key->getIPAddr()!=$_SERVER['REMOTE_ADDR'])
            return $this->exerr(401, 'API key not found/active or source IP not authorized');

        return $key;
    }

    function getApiKey() {

        if (!$this->apikey && isset($_SERVER['HTTP_X_API_KEY']) && isset($_SERVER['REMOTE_ADDR']))
            $this->apikey = API::lookupByKey($_SERVER['HTTP_X_API_KEY'], $_SERVER['REMOTE_ADDR']);

        return $this->apikey;
    }

    /**
     * Retrieves the body of the API request and converts it to a common
     * hashtable. For JSON formats, this is mostly a noop, the conversion
     * work will be done for XML requests
     */
    function getRequest($format) {
        global $ost;

        $input = $ost->is_cli()?'php://stdin':'php://input';

        if (!($stream = @fopen($input, 'r')))
            $this->exerr(400, "Unable to read request body");

        $parser = null;
        switch(strtolower($format)) {
            case 'xml':
                if (!function_exists('xml_parser_create'))
                    $this->exerr(501, 'XML extension not supported');

                $parser = new ApiXmlDataParser();
                break;
            case 'json':
                $parser = new ApiJsonDataParser();
                break;
            case 'email':
                $parser = new ApiEmailDataParser();
                break;
            default:
                $this->exerr(415, 'Unsupported data format');
        }

        if (!($data = $parser->parse($stream)))
            $this->exerr(400, $parser->lastError());

        //Validate structure of the request.
        $this->validate($data, $format);

        return $data;
    }

    function getEmailRequest() {
        return $this->getRequest('email');
    }


    /**
     * Structure to validate the request against -- must be overridden to be
     * useful
     */
    function getRequestStructure($format) { return array(); }
    /**
     * Simple validation that makes sure the keys of a parsed request are
     * expected. It is assumed that the functions actually implementing the
     * API will further validate the contents of the request
     */
    function validateRequestStructure($data, $structure, $prefix="") {

        foreach ($data as $key=>$info) {
            if (is_array($structure) and is_array($info)) {
                $search = (isset($structure[$key]) && !is_numeric($key)) ? $key : "*";
                if (isset($structure[$search])) {
                    $this->validateRequestStructure($info, $structure[$search], "$prefix$key/");
                    continue;
                }
            } elseif (in_array($key, $structure)) {
                continue;
            }
            return $this->exerr(400, "$prefix$key: Unexpected data received");
        }

        return true;
    }

    /**
     * Validate request.
     *
     */
    function validate(&$data, $format) {
        return $this->validateRequestStructure(
                $data,
                $this->getRequestStructure($format)
                );
    }

    /**
     * API error & logging and response!
     *
     */

    /* If possible - DO NOT - overwrite the method downstream */
    function exerr($code, $error='') {
        global $ost;

        if($error && is_array($error))
            $error = Format::array_implode(": ", "\n", $error);

        //Log the error as a warning - include api key if available.
        $msg = $error;
        if($_SERVER['HTTP_X_API_KEY'])
            $msg.="\n*[".$_SERVER['HTTP_X_API_KEY']."]*\n";
        $ost->logWarning("API Error ($code)", $msg, false);

        $this->response($code, $error); //Responder should exit...
        return false;
    }

    //Default response method - can be overwritten in subclasses.
    function response($code, $resp) {
        Http::response($code, $resp);
        exit();
    }
}

include_once "class.xml.php";
class ApiXmlDataParser extends XmlDataParser {

    function parse($stream) {
        return $this->fixup(parent::parse($stream));
    }
    /**
     * Perform simple operations to make data consistent between JSON and
     * XML data types
     */
    function fixup($current) {

        if($current['ticket'])
            $current = $current['ticket'];

        if (!is_array($current))
            return $current;
        foreach ($current as $key=>&$value) {
            if ($key == "phone") {
                $current["phone_ext"] = $value["ext"];  # PHP [like] point
                $value = $value[":text"];
            } else if ($key == "alert") {
                $value = (bool)$value;
            } else if ($key == "autorespond") {
                $value = (bool)$value;
            } else if ($key == "attachments") {
                if(!isset($value['file'][':text']))
                    $value = $value['file'];

                if($value && is_array($value)) {
                    foreach ($value as &$info) {
                        $info["data"] = $info[":text"];
                        unset($info[":text"]);
                    }
                    unset($info);
                }
            } else if(is_array($value)) {
                $value = $this->fixup($value);
            }
        }

        return $current;
    }
}

include_once "class.json.php";
class ApiJsonDataParser extends JsonDataParser {
    function parse($stream) {
        return $this->fixup(parent::parse($stream));
    }
    function fixup($current) {
        if (!is_array($current))
            return $current;
        foreach ($current as $key=>&$value) {
            if ($key == "phone") {
                list($value, $current["phone_ext"])
                    = explode("X", strtoupper($value), 2);
            } else if ($key == "alert") {
                $value = (bool)$value;
            } else if ($key == "autorespond") {
                $value = (bool)$value;
            } else if ($key == "attachments") {
                foreach ($value as &$info) {
                    $data = reset($info);
                    # PHP5: fopen("data://$data[5:]");
                    if (substr($data, 0, 5) != "data:") {
                        $info = array(
                            "data" => $data,
                            "type" => "text/plain",
                            "name" => key($info));
                    } else {
                        $data = substr($data,5);
                        list($meta, $contents) = explode(",", $data);
                        list($type, $extra) = explode(";", $meta);
                        $info = array(
                            "data" => $contents,
                            "type" => ($type) ? $type : "text/plain",
                            "name" => key($info));
                        # XXX: Handle decoding here??
                        if (substr($extra, -6) == "base64")
                            $info["encoding"] = "base64";
                        # Handle 'charset' hint in $extra, such as
                        # data:text/plain;charset=iso-8859-1,Blah
                        # Convert to utf-8 since it's the encoding scheme
                        # for the database. Otherwise, assume utf-8
                        list($param,$charset) = explode('=', $extra);
                        if ($param == 'charset' && $charset)
                            $contents = Format::utf8encode($contents, $charset);
                    }
                }
                unset($value);
            }
            if (is_array($value)) {
                $value = $this->fixup($value);
            }
        }
        return $current;
    }
}

/* Email parsing */
include_once "class.mailparse.php";
class ApiEmailDataParser extends EmailDataParser {

    function parse($stream) {
        return $this->fixup(parent::parse($stream));
    }

    function fixup($data) {
        global $cfg;

        if(!$data) return $data;

        $data['source'] = 'Email';

        if(!$data['message'])
            $data['message'] = $data['subject']?$data['subject']:'(EMPTY)';

        if(!$data['subject'])
            $data['subject'] = '[No Subject]';

        if(!$data['emailId'])
            $data['emailId'] = $cfg->getDefaultEmailId();

        if($data['email'] && preg_match ('[[#][0-9]{1,10}]', $data['subject'], $matches)) {
            if(($tid=Ticket::getIdByExtId(trim(preg_replace('/[^0-9]/', '', $matches[0])), $data['email'])))
                $data['ticketId'] = $tid;
        }

        if(!$cfg->useEmailPriority())
            unset($data['priorityId']);

        return $data;
    }
}
?>
