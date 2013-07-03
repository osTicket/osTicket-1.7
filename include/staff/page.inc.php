<?php
if(!defined('OSTADMININC') || !$thisstaff || !$thisstaff->isAdmin()) die('Access Denied');
$pageTypes = array(
        'landing' => 'Landing page',
        'offline' => 'Offline page',
        'thank-you' => 'Thank you page',
        'other' => 'Other',
        );
$info=array();
$qstr='';
if($page && $_REQUEST['a']!='add'){
    $title='Update Page';
    $action='update';
    $submit_text='Save Changes';
    $info=$page->getHashtable();
    $qstr.='&id='.$page->getId();
}else {
    $title='Add New Page';
    $action='add';
    $submit_text='Add Page';
    $info['isactive']=isset($info['isactive'])?$info['isactive']:0;
    $qstr.='&a='.urlencode($_REQUEST['a']);
}
$info=Format::htmlchars(($errors && $_POST)?$_POST:$info);
?>
<form action="pages.php?<?php echo $qstr; ?>" method="post" id="save">
 <?php csrf_token(); ?>
 <input type="hidden" name="do" value="<?php echo $action; ?>">
 <input type="hidden" name="a" value="<?php echo Format::htmlchars($_REQUEST['a']); ?>">
 <input type="hidden" name="id" value="<?php echo $info['id']; ?>">
 <h2>Email Template</h2>
 <table class="form_table" width="940" border="0" cellspacing="0" cellpadding="2">
    <thead>
        <tr>
            <th colspan="2">
                <h4><?php echo $title; ?></h4>
                <em>Page information.</em>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td width="180" class="required">
              Name:
            </td>
            <td>
                <input type="text" size="40" name="name" value="<?php echo $info['name']; ?>">
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['name']; ?></span>
            </td>
        </tr>
        <tr>
            <td width="180" class="required">
                Type:
            </td>
            <td>
                <select name="type">
                    <option value="" selected="selected">Select Page Type</option>
                    <?php
                    foreach($pageTypes as $k => $v)
                        echo sprintf('<option value="%s" %s>%s</option>',
                                $k, (($info['type']==$k)?'selected="selected"':''), $v);
                    ?>
                </select>
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['type']; ?></span>
            </td>
        </tr>
        <tr>
            <td width="180" class="required">
                Status:
            </td>
            <td>
                <input type="radio" name="isactive" value="1" <?php echo $info['isactive']?'checked="checked"':''; ?>><strong>Active</strong>
                <input type="radio" name="isactive" value="0" <?php echo !$info['isactive']?'checked="checked"':''; ?>>Disabled
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['isactive']; ?></span>
            </td>
        </tr>
        <tr>
            <th colspan="2">
                <em><b>Page body</b>: Ticket variables are only supported in thank-you pages.<font class="error">*&nbsp;<?php echo $errors['body']; ?></font></em>
            </th>
        </tr>
         <tr>
            <td colspan=2 style="padding-left:3px;">
                <textarea name="body" cols="21" rows="12" style="width:98%;" class="richtext"><?php echo $info['body']; ?></textarea>
            </td>
        </tr>
        <tr>
            <th colspan="2">
                <em><strong>Admin Notes</strong>: Internal notes.&nbsp;</em>
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
    <input type="reset"  name="reset"  value="Reset">
    <input type="button" name="cancel" value="Cancel" onclick='window.location.href="pages.php"'>
</p>
</form>
