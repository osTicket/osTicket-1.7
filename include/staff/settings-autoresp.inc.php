<h2><?= _('Autoresponder Settings')?></h2>
<form action="settings.php?t=autoresp" method="post" id="save">
<?php csrf_token(); ?>
<input type="hidden" name="t" value="autoresp" >
<table class="form_table settings_table" width="940" border="0" cellspacing="0" cellpadding="2">
    <thead>
        <tr>
            <th colspan="2">
                <h4><?= _('Autoresponder Setting')?></h4>
                <em><?= _('Global setting - can be disabled at department or email level.')?></em>
            </th>
        </tr>
    </thead>
    <tbody>

        <tr>
            <td width="160"><?= _('New Ticket')?>:</td>
            <td>
                <input type="radio" name="ticket_autoresponder"  value="1"   <?php echo $config['ticket_autoresponder']?'checked="checked"':''; ?> /><b><?= _('Enable')?></b>
                <input type="radio" name="ticket_autoresponder"  value="0"   <?php echo !$config['ticket_autoresponder']?'checked="checked"':''; ?> /><?= _('Disable')?>
                &nbsp;&nbsp;&nbsp;
                <em><?= _('(Autoresponse includes the ticket ID required to check status of the ticket)')?></em>
            </td>
        </tr>
        <tr>
            <td width="160"><?= _('New Ticket by staff')?>:</td>
            <td>
                <input type="radio" name="ticket_notice_active"  value="1"   <?php echo $config['ticket_notice_active']?'checked="checked"':''; ?> /><b><?= _('Enable')?></b>
                <input type="radio" name="ticket_notice_active"  value="0"   <?php echo !$config['ticket_notice_active']?'checked="checked"':''; ?> /><?= _('Disable')?>
                 &nbsp;&nbsp;&nbsp;
                 <em><?= _('(Notice sent when staff creates a ticket on behalf of the user (Staff can overwrite))')?></em>
            </td>
        </tr>
        <tr>
            <td width="160"><?= _('New Message')?>:</td>
            <td>
                <input type="radio" name="message_autoresponder"  value="1"   <?php echo $config['message_autoresponder']?'checked="checked"':''; ?> /><b><?= _('Enable')?></b>
                <input type="radio" name="message_autoresponder"  value="0"   <?php echo !$config['message_autoresponder']?'checked="checked"':''; ?> /><?= _('Disable')?>
                &nbsp;&nbsp;&nbsp;
                <em><?= _('(Confirmation notice sent when a new message is appended to an existing ticket)')?></em>
            </td>
        </tr>
        <tr>
            <td width="160"><?= _('Overlimit notice')?>:</td>
            <td>
                <input type="radio" name="overlimit_notice_active"  value="1"   <?php echo $config['overlimit_notice_active']?'checked="checked"':''; ?> /><b><?= _('Enable')?></b>
                <input type="radio" name="overlimit_notice_active"  value="0"   <?php echo !$config['overlimit_notice_active']?'checked="checked"':''; ?> /><?= _('Disable')?>
                &nbsp;&nbsp;&nbsp;
                <em><?= _('(Ticket denied notice sent to user on limit violation. Admin gets alerts on ALL denials by default)')?></em>
            </td>
        </tr>
    </tbody>
</table>
<p style="padding-left:200px;">
    <input class="button" type="submit" name="submit" value="<?= _('Save Changes')?>">
    <input class="button" type="reset" name="reset" value="<?= _('Reset Changes')?>">
</p>
</form>
