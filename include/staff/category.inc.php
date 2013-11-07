<?php
if(!defined('OSTSCPINC') || !$thisstaff || !$thisstaff->canManageFAQ()) die(lang('access_denied'));
$info=array();
$qstr='';
if($category && $_REQUEST['a']!='add'){
    $title=lang('update_category').' :'.$category->getName();
    $action='update';
    $submit_text=lang('save_changes');
    $info=$category->getHashtable();
    $info['id']=$category->getId();
    $qstr.='&id='.$category->getId();
}else {
    $title=lang('add_new_category');
    $action='create';
    $submit_text=lang('add');
    $qstr.='&a='.$_REQUEST['a'];
}
$info=Format::htmlchars(($errors && $_POST)?$_POST:$info);

?>
<form action="categories.php?<?php echo $qstr; ?>" method="post" id="save">
 <?php csrf_token(); ?>
 <input type="hidden" name="do" value="<?php echo $action; ?>">
 <input type="hidden" name="a" value="<?php echo Format::htmlchars($_REQUEST['a']); ?>">
 <input type="hidden" name="id" value="<?php echo $info['id']; ?>">
 <h2><?php echo lang('faq_category'); ?></h2>
 <table class="form_table" width="940" border="0" cellspacing="0" cellpadding="2">
    <thead>
        <tr>
            <th colspan="2">
                <h4><?php echo $title; ?></h4>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <th colspan="2">
                <em><?php echo lang('category_info'); ?>: <?php echo lang('public_cat_if_faq'); ?></em>
            </th>
        </tr>
        <tr>
            <td width="180" class="required"><?php echo lang('category_type'); ?>:</td>
            <td>
                <input type="radio" name="ispublic" value="1" <?php echo $info['ispublic']?'checked="checked"':''; ?>><b><?php echo lang('public'); ?></b> (<?php echo lang('publish'); ?>)
                &nbsp;&nbsp;&nbsp;&nbsp;
                <input type="radio" name="ispublic" value="0" <?php echo !$info['ispublic']?'checked="checked"':''; ?>><?php echo lang('private'); ?> (<?php echo lang('internal'); ?>)
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['ispublic']; ?></span>
            </td>
        </tr>
        <tr>
            <td colspan=2>
                <div style="padding-top:3px;"><b><?php echo lang('category_name'); ?></b>:&nbsp;<span class="faded"><?php echo lang('short_desc_name'); ?></span></div>
                    <input type="text" size="70" name="name" value="<?php echo $info['name']; ?>">
                    &nbsp;<span class="error">*&nbsp;<?php echo $errors['name']; ?></span>
                <br>
                <div style="padding-top:5px;">
                    <b><?php echo lang('category_desc'); ?></b>:&nbsp;<span class="faded"><?php echo lang('summary_category'); ?></span>
                    &nbsp;
                    <font class="error">*&nbsp;<?php echo $errors['description']; ?></font></div>
                    <textarea class="richtext" name="description" cols="21" rows="12" style="width:98%;"><?php echo $info['description']; ?></textarea>
            </td>
        </tr>
        <tr>
            <th colspan="2">
                <em><?php echo lang('internal_notes'); ?>&nbsp;</em>
            </th>
        </tr>
        <tr>
            <td colspan=2>
                <textarea name="notes" cols="21" rows="8" style="width: 80%;"><?php echo $info['notes']; ?></textarea>
            </td>
        </tr>
    </tbody>
</table>
<p style="padding-left:225px;">
    <input type="submit" name="submit" value="<?php echo $submit_text; ?>">
    <input type="reset"  name="reset"  value="<?php echo lang('reset'); ?>">
    <input type="button" name="cancel" value="<?php echo lang('cancel'); ?>" onclick='window.location.href="categories.php"'>
</p>
</form>
