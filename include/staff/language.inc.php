<?php
if(!defined('OSTADMININC') || !$thisstaff || !$thisstaff->isAdmin()) die(lang('access_denied'));
$info=array();
$qstr='';
$title=lang('add_new_lang');
$action='create';
$info=Format::htmlchars(($errors && $_POST)?$_POST:$info);
?>
<h2><?php echo lang('languages'); ?></h2>
<form action="language.php?<?php echo $qstr; ?>" method="post" id="save">
 <?php csrf_token(); ?>
 <input type="hidden" name="do" value="<?php echo $action; ?>">
 <input type="hidden" name="a" value="<?php echo Format::htmlchars($_REQUEST['a']); ?>">

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
            <td width="180" class="required">
                <?php echo lang('language_name'); ?>
            </td>
            <td>
                <select name="language" style="width:250px">
                    <?php $assignedLanguages=getAssignedLanguages(); ?>
                    <?php foreach (getAllLanguages() as $key => $value): ?>
                        <?php  if(!array_key_exists($key, $assignedLanguages)): ?>
                            <option value="<?php echo $key; ?>"> <?php echo $value; ?> </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
    </tbody>
</table>
<p>
    <input type="submit" name="submit" value="<?php echo lang('save'); ?>">
    <input type="reset"  name="reset"  value="<?php echo lang('reset'); ?>">
    <input type="button" name="cancel" value="<?php echo lang('cancel'); ?>" onclick='window.location.href="language.php"'>
</p>
</form>
