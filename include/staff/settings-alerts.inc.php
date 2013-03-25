<h2><?php echo __('Alerts and Notices');?></h2>
<form action="settings.php?t=alerts" method="post" id="save">
<?php csrf_token();?>
<input type="hidden" name="t" value="alerts" >
<table class="form_table settings_table" width="940" border="0" cellspacing="0" cellpadding="2">
    <thead>
        <tr>
            <th>
                <h4><?php echo __('Alerts and Notices sent to staff on ticket "events"');?></h4>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr><th><em><b><?php echo __('New Ticket Alert');?></b>: <?php echo __('Alert sent out on new tickets');?></em></th></tr>
        <tr>
            <td><em><b><?php echo __('Status');?>:</b></em> &nbsp;
                <input type="radio" name="ticket_alert_active"  value="1"   <?php echo $config['ticket_alert_active']?'checked':''; ?> /><?php echo __('Enable');?>
                <input type="radio" name="ticket_alert_active"  value="0"   <?php echo !$config['ticket_alert_active']?'checked':''; ?> /><?php echo __('Disable');?>
                &nbsp;&nbsp;<font class="error">&nbsp;<?php echo $errors['ticket_alert_active']; ?></font></em>
             </td>
        </tr>
        <tr>
            <td>
                <input type="checkbox" name="ticket_alert_admin" <?php echo $config['ticket_alert_admin']?'checked':''; ?>> <?php echo __('Admin Email');?> <em>(<?php echo $cfg->getAdminEmail(); ?>)</em>
            </td>
        </tr>
        <tr>    
            <td>
                <input type="checkbox" name="ticket_alert_dept_manager" <?php echo $config['ticket_alert_dept_manager']?'checked':''; ?>> <?php echo __('Department Manager');?>
            </td>
        </tr>
        <tr>
            <td>
                <input type="checkbox" name="ticket_alert_dept_members" <?php echo $config['ticket_alert_dept_members']?'checked':''; ?>> <?php echo __('Department Members <em>(spammy)</em>');?>
            </td>
        </tr>
        <tr><th><em><b><?php echo __('New Message Alert');?></b>: <?php echo __('Alert sent out when a new message, from the user, is appended to an existing ticket');?></em></th></tr>
        <tr>
            <td><em><b><?php echo __('Status');?>:</b></em> &nbsp; 
              <input type="radio" name="message_alert_active"  value="1"   <?php echo $config['message_alert_active']?'checked':''; ?> /><?php echo __('Enable');?>
              &nbsp;&nbsp;
              <input type="radio" name="message_alert_active"  value="0"   <?php echo !$config['message_alert_active']?'checked':''; ?> /><?php echo __('Disable');?>
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox" name="message_alert_laststaff" <?php echo $config['message_alert_laststaff']?'checked':''; ?>> <?php echo __('Last Respondent');?>
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox" name="message_alert_assigned" <?php echo $config['message_alert_assigned']?'checked':''; ?>> <?php echo __('Assigned Staff');?>
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox" name="message_alert_dept_manager" <?php echo $config['message_alert_dept_manager']?'checked':''; ?>> <?php echo __('Department Manager <em>(spammy)</em>');?>
            </td>
        </tr>
        <tr><th><em><b><?php echo __('New Internal Note Alert');?></b>: <?php echo __('Alert sent out when a new internal note is posted.');?></em></th></tr>
        <tr>
            <td><em><b><?php echo __('Status');?>:</b></em> &nbsp;
              <input type="radio" name="note_alert_active"  value="1"   <?php echo $config['note_alert_active']?'checked':''; ?> /><?php echo __('Enable');?>
              &nbsp;&nbsp;
              <input type="radio" name="note_alert_active"  value="0"   <?php echo !$config['note_alert_active']?'checked':''; ?> /><?php echo __('Disable');?>
              &nbsp;&nbsp;&nbsp;<font class="error">&nbsp;<?php echo $errors['note_alert_active']; ?></font>
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox" name="note_alert_laststaff" <?php echo $config['note_alert_laststaff']?'checked':''; ?>> <?php echo __('Last Respondent');?>
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox" name="note_alert_assigned" <?php echo $config['note_alert_assigned']?'checked':''; ?>> <?php echo __('Assigned Staff');?>
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox" name="note_alert_dept_manager" <?php echo $config['note_alert_dept_manager']?'checked':''; ?>> <?php echo __('Department Manager <em>(spammy)</em>');?>
            </td>
        </tr>
        <tr><th><em><b><?php echo __('Ticket Assignment Alert');?></b>: <?php echo __('Alert sent out to staff on ticket assignment.');?></em></th></tr>
        <tr>
            <td><em><b><?php echo __('Status');?>: </b></em> &nbsp;
              <input name="assigned_alert_active" value="1" checked="checked" type="radio"><?php echo __('Enable');?>
              &nbsp;&nbsp;
              <input name="assigned_alert_active" value="0" type="radio"><?php echo __('Disable');?>
               &nbsp;&nbsp;&nbsp;<font class="error">&nbsp;<?php echo $errors['assigned_alert_active']; ?></font>
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox" name="assigned_alert_staff" <?php echo $config['assigned_alert_staff']?'checked':''; ?>> <?php echo __('Assigned Staff');?>
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox"name="assigned_alert_team_lead" <?php echo $config['assigned_alert_team_lead']?'checked':''; ?>><?php echo __('Team Lead <em>(On team assignment)</em>');?>
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox"name="assigned_alert_team_members" <?php echo $config['assigned_alert_team_members']?'checked':''; ?>>
                <?php echo __('Team Members <em>(spammy)</em>');?>
            </td>
        </tr>
        <tr><th><em><b><?php echo __('Ticket Transfer Alert');?></b>: <?php echo __('Alert sent out to staff of the target department on ticket transfer.');?></em></th></tr>
        <tr>
            <td><em><b><?php echo __('Status');?>:</b></em> &nbsp;
              <input type="radio" name="transfer_alert_active"  value="1"   <?php echo $config['transfer_alert_active']?'checked':''; ?> /><?php echo __('Enable');?>
              <input type="radio" name="transfer_alert_active"  value="0"   <?php echo !$config['transfer_alert_active']?'checked':''; ?> /><?php echo __('Disable');?>
              &nbsp;&nbsp;&nbsp;<font class="error">&nbsp;<?php echo $errors['alert_alert_active']; ?></font>
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox" name="transfer_alert_assigned" <?php echo $config['transfer_alert_assigned']?'checked':''; ?>> <?php echo __('Assigned Staff/Team');?>
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox" name="transfer_alert_dept_manager" <?php echo $config['transfer_alert_dept_manager']?'checked':''; ?>> <?php echo __('Department Manager');?>
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox" name="transfer_alert_dept_members" <?php echo $config['transfer_alert_dept_members']?'checked':''; ?>>
                <?php echo __('Department Members <em>(spammy)</em>');?>
            </td>
        </tr>
        <tr><th><em><b><?php echo __('Overdue Ticket Alert');?></b>: <?php echo __('Alert sent out when a ticket becomes overdue - admin email gets an alert by default.');?></em></th></tr>
        <tr>
            <td><em><b><?php echo __('Status');?>:</b></em> &nbsp;
              <input type="radio" name="overdue_alert_active"  value="1"   <?php echo $config['overdue_alert_active']?'checked':''; ?> /><?php echo __('Enable');?>
              <input type="radio" name="overdue_alert_active"  value="0"   <?php echo !$config['overdue_alert_active']?'checked':''; ?> /><?php echo __('Disable');?>
              &nbsp;&nbsp;&nbsp;<font class="error">&nbsp;<?php echo $errors['overdue_alert_active']; ?></font>
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox" name="overdue_alert_assigned" <?php echo $config['overdue_alert_assigned']?'checked':''; ?>> <?php echo __('Assigned Staff/Team');?>
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox" name="overdue_alert_dept_manager" <?php echo $config['overdue_alert_dept_manager']?'checked':''; ?>> <?php echo __('Department Manager');?>
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox" name="overdue_alert_dept_members" <?php echo $config['overdue_alert_dept_members']?'checked':''; ?>> <?php echo __('Department Members <em>(spammy)</em>');?>
            </td>
        </tr>
        <tr><th><em><b><?php echo __('System Alerts');?></b>: <?php echo __('Enabled by default. Errors are sent to system admin email');?> (<?php echo $cfg->getAdminEmail(); ?>)</em></th></tr>
        <tr>
            <td>
              <input type="checkbox" name="send_sys_errors" checked="checked" disabled="disabled"><?php echo __('System Errors');?>
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox" name="send_sql_errors" <?php echo $config['send_sql_errors']?'checked':''; ?>><?php echo __('SQL errors');?>
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox" name="send_login_errors" <?php echo $config['send_login_errors']?'checked':''; ?>><?php echo __('Excessive Login attempts');?>
            </td>
        </tr>
    </tbody>
</table>
<p style="padding-left:350px;">
    <input class="button" type="submit" name="submit" value="<?php echo __('Save Changes');?>">
    <input class="button" type="reset" name="reset" value="<?php echo __('Reset Changes');?>">
</p>
</form>
