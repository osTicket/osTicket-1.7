<?php
$msgtemplates=Template::message_templates();
$info=Format::htmlchars(($errors && $_POST)?$_POST:$_REQUEST);
$info['tpl']=($info['tpl'] && $msgtemplates[$info['tpl']])?$info['tpl']:'ticket_autoresp';
$tpl=$msgtemplates[$info['tpl']];
$info=array_merge($template->getMsgTemplate($info['tpl']),$info);

?>
<h2><?php echo _('Email Template Message');?> - <span><?php echo $template->getName(); ?></span></h2>
<div style="padding-top:10px;padding-bottom:5px;">
    <form method="get" action="templates.php">
    <input type="hidden" name="id" value="<?php echo $template->getId(); ?>">
    <input type="hidden" name="a" value="manage">
    <?php echo _('Message Template');?>:
    <select id="tpl_options" name="tpl" style="width:300px;">
        <option value="">&mdash; <?php echo _('Select Setting Group');?> &mdash;</option>
        <?php
        foreach($msgtemplates as $k=>$v) {
            $sel=($info['tpl']==$k)?'selected="selected"':'';
            echo sprintf('<option value="%s" %s>%s</option>',$k,$sel,$v['name']);
        }
        ?>
    </select>
    <input type="submit" value="<?php echo _('Go');?>">
    &nbsp;&nbsp;&nbsp;<font color="red"><?php echo $errors['tpl']; ?></font>
    </form>
</div>
<form action="templates.php?id=<?php echo $template->getId(); ?>" method="post" id="save">
<?php csrf_token(); ?>
<input type="hidden" name="id" value="<?php echo $template->getId(); ?>">
<input type="hidden" name="tpl" value="<?php echo $info['tpl']; ?>">
<input type="hidden" name="a" value="manage">
<input type="hidden" name="do" value="updatetpl">

<table class="form_table settings_table" width="940" border="0" cellspacing="0" cellpadding="2">
   <thead>
     <tr>
        <th colspan="2">
            <h4><?php echo Format::htmlchars($tpl['desc']); ?></h4>
            <em><?php echo _('Subject and body required.');?>  <a class="tip" href="ticket_variables.txt"><?php echo _('Supported Variables');?></a>.</em>
        </th>
     </tr>
    </thead>
    <tbody>
        <tr>
            <td colspan=2>
                <strong><?php echo _('Message Subject');?>:</strong> <em><?php echo _('Email message subject');?></em> <font class="error">*&nbsp;<?php echo $errors['subj']; ?></font><br>
                <input type="text" name="subj" size="60" value="<?php echo $info['subj']; ?>" >
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <strong><?php echo _('Message Body');?>:</strong> <em><?php echo _('Email message body.');?></em> <font class="error">*&nbsp;<?php echo $errors['body']; ?></font><br>
                <textarea name="body" cols="21" rows="16" style="width:98%;" wrap="soft" ><?php echo $info['body']; ?></textarea>
            </td>
        </tr>
    </tbody>
</table>
<p style="padding-left:210px;">
    <input class="button" type="submit" name="submit" value="<?php echo _('Save Changes');?>">
    <input class="button" type="reset" name="reset" value="<?php echo _('Reset Changes');?>">
    <input class="button" type="button" name="cancel" value="<?php echo _('Cancel Changes');?>" onclick='window.location.href="templates.php?id=<?php echo $template->getId(); ?>"'>
</p>
</form>
