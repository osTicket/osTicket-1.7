<?php
if(!defined('OSTADMININC') || !$thisstaff || !$thisstaff->isAdmin()) die('Access Denied');
$info=array();
$qstr='';
if($email && $_REQUEST['a']!='add'){
    $title=lang('update_email');
    $action='update';
    $submit_text=lang('save_changes');
    $info=$email->getInfo();
    $info['id']=$email->getId();
    if($info['mail_delete'])
        $info['postfetch']='delete';
    elseif($info['mail_archivefolder'])
        $info['postfetch']='archive';
    else
        $info['postfetch']=''; //nothing.
    if($info['userpass'])
        $passwdtxt=lang('enter_new_passs');

    $qstr.='&id='.$email->getId();
}else {
    $title=lang('add_new_email');
    $action='create';
    $submit_text=lang('submit');
    $info['ispublic']=isset($info['ispublic'])?$info['ispublic']:1;
    $info['ticket_auto_response']=isset($info['ticket_auto_response'])?$info['ticket_auto_response']:1;
    $info['message_auto_response']=isset($info['message_auto_response'])?$info['message_auto_response']:1;
    $qstr.='&a='.$_REQUEST['a'];
}
$info=Format::htmlchars(($errors && $_POST)?$_POST:$info);
?>
<h2><?php echo lang('email_address'); ?></h2>
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
                <em><strong><?php echo lang('Email Information &amp; Settings'); ?> </strong></em>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td width="180" class="required">
                <?php echo lang('email_address'); ?>
            </td>
            <td>
                <input type="text" size="35" name="email" value="<?php echo $info['email']; ?>">
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['email']; ?></span>
            </td>
        </tr>
        <tr>
            <td width="180" class="required">
                <?php echo lang('email_name'); ?>
            </td>
            <td>
                <input type="text" size="35" name="name" value="<?php echo $info['name']; ?>">
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['name']; ?>&nbsp;</span>
            </td>
        </tr>
        <tr>
            <td width="180">
               <?php echo lang('New Ticket Priority'); ?>
            </td>
            <td>
                <select name="priority_id">
                    <option value="">&mdash; <?php echo lang('Select Priority') ?>&mdash;</option>
                    <?php
                    $sql='SELECT priority_id,priority_desc FROM '.PRIORITY_TABLE.' pri ORDER by priority_urgency DESC';
                    if(($res=db_query($sql)) && db_num_rows($res)){
                        while(list($id,$name)=db_fetch_row($res)){
                            $selected=($info['priority_id'] && $id==$info['priority_id'])?'selected="selected"':'';
                            echo sprintf('<option value="%d" %s>%s</option>',$id,$selected,$name);
                        }
                    }
                    ?>
                </select>
                &nbsp;<span class="error"><?php echo $errors['priority_id']; ?></span>
            </td>
        </tr>
        <tr>
            <td width="180">
                <?php echo lang('New Ticket Dept.') ?>
            </td>
            <td>
                <select name="dept_id">
                    <option value="">&mdash; <?php echo lang('Select Department') ?> &mdash;</option>
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
                <?php echo lang('Auto-response') ?>
            </td>
            <td>
                <input type="checkbox" name="noautoresp" value="1" <?php echo $info['noautoresp']?'checked="checked"':''; ?> >
                <strong><?php echo lang('Disable') ?></strong> <?php echo lang('new ticket auto-response for this email. Override global and dept. settings.') ?>
            </td>
        </tr>
        <tr>
            <th colspan="2">
                <em><strong><?php echo lang('Login Information') ?>:</strong>: <?php echo lang('Optional BUT required when IMAP/POP or SMTP (with auth.) are enabled.') ?></em>
            </th>
        </tr>
        <tr>
            <td width="180">
                <?php echo lang('Username') ?>
            </td>
            <td>
                <input type="text" size="35" name="userid" value="<?php echo $info['userid']; ?>">
                &nbsp;<span class="error">&nbsp;<?php echo $errors['userid']; ?>&nbsp;</span>
            </td>
        </tr>
        <tr>
            <td width="180">
                <?php echo lang('login_pass'); ?>
            </td>
            <td>
                <input type="password" size="35" name="passwd" value="<?php echo $info['passwd']; ?>">
                &nbsp;<span class="error">&nbsp;<?php echo $errors['passwd']; ?>&nbsp;</span>
                <br><em><?php echo $passwdtxt; ?></em>
            </td>
        </tr>
        <tr>
            <th colspan="2">
                <em><strong><?php echo lang('mail_account'); ?></strong>: <?php echo lang('fetch_inc_email'); ?> &nbsp;<font class="error">&nbsp;<?php echo $errors['mail']; ?></font></em>
            </th>
        </tr>
        <tr><td><?php echo lang('status'); ?></td>
            <td>
                <label><input type="radio" name="mail_active"  value="1"   <?php echo $info['mail_active']?'checked="checked"':''; ?> /><strong><?php echo lang('enable') ?></strong></label>
                &nbsp;&nbsp;
                <label><input type="radio" name="mail_active"  value="0"   <?php echo !$info['mail_active']?'checked="checked"':''; ?> /><?php echo lang('disable') ?></label>
                &nbsp;<font class="error">&nbsp;<?php echo $errors['mail_active']; ?></font>
            </td>
        </tr>
        <tr><td><?php echo lang('host'); ?></td>
            <td><input type="text" name="mail_host" size=35 value="<?php echo $info['mail_host']; ?>">
                &nbsp;<font class="error">&nbsp;<?php echo $errors['mail_host']; ?></font>
            </td>
        </tr>
        <tr><td><?php echo lang('port'); ?></td>
            <td><input type="text" name="mail_port" size=6 value="<?php echo $info['mail_port']?$info['mail_port']:''; ?>">
                &nbsp;<font class="error">&nbsp;<?php echo $errors['mail_port']; ?></font>
            </td>
        </tr>
        <tr><td><?php echo lang('protocol'); ?></td>
            <td>
                <select name="mail_protocol">
                    <option value='POP'>&mdash; <?php echo lang('mail_protocol'); ?> &mdash;</option>
                    <option value='POP' <?php echo ($info['mail_protocol']=='POP')?'selected="selected"':''; ?> ><?php echo lang('POP') ?></option>
                    <option value='IMAP' <?php echo ($info['mail_protocol']=='IMAP')?'selected="selected"':''; ?> ><?php echo lang('IMAP') ?></option>
                </select>
                <font class="error">&nbsp;<?php echo $errors['mail_protocol']; ?></font>
            </td>
        </tr>

        <tr><td><?php echo lang('encryption'); ?></td>
            <td>
                <select name="mail_encryption">
                    <option value='NONE'>None</option>
                    <option value='SSL' <?php echo ($info['mail_encryption']=='SSL')?'selected="selected"':''; ?> ><?php echo lang('SSL') ?></option>
                </select>
                <font class="error">&nbsp;<?php echo $errors['mail_encryption']; ?></font>
            </td>
        </tr>
        <tr><td><?php echo lang('fetch_frecuency'); ?></td>
            <td>
                <input type="text" name="mail_fetchfreq" size=4 value="<?php echo $info['mail_fetchfreq']?$info['mail_fetchfreq']:''; ?>"> <?php echo lang('Delay intervals in minutes') ?>
                &nbsp;<font class="error">&nbsp;<?php echo $errors['mail_fetchfreq']; ?></font>
            </td>
        </tr>
        <tr><td><?php echo lang('email_per_fetch'); ?></td>
            <td>
                <input type="text" name="mail_fetchmax" size=4 value="<?php echo $info['mail_fetchmax']?$info['mail_fetchmax']:''; ?>"> <?php echo lang('Maximum emails to process per fetch.') ?>
                &nbsp;<font class="error">&nbsp;<?php echo $errors['mail_fetchmax']; ?></font>
            </td>
        </tr>
        <tr><td valign="top"><?php echo lang('Fetched Emails'); ?></td>
             <td>
                <input type="radio" name="postfetch" value="archive" <?php echo ($info['postfetch']=='archive')? 'checked="checked"': ''; ?> >
                 <?php echo lang('move_to'); ?>: <input type="text" name="mail_archivefolder" size="20" value="<?php echo $info['mail_archivefolder']; ?>"/> <?php echo lang('folder.') ?>
                    &nbsp;<font class="error">&nbsp;<?php echo $errors['mail_folder']; ?></font>
                <input type="radio" name="postfetch" value="delete" <?php echo ($info['postfetch']=='delete')? 'checked="checked"': ''; ?> >
                <?php echo lang('delete_fetch_email'); ?>
                <input type="radio" name="postfetch" value="" <?php echo (isset($info['postfetch']) && !$info['postfetch'])? 'checked="checked"': ''; ?> >
                 <?php echo lang('do_nothing'); ?>
              <br><em><?php echo lang('moving_fetched'); ?></em> &nbsp;<font class="error"><?php echo $errors['postfetch']; ?></font>
            </td>
        </tr>

        <tr>
            <th colspan="2">
                <em><strong><?php echo lang('smtp_settings'); ?></strong>: <?php echo lang('when_enabled_the'); ?> <b><?php echo lang('email_account'); ?></b> <?php echo lang('will_use_smtp'); ?> &nbsp;<font class="error">&nbsp;<?php echo $errors['smtp']; ?></font></em>
            </th>
        </tr>
        <tr><td><?php echo lang('status'); ?></td>
            <td>
                <label><input type="radio" name="smtp_active"  value="1"   <?php echo $info['smtp_active']?'checked':''; ?> /><?php echo lang('Enable') ?></label>
                <label><input type="radio" name="smtp_active"  value="0"   <?php echo !$info['smtp_active']?'checked':''; ?> /><?php echo lang('Disable') ?></label>
                &nbsp;<font class="error">&nbsp;<?php echo $errors['smtp_active']; ?></font>
            </td>
        </tr>
        <tr><td><?php echo lang('smtp_host'); ?></td>
            <td><input type="text" name="smtp_host" size=35 value="<?php echo $info['smtp_host']; ?>">
                &nbsp;<font class="error">&nbsp;<?php echo $errors['smtp_host']; ?></font>
            </td>
        </tr>
        <tr><td><?php echo lang('smtp_port'); ?></td>
            <td><input type="text" name="smtp_port" size=6 value="<?php echo $info['smtp_port']?$info['smtp_port']:''; ?>">
                &nbsp;<font class="error">&nbsp;<?php echo $errors['smtp_port']; ?></font>
            </td>
        </tr>
        <tr><td><?php echo lang('auth_required'); ?></td>
            <td>

                 <label><input type="radio" name="smtp_auth"  value="1"
                    <?php echo $info['smtp_auth']?'checked':''; ?> /><?php echo lang('yes') ?></label>
                 <label><input type="radio" name="smtp_auth"  value="0"
                    <?php echo !$info['smtp_auth']?'checked':''; ?> /><?php echo lang('no') ?></label>
                <font class="error">&nbsp;<?php echo $errors['smtp_auth']; ?></font>
            </td>
        </tr>
        <tr>
            <td><?php echo lang('allow_head_spoof'); ?></td>
            <td>
                <input type="checkbox" name="smtp_spoofing" value="1" <?php echo $info['smtp_spoofing'] ?'checked="checked"':''; ?>>
                <?php echo lang('email_header_spoof'); ?> <em>(<?php echo lang('only_apply_email'); ?>)</em>
            </td>
        </tr>
        <tr>
            <th colspan="2">
                <em><strong><?php echo lang('internal_notes'); ?></strong>: <?php echo lang('admins_notes'); ?> &nbsp;<span class="error">&nbsp;<?php echo $errors['notes']; ?></span></em>
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
    <input type="reset"  name="reset"  value="<?php echo lang('reset'); ?>">
    <input type="button" name="cancel" value="<?php echo lang('cancel'); ?>" onclick='window.location.href="emails.php"'>
</p>
</form>
