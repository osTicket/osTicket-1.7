<h2><?php echo lang("alert_and_notice"); ?></h2>
<form action="settings.php?t=alerts" method="post" id="save">
<?php csrf_token(); ?>
<input type="hidden" name="t" value="alerts" >
<table class="form_table settings_table" width="940" border="0" cellspacing="0" cellpadding="2">
    <thead>
        <tr>
            <th>
                <h4><?php echo lang("alert_and_notice"); ?> <?php echo lang("sent_to_staff"); ?></h4>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr><th><em><b><?php echo lang("new_ticket_alert"); ?></b>:<?php echo lang("alert_new_ticket"); ?></em></th></tr>
        <tr>
            <td><em><b><?php echo lang("status"); ?>:</b></em> &nbsp;
                <input type="radio" name="ticket_alert_active"  value="1"   <?php echo $config['ticket_alert_active']?'checked':''; ?> /> <?php echo lang("enable"); ?> 
                <input type="radio" name="ticket_alert_active"  value="0"   <?php echo !$config['ticket_alert_active']?'checked':''; ?> /><?php echo lang("disable"); ?> 
                &nbsp;&nbsp;<font class="error">&nbsp;<?php echo $errors['ticket_alert_active']; ?></font></em>
             </td>
        </tr>
        <tr>
            <td>
                <input type="checkbox" name="ticket_alert_admin" <?php echo $config['ticket_alert_admin']?'checked':''; ?>> <?php echo lang("admin_email"); ?>  <em>(<?php echo $cfg->getAdminEmail(); ?>)</em>
            </td>
        </tr>
        <tr>    
            <td>
                <input type="checkbox" name="ticket_alert_dept_manager" <?php echo $config['ticket_alert_dept_manager']?'checked':''; ?>> <?php echo lang("depart_manager"); ?> 
            </td>
        </tr>
        <tr>
            <td>
                <input type="checkbox" name="ticket_alert_dept_members" <?php echo $config['ticket_alert_dept_members']?'checked':''; ?>> <?php echo lang("depart_members"); ?>  <em>(<?php echo lang("spammy"); ?> )</em>
            </td>
        </tr>
        <tr><th><em><b><?php echo lang("new_message_alert"); ?> </b>: <?php echo lang("ticket_message"); ?> </em></th></tr>
        <tr>
            <td><em><b><?php echo lang("status"); ?> :</b></em> &nbsp; 
              <input type="radio" name="message_alert_active"  value="1"   <?php echo $config['message_alert_active']?'checked':''; ?> /><?php echo lang("enable"); ?> 
              &nbsp;&nbsp;
              <input type="radio" name="message_alert_active"  value="0"   <?php echo !$config['message_alert_active']?'checked':''; ?> /><?php echo lang("disable"); ?> 
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox" name="message_alert_laststaff" <?php echo $config['message_alert_laststaff']?'checked':''; ?>> <?php echo lang("last_respondent"); ?> 
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox" name="message_alert_assigned" <?php echo $config['message_alert_assigned']?'checked':''; ?>> <?php echo lang("assigned_staff"); ?> 
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox" name="message_alert_dept_manager" <?php echo $config['message_alert_dept_manager']?'checked':''; ?>> <?php echo lang("depart_manager"); ?>  <em>(<?php echo lang("spammy"); ?> )</em>
            </td>
        </tr>
        <tr><th><em><b><?php echo lang("alert_internal_note"); ?></b>:<?php echo lang("internal_note_post"); ?>.</em></th></tr>
        <tr>
            <td><em><b><?php echo lang("status"); ?> :</b></em> &nbsp;
              <input type="radio" name="note_alert_active"  value="1"   <?php echo $config['note_alert_active']?'checked':''; ?> /><?php echo lang("enable"); ?> 
              &nbsp;&nbsp;
              <input type="radio" name="note_alert_active"  value="0"   <?php echo !$config['note_alert_active']?'checked':''; ?> /><?php echo lang("disable"); ?> 
              &nbsp;&nbsp;&nbsp;<font class="error">&nbsp;<?php echo $errors['note_alert_active']; ?></font>
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox" name="note_alert_laststaff" <?php echo $config['note_alert_laststaff']?'checked':''; ?>> <?php echo lang("last_respondent"); ?> 
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox" name="note_alert_assigned" <?php echo $config['note_alert_assigned']?'checked':''; ?>> <?php echo lang("assigned_staff"); ?> 
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox" name="note_alert_dept_manager" <?php echo $config['note_alert_dept_manager']?'checked':''; ?>> <?php echo lang("depart_manager"); ?>  <em>(<?php echo lang("spammy"); ?> )</em>
            </td>
        </tr>
        <tr><th><em><b><?php echo lang("ticket_assig_alert"); ?> </b>: <?php echo lang("staff_ticket_assig"); ?> .</em></th></tr>
        <tr>
            <td><em><b><?php echo lang("status"); ?> : </b></em> &nbsp;
              <input name="assigned_alert_active" value="1" checked="checked" type="radio"><?php echo lang("enable"); ?> 
              &nbsp;&nbsp;
              <input name="assigned_alert_active" value="0" type="radio"><?php echo lang("disable"); ?> 
               &nbsp;&nbsp;&nbsp;<font class="error">&nbsp;<?php echo $errors['assigned_alert_active']; ?></font>
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox" name="assigned_alert_staff" <?php echo $config['assigned_alert_staff']?'checked':''; ?>> <?php echo lang("assigned_staff"); ?> 
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox"name="assigned_alert_team_lead" <?php echo $config['assigned_alert_team_lead']?'checked':''; ?>><?php echo lang("team_lead"); ?><em>(<?php echo lang("team_assigment"); ?> )</em>
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox"name="assigned_alert_team_members" <?php echo $config['assigned_alert_team_members']?'checked':''; ?>>
                <?php echo lang("team_members"); ?> <em>(<?php echo lang("spammy"); ?> )</em>
            </td>
        </tr>
        <tr><th><em><b><?php echo lang("ticket_transfer"); ?> </b>: <?php echo lang("alert_on_target_dep"); ?>.</em></th></tr>
        <tr>
            <td><em><b><?php echo lang("status"); ?> :</b></em> &nbsp;
              <input type="radio" name="transfer_alert_active"  value="1"   <?php echo $config['transfer_alert_active']?'checked':''; ?> /><?php echo lang("enable"); ?> 
              <input type="radio" name="transfer_alert_active"  value="0"   <?php echo !$config['transfer_alert_active']?'checked':''; ?> /><?php echo lang("disable"); ?> 
              &nbsp;&nbsp;&nbsp;<font class="error">&nbsp;<?php echo $errors['alert_alert_active']; ?></font>
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox" name="transfer_alert_assigned" <?php echo $config['transfer_alert_assigned']?'checked':''; ?>> <?php echo lang("assigned_staff"); ?> /<?php echo lang("team"); ?> 
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox" name="transfer_alert_dept_manager" <?php echo $config['transfer_alert_dept_manager']?'checked':''; ?>> <?php echo lang("depart_manager"); ?> 
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox" name="transfer_alert_dept_members" <?php echo $config['transfer_alert_dept_members']?'checked':''; ?>>
                <?php echo lang("depart_members"); ?>  <em>(<?php echo lang("spammy"); ?> )</em>
            </td>
        </tr>
        <tr><th><em><b><?php echo lang("overdue_ticket"); ?> </b>: <?php echo lang("ticket_bec_overdue"); ?>.</em></th></tr>
        <tr>
            <td><em><b><?php echo lang("status"); ?> :</b></em> &nbsp;
              <input type="radio" name="overdue_alert_active"  value="1"   <?php echo $config['overdue_alert_active']?'checked':''; ?> /><?php echo lang("enable"); ?> 
              <input type="radio" name="overdue_alert_active"  value="0"   <?php echo !$config['overdue_alert_active']?'checked':''; ?> /><?php echo lang("disable"); ?> 
              &nbsp;&nbsp;&nbsp;<font class="error">&nbsp;<?php echo $errors['overdue_alert_active']; ?></font>
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox" name="overdue_alert_assigned" <?php echo $config['overdue_alert_assigned']?'checked':''; ?>> <?php echo lang("assigned_staff"); ?> /<?php echo lang("team"); ?> 
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox" name="overdue_alert_dept_manager" <?php echo $config['overdue_alert_dept_manager']?'checked':''; ?>> <?php echo lang("depart_manager"); ?> 
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox" name="overdue_alert_dept_members" <?php echo $config['overdue_alert_dept_members']?'checked':''; ?>> <?php echo lang("depart_members"); ?>  <em>(<?php echo lang("spammy"); ?> )</em>
            </td>
        </tr>
        <tr><th><em><b><?php echo lang("system_alerts"); ?> </b>: <?php echo lang("error_by_default"); ?>  (<?php echo $cfg->getAdminEmail(); ?>)</em></th></tr>
        <tr>
            <td>
              <input type="checkbox" name="send_sys_errors" checked="checked" disabled="disabled"><?php echo lang("system_errors"); ?> 
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox" name="send_sql_errors" <?php echo $config['send_sql_errors']?'checked':''; ?>><?php echo lang("sql_errors"); ?> 
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox" name="send_login_errors" <?php echo $config['send_login_errors']?'checked':''; ?>><?php echo lang("excess_login_att"); ?> 
            </td>
        </tr>
    </tbody>
</table>
<p style="padding-left:350px;">
    <input class="button" type="submit" name="submit" value="<?php echo lang("save_changes"); ?> ">
    <input class="button" type="reset" name="reset" value="<?php echo lang("reset_changes"); ?> ">
</p>
</form>
