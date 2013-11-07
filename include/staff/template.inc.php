<?php
if(!defined('OSTADMININC') || !$thisstaff || !$thisstaff->isAdmin()) die('Access Denied');

$info=array();
$qstr='';
if($template && $_REQUEST['a']!='add'){
    $title=lang('Update Template');
    $action=lang('update');
    $submit_text=lang('save_changes');
    $info=$template->getInfo();
    $info['tpl_id']=$template->getId();
    $qstr.='&tpl_id='.$template->getId();
}else {
    $title=lang('Add New Template');
    $action=lang('add');
    $submit_text=lang('Add Template');
    $info['isactive']=isset($info['isactive'])?$info['isactive']:0;
    $qstr.='&a='.urlencode($_REQUEST['a']);
}
$info=Format::htmlchars(($errors && $_POST)?$_POST:$info);
?>
<form action="templates.php?<?php echo $qstr; ?>" method="post" id="save">
 <?php csrf_token(); ?>
 <input type="hidden" name="do" value="<?php echo $action; ?>">
 <input type="hidden" name="a" value="<?php echo Format::htmlchars($_REQUEST['a']); ?>">
 <input type="hidden" name="tpl_id" value="<?php echo $info['tpl_id']; ?>">
 <h2><?php echo lang('Email Template') ?></h2>
 <table class="form_table" width="940" border="0" cellspacing="0" cellpadding="2">
    <thead>
        <tr>
            <th colspan="2">
                <h4><?php echo $title; ?></h4>
                <em><?php echo lang('Template information.'); ?></em>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td width="180" class="required">
                <?php echo lang('name'); ?>:
            </td>
            <td>
                <input type="text" size="30" name="name" value="<?php echo $info['name']; ?>">
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['name']; ?></span>
            </td>
        </tr>
        <tr>
            <td width="180" class="required">
               <?php echo lang('status'); ?>
            </td>
            <td>
                <input type="radio" name="isactive" value="1" <?php echo $info['isactive']?'checked="checked"':''; ?>><strong><?php echo lang('Active')?></strong>
                <input type="radio" name="isactive" value="0" <?php echo !$info['isactive']?'checked="checked"':''; ?>><?php echo lang('Disabled') ?>
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['isactive']; ?></span>
            </td>
        </tr>
        <?php
        if($template){ ?>
        <tr>
            <th colspan="2">
                <em><strong><?php echo lang('Template Messages') ?></strong>: <?php echo lang('Click on the message to edit.') ?>&nbsp;
                    <span class="error">*&nbsp;<?php echo $errors['rules']; ?></span></em>
            </th>
        </tr>
        <?php
         foreach($template->getTemplates() as $tpl){
             $info = $tpl->getDescription();
             if (!$info['name'])
                 continue;
            echo sprintf('<tr><td colspan=2>&nbsp;<strong><a href="templates.php?id=%d&a=manage">%s</a></strong>&nbsp-&nbsp<em>%s</em></td></tr>',
                    $tpl->getId(),Format::htmlchars($info['name']),
                    Format::htmlchars($info['desc']));
         }
         if (($undef = $template->getUndefinedTemplateNames())) { ?>
        <tr>
            <th colspan="2">
                <em><strong><?php echo lang('Unimplemented Template Messages') ?></strong>: <?php echo lang('Click on the message to implement') ?></em>
            </th>
        </tr>
        <?php
            foreach($template->getUndefinedTemplateNames() as $cn=>$info){
                echo sprintf('<tr><td colspan=2>&nbsp;<strong><a
                    href="templates.php?tpl_id=%d&a=implement&code_name=%s"
                    style="color:red;text-decoration:underline"
                    >%s</a></strong>&nbsp-&nbsp<em>%s</em></td></tr>',
                    $template->getId(),$cn,Format::htmlchars($info['name']),
                    Format::htmlchars($info['desc']));
            }
        }
        }else{ ?>
        <tr>
            <td width="180" class="required">
                <?php echo lang('Template To Clone')?>:
            </td>
            <td>
                <select name="tpl_id">
                    <option value="0">&mdash; <?php echo lang('Select One')?> &dash;</option>
                    <?php
                    $sql='SELECT tpl_id,name FROM '.EMAIL_TEMPLATE_GRP_TABLE.' ORDER by name';
                    if(($res=db_query($sql)) && db_num_rows($res)){
                        while(list($id,$name)=db_fetch_row($res)){
                            $selected=($info['tpl_id'] && $id==$info['tpl_id'])?'selected="selected"':'';
                            echo sprintf('<option value="%d" %s>%s</option>',$id,$selected,$name);
                        }
                    }
                    ?>
                </select>
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['tpl_id']; ?></span>
                 <em>(<?php echo lang('select an existing template to copy and edit it thereafter')?></em>
            </td>
        </tr>
        <?php } ?>
        <tr>
            <th colspan="2">
                <em><strong><?php echo lang('Admin Notes')?></strong>: <?php echo lang('Internal notes.')?>&nbsp;</em>
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
    <input type="reset"  name="reset"  value="<?php echo lang('reset') ?>">
    <input type="button" name="cancel" value="<?php echo lang('cancel') ?>" onclick='window.location.href="templates.php"'>
</p>
</form>
