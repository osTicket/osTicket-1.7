<h2><?= _('Alerts and Notices')?></h2>
<form action="settings.php?t=alerts" method="post" id="save">
<?php csrf_token(); ?>
<input type="hidden" name="t" value="alerts" >
<table class="form_table settings_table" width="940" border="0" cellspacing="0" cellpadding="2">
    <thead>
        <tr>
            <th>
                <h4><?= _('Alerts and Notices sent to staff on ticket "events"')?></h4>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr><th><em><b><?= _('New Ticket Alert')?></b>: <?= _('Alert sent out on new tickets')?></em></th></tr>
        <tr>
            <td><em><b><?= _('Status')?>:</b></em> &nbsp;
                <input type="radio" name="ticket_alert_active"  value="1"   <?php echo $config['ticket_alert_active']?'checked':''; ?> /><?= _('Enable')?>
                <input type="radio" name="ticket_alert_active"  value="0"   <?php echo !$config['ticket_alert_active']?'checked':''; ?> /><?= _('Disable')?>
                &nbsp;&nbsp;<font class="error">&nbsp;<?php echo $errors['ticket_alert_active']; ?></font></em>
             </td>
        </tr>
        <tr>
            <td>
                <input type="checkbox" name="ticket_alert_admin" <?php echo $config['ticket_alert_admin']?'checked':''; ?>> <?= _('Admin Email')?> <em>(<?php echo $cfg->getAdminEmail(); ?>)</em>
            </td>
        </tr>
        <tr>    
            <td>
                <input type="checkbox" name="ticket_alert_dept_manager" <?php echo $config['ticket_alert_dept_manager']?'checked':''; ?>> <?= _('Department Manager')?>
            </td>
        </tr>
        <tr>
            <td>
                <input type="checkbox" name="ticket_alert_dept_members" <?php echo $config['ticket_alert_dept_members']?'checked':''; ?>> <?= _('Department Members')?> <em><?= _('(spammy)')?></em>
            </td>
        </tr>
        <tr><th><em><b><?= _('New Message Alert')?></b>: <?= _('Alert sent out when a new message, from the user, is appended to an existing ticket')?></em></th></tr>
        <tr>
            <td><em><b><?= _('Status')?>:</b></em> &nbsp; 
              <input type="radio" name="message_alert_active"  value="1"   <?php echo $config['message_alert_active']?'checked':''; ?> /><?= _('Enable')?>
              &nbsp;&nbsp;
              <input type="radio" name="message_alert_active"  value="0"   <?php echo !$config['message_alert_active']?'checked':''; ?> /><?= _('Disable')?>
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox" name="message_alert_laststaff" <?php echo $config['message_alert_laststaff']?'checked':''; ?>> <?= _('Last Respondent')?>
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox" name="message_alert_assigned" <?php echo $config['message_alert_assigned']?'checked':''; ?>> <?= _('Assigned Staff')?>
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox" name="message_alert_dept_manager" <?php echo $config['message_alert_dept_manager']?'checked':''; ?>> <?= _('Department Manager')?> <em><?= _('(spammy)')?></em>
            </td>
        </tr>
        <tr><th><em><b><?= _('New Internal Note Alert')?></b>: <?= _('Alert sent out when a new internal note is posted.')?></em></th></tr>
        <tr>
            <td><em><b><?= _('Status')?>:</b></em> &nbsp;
              <input type="radio" name="note_alert_active"  value="1"   <?php echo $config['note_alert_active']?'checked':''; ?> /><?= _('Enable')?>
              &nbsp;&nbsp;
              <input type="radio" name="note_alert_active"  value="0"   <?php echo !$config['note_alert_active']?'checked':''; ?> /><?= _('Disable')?>
              &nbsp;&nbsp;&nbsp;<font class="error">&nbsp;<?php echo $errors['note_alert_active']; ?></font>
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox" name="note_alert_laststaff" <?php echo $config['note_alert_laststaff']?'checked':''; ?>> <?= _('Last Respondent')?>
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox" name="note_alert_assigned" <?php echo $config['note_alert_assigned']?'checked':''; ?>> <?= _('Assigned Staff')?>
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox" name="note_alert_dept_manager" <?php echo $config['note_alert_dept_manager']?'checked':''; ?>> <?= _('Department Manager')?> <em><?= _('(spammy)')?></em>
            </td>
        </tr>
        <tr><th><em><b><?= _('Ticket Assignment Alert')?></b>: <?= _('Alert sent out to staff on ticket assignment.')?></em></th></tr>
        <tr>
            <td><em><b><?= _('Status')?>: </b></em> &nbsp;
              <input name="assigned_alert_active" value="1" checked="checked" type="radio"><?= _('Enable')?>
              &nbsp;&nbsp;
              <input name="assigned_alert_active" value="0" type="radio"><?= _('Disable')?>
               &nbsp;&nbsp;&nbsp;<font class="error">&nbsp;<?php echo $errors['assigned_alert_active']; ?></font>
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox" name="assigned_alert_staff" <?php echo $config['assigned_alert_staff']?'checked':''; ?>> <?= _('Assigned Staff')?>
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox"name="assigned_alert_team_lead" <?php echo $config['assigned_alert_team_lead']?'checked':''; ?>> <?= _('Team Lead')?> <em><?= _('(On team assignment)')?></em>
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox"name="assigned_alert_team_members" <?php echo $config['assigned_alert_team_members']?'checked':''; ?>>
                <?= _('Team Members')?> <em><?= _('(spammy)')?></em>
            </td>
        </tr>
        <tr><th><em><b><?= _('Ticket Transfer Alert')?></b>: <?= _('Alert sent out to staff of the target department on ticket transfer.')?></em></th></tr>
        <tr>
            <td><em><b><?= _('Status')?>:</b></em> &nbsp;
              <input type="radio" name="transfer_alert_active"  value="1"   <?php echo $config['transfer_alert_active']?'checked':''; ?> /><?= _('Enable')?>
              <input type="radio" name="transfer_alert_active"  value="0"   <?php echo !$config['transfer_alert_active']?'checked':''; ?> /><?= _('Disable')?>
              &nbsp;&nbsp;&nbsp;<font class="error">&nbsp;<?php echo $errors['alert_alert_active']; ?></font>
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox" name="transfer_alert_assigned" <?php echo $config['transfer_alert_assigned']?'checked':''; ?>> <?= _('Assigned Staff/Team')?>
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox" name="transfer_alert_dept_manager" <?php echo $config['transfer_alert_dept_manager']?'checked':''; ?>> <?= _('Department Manager')?>
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox" name="transfer_alert_dept_members" <?php echo $config['transfer_alert_dept_members']?'checked':''; ?>>
                <?= _('Department Members')?> <em><?= _('(spammy)')?></em>
            </td>
        </tr>
        <tr><th><em><b><?= _('Overdue Ticket Alert')?></b>: <?= _('Alert sent out when a ticket becomes overdue - admin email gets an alert by default.')?></em></th></tr>
        <tr>
            <td><em><b><?= _('Status')?>:</b></em> &nbsp;
              <input type="radio" name="overdue_alert_active"  value="1"   <?php echo $config['overdue_alert_active']?'checked':''; ?> /><?= _('Enable')?>
              <input type="radio" name="overdue_alert_active"  value="0"   <?php echo !$config['overdue_alert_active']?'checked':''; ?> /><?= _('Disable')?>
              &nbsp;&nbsp;&nbsp;<font class="error">&nbsp;<?php echo $errors['overdue_alert_active']; ?></font>
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox" name="overdue_alert_assigned" <?php echo $config['overdue_alert_assigned']?'checked':''; ?>> <?= _('Assigned Staff/Team')?>
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox" name="overdue_alert_dept_manager" <?php echo $config['overdue_alert_dept_manager']?'checked':''; ?>> <?= _('Department Manager')?>
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox" name="overdue_alert_dept_members" <?php echo $config['overdue_alert_dept_members']?'checked':''; ?>> <?= _('Department Members')?> <em><?= _('(spammy)')?></em>
            </td>
        </tr>
        <tr><th><em><b><?= _('System Alerts')?></b>: <?= _('Enabled by default. Errors are sent to system admin email')?> (<?php echo $cfg->getAdminEmail(); ?>)</em></th></tr>
        <tr>
            <td>
              <input type="checkbox" name="send_sys_errors" checked="checked" disabled="disabled"> <?= _('System Errors')?>
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox" name="send_sql_errors" <?php echo $config['send_sql_errors']?'checked':''; ?>> <?= _('SQL errors')?>
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox" name="send_login_errors" <?php echo $config['send_login_errors']?'checked':''; ?>> <?= _('Excessive Login attempts')?>
            </td>
        </tr>
    </tbody>
</table>
<p style="padding-left:350px;">
    <input class="button" type="submit" name="submit" value="<?= _('Save Changes')?>">
    <input class="button" type="reset" name="reset" value="<?= _('Reset Changes')?>">
</p>
</form>
