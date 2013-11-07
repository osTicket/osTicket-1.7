<h2><?php echo lang("Auto_res_Settings"); ?></h2>
<form action="settings.php?t=autoresp" method="post" id="save">
<?php csrf_token(); ?>
<input type="hidden" name="t" value="autoresp" >
<table class="form_table settings_table" width="940" border="0" cellspacing="0" cellpadding="2">
    <thead>
        <tr>
            <th colspan="2">
                <h4><?php echo lang("Auto_res_Settings"); ?> </h4>
                <em><?php echo lang("global_settings"); ?>.</em>
            </th>
        </tr>
    </thead>
    <tbody>

        <tr>
            <td width="160"><?php echo lang("new_ticket"); ?> :</td>
            <td>
                <input type="radio" name="ticket_autoresponder"  value="1"   <?php echo $config['ticket_autoresponder']?'checked="checked"':''; ?> /><b><?php echo lang('enable') ?></b>
                <input type="radio" name="ticket_autoresponder"  value="0"   <?php echo !$config['ticket_autoresponder']?'checked="checked"':''; ?> /><?php echo lang('disable') ?>
                &nbsp;&nbsp;&nbsp;
                <em>(<?php echo lang("check_status_ticket"); ?>)</em>
            </td>
        </tr>
        <tr>
            <td width="160"><?php echo lang("new_ticket_staff"); ?>:</td>
            <td>
                <input type="radio" name="ticket_notice_active"  value="1"   <?php echo $config['ticket_notice_active']?'checked="checked"':''; ?> /><b><?php echo lang('enable') ?></b>
                <input type="radio" name="ticket_notice_active"  value="0"   <?php echo !$config['ticket_notice_active']?'checked="checked"':''; ?> /><?php echo lang('disable') ?>
                 &nbsp;&nbsp;&nbsp;
                 <em>(<?php echo lang("ticket_behalf_user"); ?> (<?php echo lang("staff_can_owrite"); ?> ))</em>
            </td>
        </tr>
        <tr>
            <td width="160"><?php echo lang("new_message"); ?>:</td>
            <td>
                <input type="radio" name="message_autoresponder"  value="1"   <?php echo $config['message_autoresponder']?'checked="checked"':''; ?> /><b><?php echo lang('enable') ?></b>
                <input type="radio" name="message_autoresponder"  value="0"   <?php echo !$config['message_autoresponder']?'checked="checked"':''; ?> /><?php echo lang('disable') ?>
                &nbsp;&nbsp;&nbsp;
                <em>(<?php echo lang("append_ticket"); ?>)</em>
            </td>
        </tr>
        <tr>
            <td width="160"><?php echo lang("overlimit_notice"); ?>:</td>
            <td>
                <input type="radio" name="overlimit_notice_active"  value="1"   <?php echo $config['overlimit_notice_active']?'checked="checked"':''; ?> /><b><?php echo lang('enable') ?></b>
                <input type="radio" name="overlimit_notice_active"  value="0"   <?php echo !$config['overlimit_notice_active']?'checked="checked"':''; ?> /><?php echo lang('disable') ?>
                &nbsp;&nbsp;&nbsp;
                <em>(<?php echo lang("ticket_denied"); ?>)</em>
            </td>
        </tr>
    </tbody>
</table>
<p style="padding-left:200px;">
    <input class="button" type="submit" name="submit" value="<?php echo lang("save_changes"); ?> ">
    <input class="button" type="reset" name="reset" value="<?php echo lang("reset_changes"); ?> ">
</p>
</form>
