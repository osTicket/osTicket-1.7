<?php
if(!defined('OSTADMININC') || !$thisstaff || !$thisstaff->isAdmin()) die(lang('access_denied'));

$info=array();
$qstr='';
if($rule && $_REQUEST['a']!='add'){
    $title=lang('update_ban_rule');
    $action='update';
    $submit_text=lang('update');
    $info=$rule->getInfo();
    $info['id']=$rule->getId();
    $qstr.='&id='.$rule->getId();
}else {
    $title=lang('add_email_to_ban');
    $action='add';
    $submit_text=lang('add');
    $info['isactive']=isset($info['isactive'])?$info['isactive']:1;
    $qstr.='&a='.urlencode($_REQUEST['a']);
}
$info=Format::htmlchars(($errors && $_POST)?$_POST:$info);
?>
<form action="banlist.php?<?php echo $qstr; ?>" method="post" id="save">
 <?php csrf_token(); ?>
 <input type="hidden" name="do" value="<?php echo $action; ?>">
 <input type="hidden" name="a" value="<?php echo Format::htmlchars($_REQUEST['a']); ?>">
 <input type="hidden" name="id" value="<?php echo $info['id']; ?>">
 <h2><?php echo lang('manage_ban_list'); ?></h2>
 <table class="form_table" width="940" border="0" cellspacing="0" cellpadding="2">
    <thead>
        <tr>
            <th colspan="2">
                <h4><?php echo $title; ?></h4>
                <em><?php echo lang('valid_email_requir'); ?></em>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td width="180" class="required">
              <?php echo lang('filter_name'); ?>:
            </td>
            <td><?php echo $filter->getName(); ?></td>
        </tr>
        <tr>
            <td width="180" class="required">
                <?php echo lang('ban_status'); ?>:
            </td>
            <td>
                <input type="radio" name="isactive" value="1" <?php echo $info['isactive']?'checked="checked"':''; ?>><strong><?php echo lang('active'); ?></strong>
                <input type="radio" name="isactive" value="0" <?php echo !$info['isactive']?'checked="checked"':''; ?>><?php echo lang('disabled'); ?>
                &nbsp;<span class="error">*&nbsp;</span>
            </td>
        </tr>
        <tr>
            <td width="180" class="required">
                <?php echo lang('email_address'); ?>:
            </td>
            <td>
                <input name="val" type="text" size="24" value="<?php echo $info['val']; ?>">
                 &nbsp;<span class="error">*&nbsp;<?php echo $errors['val']; ?></span>
            </td>
        </tr>
        <tr>
            <th colspan="2">
                <em><strong><?php echo lang('internal_notes'); ?></strong>: <?php echo lang('admin_notes'); ?>&nbsp;</em>
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
    <input type="button" name="cancel" value="<?php echo lang('cancel'); ?>" onclick='window.location.href="banlist.php"'>
</p>
</form>
