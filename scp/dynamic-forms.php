<?php
require('admin.inc.php');
require_once(INCLUDE_DIR."/class.dynamic_forms.php");

$form=null;
if($_REQUEST['id'] && !($form=DynamicForm::lookup($_REQUEST['id'])))
    $errors['err']='Unknown or invalid dynamic form ID.';

if($_POST) {
    $fields = array('title', 'notes');
    switch(strtolower($_POST['do'])) {
        case 'update':
            foreach ($fields as $f)
                if (isset($_POST[$f]))
                    $form->set($f, $_POST[$f]);
            if ($form->isValid())
                $form->save();
            foreach ($form->getFields() as $field) {
                $id = $field->get('id');
                if ($field->get('editable') && $_POST["delete-$id"] == 'on') {
                    $field->delete();
                    continue;
                }
                foreach (array('sort','label','type','name') as $f)
                    if (isset($_POST["$f-$id"]))
                        $field->set($f, $_POST["$f-$id"]);
                if ($field->get('editable'))
                    $field->set('required', $_POST["required-$id"] == 'on' ?  1 : 0);
                if ($field->isValid())
                    $field->save();
            }
            break;
        case 'add':
            $form = DynamicForm::create(array(
                'title'=>$_POST['title'],
                'notes'=>$_POST['notes']));
            if ($form->isValid())
                $form->save();
            break;
    }

    if ($form) {
        for ($i=0; isset($_POST["sort-new-$i"]); $i++) {
            if (!$_POST["label-new-$i"])
                continue;
            $field = DynamicFormField::create(array(
                'form_id'=>$form->get('id'),
                'sort'=>$_POST["sort-new-$i"],
                'label'=>$_POST["label-new-$i"],
                'type'=>$_POST["type-new-$i"],
                'name'=>$_POST["name-new-$i"],
                'required'=>$_POST["required-new-$i"] == 'on' ? 1 : 0
            ));
            if ($field->isValid())
                $field->save();
        }
        # Invalidate field cache
        $form->_fields = false;
    }
}

$page='dynamic-forms.inc.php';
if($form || ($_REQUEST['a'] && !strcasecmp($_REQUEST['a'],'add')))
    $page='dynamic-form.inc.php';

$nav->setTabActive('manage');
require(STAFFINC_DIR.'header.inc.php');
require(STAFFINC_DIR.$page);
include(STAFFINC_DIR.'footer.inc.php');
?>
