<?php
if(!defined('OSTADMININC') || !$thisstaff || !$thisstaff->isAdmin() || !$config) die(lang('access_denied'));
if(!($maxfileuploads=ini_get('max_file_uploads')))
    $maxfileuploads=DEFAULT_MAX_FILE_UPLOADS;
?>
<h2><?php echo lang("ticket_settings"); ?> </h2>
<form action="settings.php?t=tickets" method="post" id="save">
<?php csrf_token(); ?>
<input type="hidden" name="t" value="tickets" >
<table class="form_table settings_table" width="940" border="0" cellspacing="0" cellpadding="2">
    <thead>
        <tr>
            <th colspan="2">
                <h4><?php echo lang("ticket_set"); ?></h4>
                <em><?php echo lang("ticket_settings"); ?>.</em>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr><td width="220" class="required"><?php echo lang("tickets_ids"); ?>:</td>
            <td>
                <input type="radio" name="random_ticket_ids"  value="0" <?php echo !$config['random_ticket_ids']?'checked="checked"':''; ?> />
                <?php echo lang("secuencial"); ?> 
                <input type="radio" name="random_ticket_ids"  value="1" <?php echo $config['random_ticket_ids']?'checked="checked"':''; ?> />
                <?php echo lang("random"); ?>   <em>(<?php echo lang("highly_recom"); ?>)</em>
            </td>
        </tr>

        <tr>
            <td width="180" class="required">
                <?php echo lang("default"); ?>  SLA:
            </td>
            <td>
                <select name="default_sla_id">
                    <option value="0">&mdash; <?php echo lang("none"); ?>  &mdash;</option>
                    <?php
                    if($slas=SLA::getSLAs()) {
                        foreach($slas as $id => $name) {
                            echo sprintf('<option value="%d" %s>%s</option>',
                                    $id,
                                    ($config['default_sla_id'] && $id==$config['default_sla_id'])?'selected="selected"':'',
                                    $name);
                        }
                    }
                    ?>
                </select>
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['default_sla_id']; ?></span>
            </td>
        </tr>
        <tr>
            <td width="180" class="required"><?php echo lang("default_priority"); ?>:</td>
            <td>
                <select name="default_priority_id">
                    <?php
                    $priorities= db_query('SELECT priority_id,priority_desc FROM '.TICKET_PRIORITY_TABLE);
                    while (list($id,$tag) = db_fetch_row($priorities)){ ?>
                        <option value="<?php echo $id; ?>"<?php echo ($config['default_priority_id']==$id)?'selected':''; ?>><?php echo $tag; ?></option>
                    <?php
                    } ?>
                </select>
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['default_priority_id']; ?></span>
             </td>
        </tr>
        <tr>
            <td><?php echo lang("maximum_open_tick"); ?> :</td>
            <td>
                <input type="text" name="max_open_tickets" size=4 value="<?php echo $config['max_open_tickets']; ?>">
                <?php echo lang("per_email"); ?>. <em>(<?php echo lang("flood_control"); ?>)</em>
            </td>
        </tr>
        <tr>
            <td><?php echo lang("ticket_time_lock"); ?>:</td>
            <td>
                <input type="text" name="autolock_minutes" size=4 value="<?php echo $config['autolock_minutes']; ?>">
                <font class="error"><?php echo $errors['autolock_minutes']; ?></font>
                <em>(<?php echo lang("minutes_to_lock"); ?>)</em>
            </td>
        </tr>
        <tr>
            <td width="180"><?php echo lang("ticket_priority"); ?>:</td>
            <td>
                <input type="checkbox" name="allow_priority_change" value="1" <?php echo $config['allow_priority_change'] ?'checked="checked"':''; ?>>
                <em>(<?php echo lang("ticket_priority"); ?>)</em>
            </td>
        </tr>
        <tr>
            <td width="180"><?php echo lang("e_ticket_priority"); ?>:</td>
            <td>
                <input type="checkbox" name="use_email_priority" value="1" <?php echo $config['use_email_priority'] ?'checked="checked"':''; ?> >
                <em>(<?php echo lang("mail_priority"); ?>)</em>
            </td>
        </tr>

        <tr>
            <td width="180"><?php echo lang("related_tickets"); ?>:</td>
            <td>
                <input type="checkbox" name="show_related_tickets" value="1" <?php echo $config['show_related_tickets'] ?'checked="checked"':''; ?> >
                <em>(<?php echo lang("tickets_on_user"); ?>)</em>
            </td>
        </tr>
        <tr>
            <td width="180"><?php echo lang("notes_inline"); ?>:</td>
            <td>
                <input type="checkbox" name="show_notes_inline" value="1" <?php echo $config['show_notes_inline'] ?'checked="checked"':''; ?> >
                <em>(<?php echo lang("s_intenal_notes"); ?>)</em>
              </td>
        </tr>
        <tr><td><?php echo lang("click_url"); ?>:</td>
            <td>
              <input type="checkbox" name="clickable_urls" <?php echo $config['clickable_urls']?'checked="checked"':''; ?>>
               <em>(<?php echo lang("convert_url"); ?>)</em>
            </td>
        </tr>
        <tr>
            <td><?php echo lang("human_verif"); ?>:</td>
            <td>
                <input type="checkbox" name="enable_captcha" <?php echo $config['enable_captcha']?'checked="checked"':''; ?>>
                <?php echo lang("enable_captcha"); ?>.<em>(<?php echo lang("require_dglib"); ?>)</em> &nbsp;<font class="error">&nbsp;<?php echo $errors['enable_captcha']; ?></font><br/>
            </td>
        </tr>
        <tr>
            <td><?php echo lang("ropen_ticket"); ?>:</td>
            <td>
                <input type="checkbox" name="auto_assign_reopened_tickets" <?php echo $config['auto_assign_reopened_tickets']?'checked="checked"':''; ?>>
                <?php echo lang("auto_ropen_ticket"); ?>.
            </td>
        </tr>
        <tr>
            <td><?php echo lang("assigned_tickets"); ?>:</td>
            <td>
                <input type="checkbox" name="show_assigned_tickets" <?php echo $config['show_assigned_tickets']?'checked="checked"':''; ?>>
                <?php echo lang("assigned_tickets_oq"); ?>.
            </td>
        </tr>
        <tr>
            <td><?php echo lang("answered_tickets"); ?>:</td>
            <td>
                <input type="checkbox" name="show_answered_tickets" <?php echo $config['show_answered_tickets']?'checked="checked"':''; ?>>
                <?php echo lang("s_answered_tickets"); ?>.
            </td>
        </tr>
        <tr>
            <td><?php echo lang("ticket_activity"); ?>:</td>
            <td>
                <input type="checkbox" name="log_ticket_activity" <?php echo $config['log_ticket_activity']?'checked="checked"':''; ?>>
                <?php echo lang("ticket_act_as_in"); ?>.
            </td>
        </tr>
        <tr>
            <td><?php echo lang("staff_identity"); ?>:</td>
            <td>
                <input type="checkbox" name="hide_staff_name" <?php echo $config['hide_staff_name']?'checked="checked"':''; ?>>
                <?php echo lang("hide_name_respons"); ?>.
            </td>
        </tr>
        <tr>
            <th colspan="2">
                <em><b><?php echo lang("attachments"); ?></b>:  <?php echo lang("upload_apply_ticket"); ?>.</em>
            </th>
        </tr>
        <tr>
            <td width="180"><?php echo lang("allow_attachments"); ?>:</td>
            <td>
              <input type="checkbox" name="allow_attachments" <?php echo $config['allow_attachments']?'checked="checked"':''; ?>><b><?php echo lang('allow_attachments'); ?></b>
                &nbsp; <em>(<?php echo lang("global"); ?>)</em>
                &nbsp;<font class="error">&nbsp;<?php echo $errors['allow_attachments']; ?></font>
            </td>
        </tr>
        <tr>
            <td width="180"><?php echo lang("api_attachment"); ?>:</td>
            <td>
                <input type="checkbox" name="allow_email_attachments" <?php echo $config['allow_email_attachments']?'checked="checked"':''; ?>><?php echo lang('Accept emailed/API attachments.'); ?>
                    &nbsp;<font class="error">&nbsp;<?php echo $errors['allow_email_attachments']; ?></font>
            </td>
        </tr>
        <tr>
            <td width="180"><?php echo lang("web_attachment"); ?>:</td>
            <td>
                <input type="checkbox" name="allow_online_attachments" <?php echo $config['allow_online_attachments']?'checked="checked"':''; ?> >
                    <?php echo lang("allow_web_upload"); ?> &nbsp;&nbsp;&nbsp;&nbsp;
                <input type="checkbox" name="allow_online_attachments_onlogin" <?php echo $config['allow_online_attachments_onlogin'] ?'checked="checked"':''; ?> >
                    <?php echo lang("limit_authenticate"); ?>. <em>(<?php echo lang("u_log_to_upload"); ?>)</em>
                    <font class="error">&nbsp;<?php echo $errors['allow_online_attachments']; ?></font>
            </td>
        </tr>
        <tr>
            <td><?php echo lang("max_file_upload"); ?>:</td>
            <td>
                <select name="max_user_file_uploads">
                    <?php
                    for($i = 1; $i <=$maxfileuploads; $i++) {
                        ?>
                        <option <?php echo $config['max_user_file_uploads']==$i?'selected="selected"':''; ?> value="<?php echo $i; ?>">
                            <?php echo $i; ?>&nbsp;<?php echo ($i>1)?lang('files'):lang('file'); ?></option>
                        <?php
                    } ?>
                </select>
                <em>(<?php echo lang("u_upload_simult"); ?>)</em>
                &nbsp;<font class="error">&nbsp;<?php echo $errors['max_user_file_uploads']; ?></font>
            </td>
        </tr>
        <tr>
            <td><?php echo lang("max_sfile_upload"); ?>:</td>
            <td>
                <select name="max_staff_file_uploads">
                    <?php
                    for($i = 1; $i <=$maxfileuploads; $i++) {
                        ?>
                        <option <?php echo $config['max_staff_file_uploads']==$i?'selected="selected"':''; ?> value="<?php echo $i; ?>">
                            <?php echo $i; ?>&nbsp;<?php echo ($i>1)?lang('files'):lang('file'); ?></option>
                        <?php
                    } ?>
                </select>
                <em>(<?php echo lang("s_upload_simult"); ?>)</em>
                &nbsp;<font class="error">&nbsp;<?php echo $errors['max_staff_file_uploads']; ?></font>
            </td>
        </tr>
        <tr>
            <td width="180"><?php echo lang("maximum"); ?>  <?php echo lang("file_size"); ?>:</td>
            <td>
                <input type="text" name="max_file_size" value="<?php echo $config['max_file_size']; ?>"> <?php echo lang('in'); ?> bytes.
                    <em>(<?php echo lang("system_max"); ?>. <?php echo Format::file_size(ini_get('upload_max_filesize')); ?>)</em>
                    <font class="error">&nbsp;<?php echo $errors['max_file_size']; ?></font>
            </td>
        </tr>
        <tr>
            <td width="180"><?php echo lang("ticket_response_file"); ?>:</td>
            <td>
                <input type="checkbox" name="email_attachments" <?php echo $config['email_attachments']?'checked="checked"':''; ?> ><?php echo lang('e_attachment_to_user'); ?>
            </td>
        </tr>
        <tr>
            <th colspan="2">
                <em><strong><?php echo lang("file_types_accept"); ?></strong>: <?php echo lang("limit_file_user_subm"); ?>.
                <font class="error">&nbsp;<?php echo $errors['allowed_filetypes']; ?></font></em>
            </th>
        </tr>
        <tr>
            <td colspan="2">
                <em><?php echo lang("allow_extensions"); ?> <b><i>.*</i></b>&nbsp;<?php echo lang("e_e_not_recommended"); ?>.</em><br>
                <textarea name="allowed_filetypes" cols="21" rows="4" style="width: 65%;" wrap="hard" ><?php echo $config['allowed_filetypes']; ?></textarea>
            </td>
        </tr>
    </tbody>
</table>
<p style="padding-left:250px;">
    <input class="button" type="submit" name="submit" value="<?php echo lang("save_changes"); ?>">
    <input class="button" type="reset" name="reset" value="<?php echo lang("reset_changes"); ?>">
</p>
</form>

