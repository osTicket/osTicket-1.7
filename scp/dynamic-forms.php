<?php
require('admin.inc.php');
require_once(INCLUDE_DIR."/class.dynamic_forms.php");

$group=null;
if($_REQUEST['id'] && !($group=DynamicFormset::lookup($_REQUEST['id'])))
    $errors['err']='Unknown or invalid dynamic form ID.';

if($_POST) {
    $fields = array('title', 'notes');
    switch(strtolower($_POST['do'])) {
        case 'update':
            foreach ($fields as $f)
                if (isset($_POST[$f]))
                    $group->set($f, $_POST[$f]);
            if ($group->isValid())
                $group->save();
            foreach ($group->getForms() as $form) {
                $id = $form->get('id');
                if ($_POST["delete-$id"] == 'on') {
                    $form->delete();
                    continue;
                }
                foreach (array('sort','section_id','title','instructions') as $f)
                    if (isset($_POST["$f-$id"]))
                        $form->set($f, $_POST["$f-$id"]);
                if ($form->isValid())
                    $form->save();
            }
            break;
        case 'add':
            $group = DynamicFormset::create(array(
                'title'=>$_POST['title'],
                'notes'=>$_POST['notes']));
            if ($group->isValid())
                $group->save();
            break;
    }

    if ($group) {
        for ($i=0; isset($_POST["sort-new-$i"]); $i++) {
            if (!$_POST["section_id-new-$i"])
                continue;
            $field = DynamicFormsetSections::create(array(
                'formset_id'=>$group->get('id'),
                'sort'=>$_POST["sort-new-$i"],
                'title'=>$_POST["title-new-$i"],
                'section_id'=>$_POST["section_id-new-$i"],
                'instructions'=>$_POST["instructions-new-$i"]
            ));
            if ($field->isValid())
                $field->save();
        }
        # Invalidate field cache
        $group->_forms = false;
    }
}

$page='dynamic-forms.inc.php';
if($group || ($_REQUEST['a'] && !strcasecmp($_REQUEST['a'],'add')))
    $page='dynamic-form.inc.php';

$nav->setTabActive('forms');
require(STAFFINC_DIR.'header.inc.php');
require(STAFFINC_DIR.$page);
include(STAFFINC_DIR.'footer.inc.php');
?>
