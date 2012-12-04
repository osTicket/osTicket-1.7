<?php

$info=array();
if($group && $_REQUEST['a']!='add') {
    $title = 'Update dynamic form';
    $action = 'update';
    $submit_text='Save Changes';
    $info = $group->ht;
    $newcount=2;
} else {
    $title = 'Add new dynamic form';
    $action = 'add';
    $submit_text='Add Form';
    $newcount=4;
}
$info=Format::htmlchars(($errors && $_POST)?$_POST:$info);

?>
<form action="?" method="post" id="save">
    <?php csrf_token(); ?>
    <input type="hidden" name="do" value="<?php echo $action; ?>">
    <input type="hidden" name="id" value="<?php echo $info['id']; ?>">
    <h2>Dynamic Form</h2>
    <table class="form_table" width="940" border="0" cellspacing="0" cellpadding="2">
    <thead>
        <tr>
            <th colspan="4">
                <h4><?php echo $title; ?></h4>
                <em>Dynamic forms are used to combine several form sections
                into a larger form for use in the ticketing system. This
                allows for common sections to be reused among various forms
                </em>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td width="180" class="required">Title:</td>
            <td colspan="3"><input size="40" type="text" name="title"
                value="<?php echo $info['title']; ?>"/></td>
        </tr>
        <tr>
            <td width="180">Description:</td>
            <td colspan="3"><textarea name="notes" rows="3" cols="40"><?php
                echo $info['notes']; ?></textarea>
            </td>
        </tr>
    </tbody>
    <tbody>
       <tr><th>Delete | Sort</th><th>Title</th><th>Form
       name</th><th>Instructions</th></tr>
       <?php if ($group) foreach ($group->getForms() as $formatt) { 
           $form = $formatt->getForm(); ?>
           <tr>
               <td><input type="checkbox" name="delete-<?php echo $formatt->get('id'); ?>"/> <em>|</em>
                   <input type="text" size="4" name="sort-<?php echo $formatt->get('id'); ?>"
                       value="<?php echo $formatt->get('sort'); ?>"/>
               </td><td>
                   <input type="text" name="title-<?php echo $formatt->get('id'); ?>" size="16"
                       value="<?php echo $formatt->get('title'); ?>"/>
               </td><td>
                   <select name="section_id-<?php echo $formatt->get('id'); ?>">
                   <?php foreach (DynamicFormSection::all() as $form) { ?>
                       <option value="<?php echo $form->get('id'); ?>" <?php
                            if ($formatt->get('section_id') == $form->get('id'))
                                echo 'selected="selected"'; ?>>
                           <?php echo $form->get('title'); ?>
                       </option>
                   <?php } ?>
                   </select>
                    <a class="action-button" style="float:none"
                        href="dynamic-form-sections.php?id=<?php
                            echo $formatt->get('section_id'); ?>"><i class="icon-edit"></i
                            > Edit</a>
               </td><td>
                   <textarea rows="2" cols="40" name="instructions-<?php echo $formatt->get('id'); ?>"
                        ><?php echo $formatt->get('instructions') ?></textarea>
               </td>
           </tr>
       <?php } 
       for ($i=0; $i<$newcount; $i++) { ?>
       <tr>
           <td><em>add</em>
               <input type="text" name="sort-new-<?php echo $i; ?>" size="4"/>
           </td><td>
               <input type="text" name="title-new-<?php echo $i; ?>" size="16"/>
           </td><td>
               <select name="section_id-new-<?php echo $i; ?>">
                   <option value="0">&mdash; Select Form &mdash;</option>
               <?php foreach (DynamicFormSection::all() as $form) { ?>
                   <option value="<?php echo $form->get('id'); ?>">
                       <?php echo $form->get('title'); ?>
                   </option>
               <?php } ?>
               </select>
            </td><td><textarea rows="2" cols="40"
                name="instructions-new-<?php echo $i; ?>"></textarea>
            </td>
        </tr>
        <?php } ?>
    </tbody>
</table>
<p style="padding-left:225px;">
    <input type="submit" name="submit" value="<?php echo $submit_text; ?>">
    <input type="reset"  name="reset"  value="Reset">
    <input type="button" name="cancel" value="Cancel" onclick='window.location.href="?"'>
</p>
</form>
