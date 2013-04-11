<h2>Alerts and Notices</h2>
<form action="settings.php?t=alerts" method="post" id="save">
<?php csrf_token(); ?>
<input type="hidden" name="t" value="alerts" >
<table class="form_table settings_table" width="940" border="0" cellspacing="0" cellpadding="2">
    <thead>
        <tr>
            <th>
                <h4>Alerts and Notices sent to staff on ticket "events"</h4>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr><th><em><b>New Ticket Alert</b>: Alert sent out on new tickets</em></th></tr>
        <tr>
            <td><em><b>Status:</b></em> &nbsp;
                <label>
                <input type="radio" name="ticket_alert_active"  value="1"   <?php echo $config['ticket_alert_active']?'checked':''; ?> />Enable
                </label>
                <label>
                <input type="radio" name="ticket_alert_active"  value="0"   <?php echo !$config['ticket_alert_active']?'checked':''; ?> />Disable
                </label>
                &nbsp;&nbsp;<font class="error">&nbsp;<?php echo $errors['ticket_alert_active']; ?></font></em>
             </td>
        </tr>
        <tr>
            <td>
            	<label>
                <input type="checkbox" name="ticket_alert_admin" <?php echo $config['ticket_alert_admin']?'checked':''; ?>> Admin Email
                 <em>(<?php echo $cfg->getAdminEmail(); ?>)</em>
                 </label>
            </td>
        </tr>
        <tr>    
            <td>
                <label>
                <input type="checkbox" name="ticket_alert_dept_manager" <?php echo $config['ticket_alert_dept_manager']?'checked':''; ?>> Department Manager
                </label>
            </td>
        </tr>
        <tr>
            <td>
                <label>
                <input type="checkbox" name="ticket_alert_dept_members" <?php echo $config['ticket_alert_dept_members']?'checked':''; ?>> Department Members 
                <em>(spammy)</em>
                </label>
            </td>
        </tr>
        <tr><th><em><b>New Message Alert</b>: Alert sent out when a new message, from the user, is appended to an existing ticket</em></th></tr>
        <tr>
            <td><em><b>Status:</b></em> &nbsp; 
              <label>
              <input type="radio" name="message_alert_active"  value="1"   <?php echo $config['message_alert_active']?'checked':''; ?> />Enable
              </label>
              &nbsp;&nbsp;
              <label>
              <input type="radio" name="message_alert_active"  value="0"   <?php echo !$config['message_alert_active']?'checked':''; ?> />Disable
              </label>
            </td>
        </tr>
        <tr>
            <td>
              <label>
              <input type="checkbox" name="message_alert_laststaff" <?php echo $config['message_alert_laststaff']?'checked':''; ?>> Last Respondent
              </label>
            </td>
        </tr>
        <tr>
            <td>
              <label>
              <input type="checkbox" name="message_alert_assigned" <?php echo $config['message_alert_assigned']?'checked':''; ?>> Assigned Staff
              </label>
            </td>
        </tr>
        <tr>
            <td>
              <label>
              <input type="checkbox" name="message_alert_dept_manager" <?php echo $config['message_alert_dept_manager']?'checked':''; ?>> Department Manager <em>(spammy)</em>
              </label>
            </td>
        </tr>
        <tr><th><em><b>New Internal Note Alert</b>: Alert sent out when a new internal note is posted.</em></th></tr>
        <tr>
            <td><em><b>Status:</b></em> &nbsp;
              <label>
              <input type="radio" name="note_alert_active"  value="1"   <?php echo $config['note_alert_active']?'checked':''; ?> />Enable
              </label>
              &nbsp;&nbsp;
              <label>
              <input type="radio" name="note_alert_active"  value="0"   <?php echo !$config['note_alert_active']?'checked':''; ?> />Disable
              </label>
              &nbsp;&nbsp;&nbsp;<font class="error">&nbsp;<?php echo $errors['note_alert_active']; ?></font>
            </td>
        </tr>
        <tr>
            <td>
              <label>
              <input type="checkbox" name="note_alert_laststaff" <?php echo $config['note_alert_laststaff']?'checked':''; ?>> Last Respondent
              </label>
            </td>
        </tr>
        <tr>
            <td>
              <label>
              <input type="checkbox" name="note_alert_assigned" <?php echo $config['note_alert_assigned']?'checked':''; ?>> Assigned Staff
              </label>
            </td>
        </tr>
        <tr>
            <td>
              <label>
              <input type="checkbox" name="note_alert_dept_manager" <?php echo $config['note_alert_dept_manager']?'checked':''; ?>> Department Manager <em>(spammy)</em>
              </label>
            </td>
        </tr>
        <tr><th><em><b>Ticket Assignment Alert</b>: Alert sent out to staff on ticket assignment.</em></th></tr>
        <tr>
            <td><em><b>Status: </b></em> &nbsp;
              <label>
              <input type="radio" name="assigned_alert_active" value="1" checked="checked">Enable
              </label>
              &nbsp;&nbsp;
              <label>
              <input type="radio" name="assigned_alert_active" value="0">Disable
              </label>
               &nbsp;&nbsp;&nbsp;<font class="error">&nbsp;<?php echo $errors['assigned_alert_active']; ?></font>
            </td>
        </tr>
        <tr>
            <td>
              <label>
              <input type="checkbox" name="assigned_alert_staff" <?php echo $config['assigned_alert_staff']?'checked':''; ?>> Assigned Staff
              </label>
            </td>
        </tr>
        <tr>
            <td>
              <label>
              <input type="checkbox"name="assigned_alert_team_lead" <?php echo $config['assigned_alert_team_lead']?'checked':''; ?>>Team Lead <em>(On team assignment)</em>
              </label>
            </td>
        </tr>
        <tr>
            <td>
              <label>
              <input type="checkbox"name="assigned_alert_team_members" <?php echo $config['assigned_alert_team_members']?'checked':''; ?>>
                Team Members <em>(spammy)</em>
              </label>
            </td>
        </tr>
        <tr><th><em><b>Ticket Transfer Alert</b>: Alert sent out to staff of the target department on ticket transfer.</em></th></tr>
        <tr>
            <td><em><b>Status:</b></em> &nbsp;
              <label>
              <input type="radio" name="transfer_alert_active"  value="1"   <?php echo $config['transfer_alert_active']?'checked':''; ?> />Enable
              </label>
              <label>
              <input type="radio" name="transfer_alert_active"  value="0"   <?php echo !$config['transfer_alert_active']?'checked':''; ?> />Disable
              </label>
              &nbsp;&nbsp;&nbsp;<font class="error">&nbsp;<?php echo $errors['alert_alert_active']; ?></font>
            </td>
        </tr>
        <tr>
            <td>
              <label>
              <input type="checkbox" name="transfer_alert_assigned" <?php echo $config['transfer_alert_assigned']?'checked':''; ?>> Assigned Staff/Team
              </label>
            </td>
        </tr>
        <tr>
            <td>
              <label>
              <input type="checkbox" name="transfer_alert_dept_manager" <?php echo $config['transfer_alert_dept_manager']?'checked':''; ?>> Department Manager
              </label>
            </td>
        </tr>
        <tr>
            <td>
              <label>
              <input type="checkbox" name="transfer_alert_dept_members" <?php echo $config['transfer_alert_dept_members']?'checked':''; ?>>
                Department Members <em>(spammy)</em>
              </label>
            </td>
        </tr>
        <tr><th><em><b>Overdue Ticket Alert</b>: Alert sent out when a ticket becomes overdue - admin email gets an alert by default.</em></th></tr>
        <tr>
            <td><em><b>Status:</b></em> &nbsp;
              <label>
              <input type="radio" name="overdue_alert_active"  value="1"   <?php echo $config['overdue_alert_active']?'checked':''; ?> />Enable
              </label>
              <label>
              <input type="radio" name="overdue_alert_active"  value="0"   <?php echo !$config['overdue_alert_active']?'checked':''; ?> />Disable
              </label>
              &nbsp;&nbsp;&nbsp;<font class="error">&nbsp;<?php echo $errors['overdue_alert_active']; ?></font>
            </td>
        </tr>
        <tr>
            <td>
              <label>
              <input type="checkbox" name="overdue_alert_assigned" <?php echo $config['overdue_alert_assigned']?'checked':''; ?>> Assigned Staff/Team
              </label>
            </td>
        </tr>
        <tr>
            <td>
              <label>
              <input type="checkbox" name="overdue_alert_dept_manager" <?php echo $config['overdue_alert_dept_manager']?'checked':''; ?>> Department Manager
              </label>
            </td>
        </tr>
        <tr>
            <td>
              <label>
              <input type="checkbox" name="overdue_alert_dept_members" <?php echo $config['overdue_alert_dept_members']?'checked':''; ?>> Department Members <em>(spammy)</em>
              </label>
            </td>
        </tr>
        <tr><th><em><b>System Alerts</b>: Enabled by default. Errors are sent to system admin email (<?php echo $cfg->getAdminEmail(); ?>)</em></th></tr>
        <tr>
            <td>
              <label>
              <input type="checkbox" name="send_sys_errors" checked="checked" disabled="disabled">System Errors
              </label>
            </td>
        </tr>
        <tr>
            <td>
              <label>
              <input type="checkbox" name="send_sql_errors" <?php echo $config['send_sql_errors']?'checked':''; ?>>SQL errors
              </label>
            </td>
        </tr>
        <tr>
            <td>
              <label>
              <input type="checkbox" name="send_login_errors" <?php echo $config['send_login_errors']?'checked':''; ?>>Excessive Login attempts
              </label>
            </td>
        </tr>
    </tbody>
</table>
<p style="padding-left:350px;">
    <input class="button" type="submit" name="submit" value="Save Changes">
    <input class="button" type="reset" name="reset" value="Reset Changes">
</p>
</form>
