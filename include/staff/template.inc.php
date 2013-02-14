<?php
if(!defined('OSTADMININC') || !$thisstaff || !$thisstaff->isAdmin()) die(_('Access Denied'));

$info=array();
$qstr='';
if($template && $_REQUEST['a']!='add'){
    $title=_('Update Template');
    $action='update';
    $submit_text=_('Save Changes');
    $info=$template->getInfo();
    $info['id']=$template->getId();
    $qstr.='&id='.$template->getId();
}else {
    $title=_('Add New Template');
    $action='add';
    $submit_text=_('Add Template');
    $info['isactive']=isset($info['isactive'])?$info['isactive']:0;
    $qstr.='&a='.urlencode($_REQUEST['a']);
}
$info=Format::htmlchars(($errors && $_POST)?$_POST:$info);
?>
<form action="templates.php?<?php echo $qstr; ?>" method="post" id="save">
 <?php csrf_token(); ?>
 <input type="hidden" name="do" value="<?php echo $action; ?>">
 <input type="hidden" name="a" value="<?php echo Format::htmlchars($_REQUEST['a']); ?>">
 <input type="hidden" name="id" value="<?php echo $info['id']; ?>">
 <h2><?= _('Email Template')?></h2>
 <table class="form_table" width="940" border="0" cellspacing="0" cellpadding="2">
    <thead>
        <tr>
            <th colspan="2">
                <h4><?php echo $title; ?></h4>
                <em><?= _('Template information.')?></em>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td width="180" class="required">
              <?= _('Name')?>:
            </td>
            <td>
                <input type="text" size="30" name="name" value="<?php echo $info['name']; ?>">
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['name']; ?></span>
            </td>
        </tr>
        <tr>
            <td width="180" class="required">
                <?= _('Status')?>:
            </td>
            <td>
                <input type="radio" name="isactive" value="1" <?php echo $info['isactive']?'checked="checked"':''; ?>><strong><?= _('Active')?></strong>
                <input type="radio" name="isactive" value="0" <?php echo !$info['isactive']?'checked="checked"':''; ?>><?= _('Disabled')?>
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['isactive']; ?></span>
            </td>
        </tr>
        <tr>
            <td width="180" class="required">
                <?= _('Language')?>:
            </td>
            <td>
                <select name="lang_id">
                    <option value="en" selected="selected">English (US)</option>
		    <option value="pt-br">Português (BR)</option>
                </select>
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['lang_id']; ?></span>
            </td>
        </tr>
        <?php
        if($template){ ?>
        <tr>
            <th colspan="2">
                <em><strong><?= _('Template Messages')?></strong>: <?= _('Click on the message to edit.')?>&nbsp;
                    <span class="error">*&nbsp;<?php echo $errors['rules']; ?></span></em>
            </th>
        </tr>
        <?php
         foreach(Template::message_templates() as $k=>$tpl){
            echo sprintf('<tr><td colspan=2>&nbsp;<strong><a href="templates.php?id=%d&a=manage&tpl=%s">%s</a></strong>&nbsp-&nbsp<em>%s</em></td></tr>',
                    $template->getId(),$k,Format::htmlchars($tpl['name']),Format::htmlchars($tpl['desc']));
         }
        }else{ ?>
        <tr>
            <td width="180" class="required">
                <?= _('Template To Clone')?>:
            </td>
            <td>
                <select name="tpl_id">
                    <option value="0">&mdash; <?= _('Select One')?> &dash;</option>
                    <?php
                    $sql='SELECT tpl_id,name FROM '.EMAIL_TEMPLATE_TABLE.' ORDER by name';
                    if(($res=db_query($sql)) && db_num_rows($res)){
                        while(list($id,$name)=db_fetch_row($res)){
                            $selected=($info['tpl_id'] && $id==$info['tpl_id'])?'selected="selected"':'';
                            echo sprintf('<option value="%d" %s>%s</option>',$id,$selected,$name);
                        }
                    }
                    ?>
                </select>
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['tpl_id']; ?></span>
                 <em><?= _('(select an existing template to copy and edit it thereafter)')?></em>
            </td>
        </tr>
        <?php } ?>
        <tr>
            <th colspan="2">
                <em><strong><?= _('Admin Notes')?></strong>: <?= _('Internal notes.')?>&nbsp;</em>
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
    <input type="reset"  name="reset"  value="<?= _('Reset')?>">
    <input type="button" name="cancel" value="<?= _('Cancel')?>" onclick='window.location.href="templates.php"'>
</p>
</form>
