<?php
if(!defined('OSTADMININC') || !$thisstaff || !$thisstaff->isAdmin()) die(_('Access Denied'));
$info=array();
$qstr='';
if($email && $_REQUEST['a']!='add'){
    $title=_('Update Email');
    $action='update';
    $submit_text=_('Save Changes');
    $info=$email->getInfo();
    $info['id']=$email->getId();
    if($info['mail_delete'])
        $info['postfetch']='delete';
    elseif($info['mail_archivefolder'])
        $info['postfetch']='archive';
    else
        $info['postfetch']=''; //nothing.
    if($info['userpass'])
        $passwdtxt=_('To change password enter new password above.');

    $qstr.='&id='.$email->getId();
}else {
    $title=_('Add New Email');
    $action='create';
    $submit_text=_('Submit');
    $info['ispublic']=isset($info['ispublic'])?$info['ispublic']:1;
    $info['ticket_auto_response']=isset($info['ticket_auto_response'])?$info['ticket_auto_response']:1;
    $info['message_auto_response']=isset($info['message_auto_response'])?$info['message_auto_response']:1;
    $qstr.='&a='.$_REQUEST['a'];
}
$info=Format::htmlchars(($errors && $_POST)?$_POST:$info);
?>
<h2>Email Address</h2>
<form action="emails.php?<?php echo $qstr; ?>" method="post" id="save">
 <?php csrf_token(); ?>
 <input type="hidden" name="do" value="<?php echo $action; ?>">
 <input type="hidden" name="a" value="<?php echo Format::htmlchars($_REQUEST['a']); ?>">
 <input type="hidden" name="id" value="<?php echo $info['id']; ?>">
 <table class="form_table" width="940" border="0" cellspacing="0" cellpadding="2">
    <thead>
        <tr>
            <th colspan="2">
                <h4><?php echo $title; ?></h4>
                <em><strong><?= _('Email Information') ?></strong>: <?=  _('Login details are optional BUT required when IMAP/POP or SMTP are enabled.')?></em>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td width="180" class="required">
                <?= _('Email Address')?>
            </td>
            <td>
                <input type="text" size="35" name="email" value="<?php echo $info['email']; ?>">
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['email']; ?></span>
            </td>
        </tr>
        <tr>
            <td width="180" class="required">
                <?=  _('Email Name')?>
            </td>
            <td>
                <input type="text" size="35" name="name" value="<?php echo $info['name']; ?>">
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['name']; ?>&nbsp;</span>
            </td>
        </tr>
        <tr>
            <td width="180">
                <?= _('Login Username')?>
            </td>
            <td>
                <input type="text" size="35" name="userid" value="<?php echo $info['userid']; ?>">
                &nbsp;<span class="error">&nbsp;<?php echo $errors['userid']; ?>&nbsp;</span>
            </td>
        </tr>
        <tr>
            <td width="180">
                <?= _('Login Password')?>
            </td>
            <td>
                <input type="password" size="35" name="passwd" value="<?php echo $info['passwd']; ?>">
                &nbsp;<span class="error">&nbsp;<?php echo $errors['passwd']; ?>&nbsp;</span>
                <br><em><?php echo $passwdtxt; ?></em>
            </td>
        </tr>
        <tr>
            <th colspan="2">
                <em><strong><?= _('Mail Account')?></strong>: <?= _('Optional setting for fetching incoming emails. Mail fetching must be enabled with autocron active or external cron setup.')?> &nbsp;<font class="error">&nbsp;<?php echo $errors['mail']; ?></font></em>
            </th>
        </tr>
        <tr><td><?= _('Status')?></td>
            <td>
                <label><input type="radio" name="mail_active"  value="1"   <?php echo $info['mail_active']?'checked="checked"':''; ?> /><strong><?= _('Enable')?></strong></label>
                &nbsp;&nbsp;
                <label><input type="radio" name="mail_active"  value="0"   <?php echo !$info['mail_active']?'checked="checked"':''; ?> /><?= _('Disable')?></label>
                &nbsp;<font class="error">&nbsp;<?php echo $errors['mail_active']; ?></font>
            </td>
        </tr>
        <tr><td><?= _('Host') ?></td>
            <td><input type="text" name="mail_host" size=35 value="<?php echo $info['mail_host']; ?>">
                &nbsp;<font class="error">&nbsp;<?php echo $errors['mail_host']; ?></font>
            </td>
        </tr>
        <tr><td><?= _('Port') ?></td>
            <td><input type="text" name="mail_port" size=6 value="<?php echo $info['mail_port']?$info['mail_port']:''; ?>">
                &nbsp;<font class="error">&nbsp;<?php echo $errors['mail_port']; ?></font>
            </td>
        </tr>
        <tr><td><?= _('Protocol') ?></td>
            <td>
                <select name="mail_protocol">
                    <option value='POP'>&mdash; <?= _('Select Mail Protocol')?> &mdash;</option>
                    <option value='POP' <?php echo ($info['mail_protocol']=='POP')?'selected="selected"':''; ?> >POP</option>
                    <option value='IMAP' <?php echo ($info['mail_protocol']=='IMAP')?'selected="selected"':''; ?> >IMAP</option>
                </select>
                <font class="error">&nbsp;<?php echo $errors['mail_protocol']; ?></font>
            </td>
        </tr>

        <tr><td><?= _('Encryption')?></td>
            <td>
                <select name="mail_encryption">
                    <option value='NONE'><?= _('None')?></option>
                    <option value='SSL' <?php echo ($info['mail_encryption']=='SSL')?'selected="selected"':''; ?> >SSL</option>
                </select>
                <font class="error">&nbsp;<?php echo $errors['mail_encryption']; ?></font>
            </td>
        </tr>
        <tr><td><?= _('Fetch Frequency') ?></td>
            <td>
                <input type="text" name="mail_fetchfreq" size=4 value="<?php echo $info['mail_fetchfreq']?$info['mail_fetchfreq']:''; ?>"> <?= _('Delay intervals in minutes')?>
                &nbsp;<font class="error">&nbsp;<?php echo $errors['mail_fetchfreq']; ?></font>
            </td>
        </tr>
        <tr><td><?= _('Emails Per Fetch') ?></td>
            <td>
                <input type="text" name="mail_fetchmax" size=4 value="<?php echo $info['mail_fetchmax']?$info['mail_fetchmax']:''; ?>"> <?= _('Maximum emails to process per fetch.')?>
                &nbsp;<font class="error">&nbsp;<?php echo $errors['mail_fetchmax']; ?></font>
            </td>
        </tr>
        <tr>
            <td width="180">
                <?= _('New Ticket Priority')?>:
            </td>
            <td>
                <select name="priority_id">
                    <option value="">&mdash; <?= _('Select Priority')?> &mdash;</option>
                    <?php
                    $sql='SELECT priority_id,priority_desc FROM '.PRIORITY_TABLE.' pri ORDER by priority_urgency DESC';
                    if(($res=db_query($sql)) && db_num_rows($res)){
                        while(list($id,$name)=db_fetch_row($res)){
                            $selected=($info['priority_id'] && $id==$info['priority_id'])?'selected="selected"':'';
                            echo sprintf('<option value="%d" %s>%s</option>',$id,$selected,_($name));
                        }
                    }
                    ?>
                </select>
                &nbsp;<span class="error"><?php echo $errors['priority_id']; ?></span>
            </td>
        </tr>
        <tr>
            <td width="180">
                <?= _('New Ticket Dept.')?>
            </td>
            <td>
                <select name="dept_id">
                    <option value="">&mdash; <?= _('Select Department')?> &mdash;</option>
                    <?php
                    $sql='SELECT dept_id,dept_name FROM '.DEPT_TABLE.' dept ORDER by dept_name';
                    if(($res=db_query($sql)) && db_num_rows($res)){
                        while(list($id,$name)=db_fetch_row($res)){
                            $selected=($info['dept_id'] && $id==$info['dept_id'])?'selected="selected"':'';
                            echo sprintf('<option value="%d" %s>%s</option>',$id,$selected,$name);
                        }
                    }
                    ?>
                </select>
                &nbsp;<span class="error"><?php echo $errors['dept_id']; ?></span>
            </td>
        </tr>
        <tr>
            <td width="180">
                <?= _('Auto-response')?>
            </td>
            <td>
                <input type="checkbox" name="noautoresp" value="1" <?php echo $info['noautoresp']?'checked="checked"':''; ?> >
                <strong><?= _('Disable')?></strong> <?= _('new ticket auto-response for this email. Overwrite global and dept. settings.')?>
            </td>
        </tr>
        <tr><td valign="top"><?= _('Fetched Emails')?></td>
             <td>
                <input type="radio" name="postfetch" value="archive" <?php echo ($info['postfetch']=='archive')? 'checked="checked"': ''; ?> >
                <?=_(' Move to')?>: <input type="text" name="mail_archivefolder" size="20" value="<?php echo $info['mail_archivefolder']; ?>"/> <?=_(' folder.')?>
                    &nbsp;<font class="error">&nbsp;<?php echo $errors['mail_folder']; ?></font>
                <input type="radio" name="postfetch" value="delete" <?php echo ($info['postfetch']=='delete')? 'checked="checked"': ''; ?> >
                <?= _('Delete fetched emails')?>
                <input type="radio" name="postfetch" value="" <?php echo (isset($info['postfetch']) && !$info['postfetch'])? 'checked="checked"': ''; ?> >
                 <?= _('Do nothing (Not recommended)')?>
              <br><em><?= _('Moving fetched emails to a backup folder is highly recommended.')?></em> &nbsp;<font class="error"><?php echo $errors['postfetch']; ?></font>
            </td>
        </tr>

        <tr>
            <th colspan="2">
                <em><strong><?= _('SMTP Settings')?></strong>: <?= _('When enabled the')?> <b><?= _(' email account')?></b><?= _(' will use SMTP server instead of internal PHP mail() function for outgoing emails.')?> &nbsp;<font class="error">&nbsp;<?php echo $errors['smtp']; ?></font></em>
            </th>
        </tr>
        <tr><td><?= _('Status')?></td>
            <td>
                <label><input type="radio" name="smtp_active"  value="1"   <?php echo $info['smtp_active']?'checked':''; ?> /><?= _('Enable')?></label>
                <label><input type="radio" name="smtp_active"  value="0"   <?php echo !$info['smtp_active']?'checked':''; ?> /><?= _('Disable')?></label>
                &nbsp;<font class="error">&nbsp;<?php echo $errors['smtp_active']; ?></font>
            </td>
        </tr>
        <tr><td><?= _('SMTP Host')?></td>
            <td><input type="text" name="smtp_host" size=35 value="<?php echo $info['smtp_host']; ?>">
                &nbsp;<font class="error">&nbsp;<?php echo $errors['smtp_host']; ?></font>
            </td>
        </tr>
        <tr><td><?= _('SMTP Port')?></td>
            <td><input type="text" name="smtp_port" size=6 value="<?php echo $info['smtp_port']?$info['smtp_port']:''; ?>">
                &nbsp;<font class="error">&nbsp;<?php echo $errors['smtp_port']; ?></font>
            </td>
        </tr>
        <tr><td><?= _('Authentication Required?')?></td>
            <td>

                 <label><input type="radio" name="smtp_auth"  value="1"
                    <?php echo $info['smtp_auth']?'checked':''; ?> /><?= _('Yes')?></label>
                 <label><input type="radio" name="smtp_auth"  value="0"
                    <?php echo !$info['smtp_auth']?'checked':''; ?> /><?= _('NO')?></label>
                <font class="error">&nbsp;<?php echo $errors['smtp_auth']; ?></font>
            </td>
        </tr>
        <tr>
            <td><?= _('Allow Header Spoofing?')?></td>
            <td>
                <input type="checkbox" name="smtp_spoofing" value="1" <?php echo $info['smtp_spoofing'] ?'checked="checked"':''; ?>>
                <?= _('Allow email header spoofing ')?><em><?= _('(only applies to emails being sent through this account)')?></em>
            </td>
        </tr>
        <tr>
            <th colspan="2">
                <em><strong><?= _('Internal Notes')?></strong>: <?= _('Admin\'s notes.')?> &nbsp;<span class="error">&nbsp;<?php echo $errors['notes']; ?></span></em>
            </th>
        </tr>
        <tr>
            <td colspan=2>
                <textarea name="notes" cols="21" rows="5" style="width: 60%;"><?php echo $info['notes']; ?></textarea>
            </td>
        </tr>
    </tbody>
</table>
<p style="padding-left:225px;">
    <input type="submit" name="submit" value="<?php echo $submit_text; ?>">
    <input type="reset"  name="reset"  value="<?= _('Reset')?>">
    <input type="button" name="cancel" value="<?= _('Cancel')?>" onclick='window.location.href="emails.php"'>
</p>
</form>
