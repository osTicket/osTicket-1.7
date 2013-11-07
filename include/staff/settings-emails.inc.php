<?php
if(!defined('OSTADMININC') || !$thisstaff || !$thisstaff->isAdmin() || !$config) die('Access Denied');
?>
<h2><?php echo lang("email_settings"); ?> <?php echo lang("and_options"); ?> </h2>
<form action="settings.php?t=emails" method="post" id="save">
<?php csrf_token(); ?>
<input type="hidden" name="t" value="emails" >
<table class="form_table settings_table" width="940" border="0" cellspacing="0" cellpadding="2">
    <thead>
        <tr>
            <th colspan="2">
                <h4><?php echo lang("email_settings"); ?> </h4>
                <em><?php echo lang("ovw_email_dep_lev"); ?>.</em>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td width="180" class="required"><?php echo lang("system_email"); ?> :</td>
            <td>
                <select name="default_email_id">
                    <option value=0 disabled><?php echo lang("select_one"); ?></option>
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
            <td width="180" class="required"><?php echo lang("alert_email"); ?>:</td>
            <td>
                <select name="alert_email_id">
                    <option value="0" selected="selected"><?php echo lang("def_sys_email"); ?></option>
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
            <td width="180" class="required"><?php echo lang("admin_email_adress"); ?>:</td>
            <td>
                <input type="text" size=40 name="admin_email" value="<?php echo $config['admin_email']; ?>">
                    &nbsp;<font class="error">*&nbsp;<?php echo $errors['admin_email']; ?></font>
                &nbsp;&nbsp;<em>(<?php echo lang("sys_adm_email"); ?>)</em> 
            </td>
        </tr>
        <tr><th colspan=2><em><strong><?php echo lang("incoming_emails"); ?></strong>: <?php echo lang("mail_fetcher"); ?> </em></th>
        <tr>
            <td width="180"><?php echo lang("email_polling"); ?>:</td>
            <td><input type="checkbox" name="enable_mail_polling" value=1 <?php echo $config['enable_mail_polling']? 'checked="checked"': ''; ?>  ><?php echo lang('pop_imap_polling'); ?>
                 &nbsp;&nbsp;
                 <input type="checkbox" name="enable_auto_cron" <?php echo $config['enable_auto_cron']?'checked="checked"':''; ?>>
                 <?php echo lang("auto_cron"); ?> <em>(<?php echo lang("poll_staff_activ"); ?>)</em>
            </td>
        </tr>
        <tr>
            <td width="180"><?php echo lang("strip_reply"); ?>:</td>
            <td>
                <input type="checkbox" name="strip_quoted_reply" <?php echo $config['strip_quoted_reply'] ? 'checked="checked"':''; ?>>
                <em>(<?php echo lang("separator_tag"); ?>)</em>
                &nbsp;<font class="error">&nbsp;<?php echo $errors['strip_quoted_reply']; ?></font>
            </td>
        </tr>
        <tr>
            <td width="180"><?php echo lang("reply_sep_tag"); ?>:</td>
            <td><input type="text" name="reply_separator" value="<?php echo lang($config['reply_separator']); ?>">
                &nbsp;<font class="error">&nbsp;<?php echo $errors['reply_separator']; ?></font>
            </td>
        </tr>
        <tr><th colspan=2><em><strong><?php echo lang("outgoing_emails"); ?></strong>: <?php echo lang("email_applies"); ?>.</em></th></tr>
        <tr><td width="180"><?php echo lang("def_outgoing_emails"); ?>:</td>
            <td>
                <select name="default_smtp_id">
                    <option value=0 selected="selected"><?php echo lang("use_php_mail_f"); ?></option>
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
    <input class="button" type="submit" name="submit" value="<?php echo lang("save_changes"); ?>">
    <input class="button" type="reset" name="reset" value="<?php echo lang("reset_changes"); ?>">
</p>
</form>
