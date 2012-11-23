<?php

require_once("class.dynamic_forms.php");

class DynamicFormsAjaxAPI extends AjaxController {
    function getForm($form_id) {
        $form = DynamicForm::lookup($form_id);
        if (!$form) return;

        foreach ($form->getFields() as $field) {
            $field->render();
        }
    }

    function getFormsForHelpTopic($topic_id) {
        foreach (HelpTopicDynamicForm::forTopic($topic_id) as $form) {
            $form=$form->getForm();
            include(STAFFINC_DIR . 'templates/dynamic-form.tmpl.php');
        }
    }
}

?>
