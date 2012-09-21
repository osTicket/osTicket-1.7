<?php
if(!defined('OSTADMININC') || !$thisstaff || !$thisstaff->isAdmin() || !$config) die(_('Access Denied'));
?>
<h2><?= _('Email Settings and Options')?></h2>
<form action="settings.php?t=emails" method="post" id="save">
<?php csrf_token(); ?>
<input type="hidden" name="t" value="emails" >
<table class="form_table settings_table" width="940" border="0" cellspacing="0" cellpadding="2">
    <thead>
        <tr>
            <th colspan="2">
                <h4><?= _('Email Settings')?></h4>
                <em><?= _('Note that some of the global settings can be overwritten at department/email level.')?></em>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td width="180" class="required"><?= _('Default System Email')?>:</td>
            <td>
                <select name="default_email_id">
                    <option value=0 disabled><?= _('Select One')?></option>
                    <?php
                    $sql='SELECT email_id,email,name FROM '.EMAIL_TABLE;
                    if(($res=db_query($sql)) && db_num_rows($res)){
                        while (list($id,$email,$name) = db_fetch_row($res)){
                            $email=$name?"$name &lt;$email&gt;":$email;
                            ?>
                            <option value="<?php echo $id; ?>"<?php echo ($config['default_email_id']==$id)?'selected="selected"':''; ?>><?php echo $email; ?></option>
                        <?php
                        }
                    } ?>
                 </select>
                 &nbsp;<font class="error">*&nbsp;<?php echo $errors['default_email_id']; ?></font>
            </td>
        </tr>
        <tr>
            <td width="180" class="required"><?= _('Default Alert Email')?>:</td>
            <td>
                <select name="alert_email_id">
                    <option value="0" selected="selected"><?= _('Use Default System Email (above)')?></option>
                    <?php
                    $sql='SELECT email_id,email,name FROM '.EMAIL_TABLE.' WHERE email_id != '.db_input($config['default_email_id']);
                    if(($res=db_query($sql)) && db_num_rows($res)){
                        while (list($id,$email,$name) = db_fetch_row($res)){
                            $email=$name?"$name &lt;$email&gt;":$email;
                            ?>
                            <option value="<?php echo $id; ?>"<?php echo ($config['alert_email_id']==$id)?'selected="selected"':''; ?>><?php echo $email; ?></option>
                        <?php
                        }
                    } ?>
                 </select>
                 &nbsp;<font class="error">*&nbsp;<?php echo $errors['alert_email_id']; ?></font>
            </td>
        </tr>
        <tr>
            <td width="180" class="required"><?= _('Admin\'s Email Address')?>:</td>
            <td>
                <input type="text" size=40 name="admin_email" value="<?php echo $config['admin_email']; ?>">
                    &nbsp;<font class="error">*&nbsp;<?php echo $errors['admin_email']; ?></font>
                &nbsp;&nbsp;<em>(<?= _('System administrator\'s email')?>)</em> 
            </td>
        </tr>
        <tr><th colspan=2><em><strong><?= _('Incoming Emails')?></strong>: <?= _('For mail fetcher (polling) to work you must set an external cron job or enable auto-cron')?></em></th>
        <tr>
            <td width="180"><?= _('Email Polling')?>:</td>
            <td><input type="checkbox" name="enable_mail_polling" value=1 <?php echo $config['enable_mail_polling']? 'checked="checked"': ''; ?>  > <?= _('Enable POP/IMAP polling')?>
                 &nbsp;&nbsp;
                 <input type="checkbox" name="enable_auto_cron" <?php echo $config['enable_auto_cron']?'checked="checked"':''; ?>>
                 <?= _('Enable Auto-Cron')?> <em>(<?= _('Poll based on staff activity - NOT recommended')?>)</em>
            </td>
        </tr>
        <tr>
            <td width="180"><?= _('Email Piping')?>:</td>
            <td><input type="checkbox" name="enable_email_piping" value=1 <?php echo $config['enable_email_piping']? 'checked="checked"': ''; ?>> <?= _('Enable email piping')?>
                 &nbsp;&nbsp;<em>(<?= _('You pipe we accept policy')?>)</em>
            </td>
        </tr>
        <tr>
            <td width="180"><?= _('Strip Quoted Reply')?>:</td>
            <td>
                <input type="checkbox" name="strip_quoted_reply" <?php echo $config['strip_quoted_reply'] ? 'checked="checked"':''; ?>>
                <em>(<?= _('depends on the reply separator tag set below')?>)</em>
                &nbsp;<font class="error">&nbsp;<?php echo $errors['strip_quoted_reply']; ?></font>
            </td>
        </tr>
        <tr>
            <td width="180"><?= _('Reply Separator Tag')?>:</td>
            <td><input type="text" name="reply_separator" value="<?php echo $config['reply_separator']; ?>">
                &nbsp;<font class="error">&nbsp;<?php echo $errors['reply_separator']; ?></font>
            </td>
        </tr>
        <tr><th colspan=2><em><strong><?= _('Outgoing Emails')?></strong>: <?= _('Default email only applies to outgoing emails without SMTP setting')?>.</em></th></tr>
        <tr><td width="180"><?= _('Default Outgoing Email')?>:</td>
            <td>
                <select name="default_smtp_id">
                    <option value=0 selected="selected"><?= _('None: Use PHP mail function')?></option>
                    <?php
                    $sql='SELECT email_id,email,name,smtp_host FROM '.EMAIL_TABLE.' WHERE smtp_active=1';

                    if(($res=db_query($sql)) && db_num_rows($res)) {
                        while (list($id,$email,$name,$host) = db_fetch_row($res)){
                            $email=$name?"$name &lt;$email&gt;":$email;
                            ?>
                            <option value="<?php echo $id; ?>"<?php echo ($config['default_smtp_id']==$id)?'selected="selected"':''; ?>><?php echo $email; ?></option>
                        <?php
                        }
                    } ?>
                 </select>&nbsp;&nbsp;<font class="error">&nbsp;<?php echo $errors['default_smtp_id']; ?></font>
           </td>
       </tr>
    </tbody>
</table>
<p style="padding-left:250px;">
    <input class="button" type="submit" name="submit" value="<?= _('Save Changes')?>">
    <input class="button" type="reset" name="reset" value="<?= _('Reset Changes')?>">
</p>
</form>
