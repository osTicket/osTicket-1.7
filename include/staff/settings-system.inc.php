<?php
if(!defined('OSTADMININC') || !$thisstaff || !$thisstaff->isAdmin() || !$config) die('Access Denied');

$gmtime = Misc::gmtime();
?>
<h2><?= _('System Settings and Preferences')?> - <span>osTicket (v<?php echo $cfg->getVersion(); ?>)</span></h2>
<form action="settings.php?t=system" method="post" id="save">
<?php csrf_token(); ?>
<input type="hidden" name="t" value="system" >
<table class="form_table settings_table" width="940" border="0" cellspacing="0" cellpadding="2">
    <thead>
        <tr>
            <th colspan="2">
                <h4><?= _('System Settings & Preferences')?></h4>
                <em><b><?= _('General Settings')?></b>: <?= _('Offline mode will disable client interface and only allow admins to login to Staff Control Panel')?></em>
            </th>
        </tr>
    </thead>
    <tbody>

        <tr>
            <td width="220" class="required"><?= _('Helpdesk Status')?>:</td>
            <td>
                <input type="radio" name="isonline"  value="1"   <?php echo $config['isonline']?'checked="checked"':''; ?> /><b><?= _('Online')?></b> <?= _('(Active)')?>
                <input type="radio" name="isonline"  value="0"   <?php echo !$config['isonline']?'checked="checked"':''; ?> /><b><?= _('Offline')?></b> <?= _('(Disabled)')?>
                &nbsp;<font class="error">&nbsp;<?php echo $config['isoffline']?_('osTicket offline'):''; ?></font>
            </td>
        </tr>
        <tr>
            <td width="220" class="required"><?= _('Helpdesk URL')?>:</td>
            <td>
                <input type="text" size="40" name="helpdesk_url" value="<?php echo $config['helpdesk_url']; ?>">
                &nbsp;<font class="error">*&nbsp;<?php echo $errors['helpdesk_url']; ?></font></td>
        </tr>
        <tr>
            <td width="220" class="required"><?= _('Helpdesk Name/Title')?>:</td>
            <td><input type="text" size="40" name="helpdesk_title" value="<?php echo $config['helpdesk_title']; ?>">
                &nbsp;<font class="error">*&nbsp;<?php echo $errors['helpdesk_title']; ?></font></td>
        </tr>
        <tr>
            <td width="220" class="required"><?= _('Default Department')?>:</td>
            <td>
                <select name="default_dept_id">
                    <option value="">&mdash; <?= _('Select Default Department')?> &mdash;</option>
                    <?php
                    $sql='SELECT dept_id,dept_name FROM '.DEPT_TABLE.' WHERE ispublic=1';
                    if(($res=db_query($sql)) && db_num_rows($res)){
                        while (list($id,$name) = db_fetch_row($res)){
                            $selected = ($config['default_dept_id']==$id)?'selected="selected"':''; ?>
                            <option value="<?php echo $id; ?>"<?php echo $selected; ?>><?php echo $name; ?> <?= _('Dept')?></option>
                        <?php
                        }
                    } ?>
                </select>&nbsp;<font class="error">*&nbsp;<?php echo $errors['default_dept_id']; ?></font>
            </td>
        </tr>
        <tr>
            <td width="220" class="required"><?= _('Default Email Templates')?>:</td>
            <td>
                <select name="default_template_id">
                    <option value="">&mdash; <?= _('Select Default Template')?> &mdash;</option>
                    <?php
                    $sql='SELECT tpl_id,name FROM '.EMAIL_TEMPLATE_TABLE.' WHERE isactive=1 AND cfg_id='.db_input($cfg->getId()).' ORDER BY name';
                    if(($res=db_query($sql)) && db_num_rows($res)){
                        while (list($id,$name) = db_fetch_row($res)){
                            $selected = ($config['default_template_id']==$id)?'selected="selected"':''; ?>
                            <option value="<?php echo $id; ?>"<?php echo $selected; ?>><?php echo $name; ?></option>
                        <?php
                        }
                    } ?>
                </select>&nbsp;<font class="error">*&nbsp;<?php echo $errors['default_template_id']; ?></font>
            </td>
        </tr>

        <tr><td><?= _('Default Page Size')?>:</td>
            <td>
                <select name="max_page_size">
                    <?php
                     $pagelimit=$config['max_page_size'];
                    for ($i = 5; $i <= 50; $i += 5) {
                        ?>
                        <option <?php echo $config['max_page_size']==$i?'selected="selected"':''; ?> value="<?php echo $i; ?>"><?php echo $i; ?></option>
                        <?php
                    } ?>
                </select>
            </td>
        </tr>
        <tr>
            <td><?= _('Default Log Level')?>:</td>
            <td>
                <select name="log_level">
                    <option value=0 <?php echo $config['log_level'] == 0 ? 'selected="selected"':''; ?>><?= _('None (Disable Logger)')?></option>
                    <option value=3 <?php echo $config['log_level'] == 3 ? 'selected="selected"':''; ?>> <?= _('DEBUG')?></option>
                    <option value=2 <?php echo $config['log_level'] == 2 ? 'selected="selected"':''; ?>> <?= _('WARN')?></option>
                    <option value=1 <?php echo $config['log_level'] == 1 ? 'selected="selected"':''; ?>> <?= _('ERROR')?></option>
                </select>
                <font class="error">&nbsp;<?php echo $errors['log_level']; ?></font>
            </td>
        </tr>
        <tr>
            <td><?= _('Purge Logs')?>:</td>
            <td>        
                <select name="log_graceperiod">
                    <option value=0 selected><?= _('Never Purge Logs')?></option>
                    <?php
                    for ($i = 1; $i <=12; $i++) {
                        ?>
                        <option <?php echo $config['log_graceperiod']==$i?'selected="selected"':''; ?> value="<?php echo $i; ?>">
                            <?= _('After')?>&nbsp;<?php echo $i; ?>&nbsp;<?php echo ($i>1)?_('Months'):_('Month'); ?></option>
                        <?php
                    } ?>
                </select>
            </td>
        </tr>
        <tr><td><?= _('Password Reset Policy')?>:</th>
            <td>
                <select name="passwd_reset_period">
                   <option value="0"> &mdash; <?= _('None')?> &mdash;</option>
                  <?php
                    for ($i = 1; $i <= 12; $i++) {
                        echo sprintf('<option value="%d" %s>%s%s</option>',
                                $i,(($config['passwd_reset_period']==$i)?'selected="selected"':''),$i>1?_("Every")." $i ":'',$i>1?' '._('Months'):_('Monthly'));
                    }
                    ?>
                </select>
                &nbsp;<font class="error">&nbsp;<?php echo $errors['passwd_reset_period']; ?></font>
            </td>
        </tr>
        <tr><td><?= _('Bind Staff Session to IP')?>:</td>
            <td>
              <input type="checkbox" name="staff_ip_binding" <?php echo $config['staff_ip_binding']?'checked="checked"':''; ?>>
              <em><?= _('(binds staff session to originating IP address upon login)')?></em>
            </td>
        </tr>
        <tr><td><?= _('Staff Excessive Logins')?>:</td>
            <td>
                <select name="staff_max_logins">
                  <?php
                    for ($i = 1; $i <= 10; $i++) {
                        echo sprintf('<option value="%d" %s>%d</option>',$i,(($config['staff_max_logins']==$i)?'selected="selected"':''),$i);
                    }
                    ?>
                </select> <?= _('failed login attempt(s) allowed before a')?>
                <select name="staff_login_timeout">
                  <?php
                    for ($i = 1; $i <= 10; $i++) {
                        echo sprintf('<option value="%d" %s>%d</option>',$i,(($config['staff_login_timeout']==$i)?'selected="selected"':''),$i);
                    }
                    ?>
                </select> <?= _('minute lock-out is enforced.')?>
            </td>
        </tr>
        <tr><td><?= _('Staff Session Timeout')?>:</td>
            <td>
              <input type="text" name="staff_session_timeout" size=6 value="<?php echo $config['staff_session_timeout']; ?>">
                <?= _('Maximum idle time in minutes before a staff member must log in again (enter 0 to disable).')?>
            </td>
        </tr>
        <tr><td><?= _('Client Excessive Logins')?>:</td>
            <td>
                <select name="client_max_logins">
                  <?php
                    for ($i = 1; $i <= 10; $i++) {
                        echo sprintf('<option value="%d" %s>%d</option>',$i,(($config['client_max_logins']==$i)?'selected="selected"':''),$i);
                    }

                    ?>
                </select> <?= _('failed login attempt(s) allowed before a')?>
                <select name="client_login_timeout">
                  <?php
                    for ($i = 1; $i <= 10; $i++) {
                        echo sprintf('<option value="%d" %s>%d</option>',$i,(($config['client_login_timeout']==$i)?'selected="selected"':''),$i);
                    }
                    ?>
                </select> <?= _('minute lock-out is enforced.')?> 
            </td>
        </tr>

        <tr><td><?= _('Client Session Timeout')?>:</td>
            <td>
              <input type="text" name="client_session_timeout" size=6 value="<?php echo $config['client_session_timeout']; ?>">
                &nbsp;<?= _('Maximum idle time in minutes before a client must log in again (enter 0 to disable).')?>
            </td>
        </tr>
        <tr>
            <th colspan="2">
                <em><b><?= _('Date and Time Options')?></b>: <?= _('Please refer to <a href="http://php.net/date" target="_blank">PHP Manual</a> for supported parameters.')?></em>
            </th>
        </tr>
        <tr><td width="220" class="required"><?= _('Time Format')?>:</td>
            <td>
                <input type="text" name="time_format" value="<?php echo $config['time_format']; ?>">
                    &nbsp;<font class="error">*&nbsp;<?php echo $errors['time_format']; ?></font>
                    <em><?php echo Format::date($config['time_format'],$gmtime,$config['timezone_offset'],$config['enable_daylight_saving']); ?></em></td>
        </tr>
        <tr><td width="220" class="required"><?= _('Date Format')?>:</td>
            <td><input type="text" name="date_format" value="<?php echo $config['date_format']; ?>">
                        &nbsp;<font class="error">*&nbsp;<?php echo $errors['date_format']; ?></font>
                        <em><?php echo Format::date($config['date_format'],$gmtime,$config['timezone_offset'],$config['enable_daylight_saving']); ?></em>
            </td>
        </tr>
        <tr><td width="220" class="required"><?= _('Date &amp; Time Format')?>:</td>
            <td><input type="text" name="datetime_format" value="<?php echo $config['datetime_format']; ?>">
                        &nbsp;<font class="error">*&nbsp;<?php echo $errors['datetime_format']; ?></font>
                        <em><?php echo Format::date($config['datetime_format'],$gmtime,$config['timezone_offset'],$config['enable_daylight_saving']); ?></em>
            </td>
        </tr>
        <tr><td width="220" class="required"><?= _('Day, Date &amp; Time Format')?>:</td>
            <td><input type="text" name="daydatetime_format" value="<?php echo $config['daydatetime_format']; ?>">
                        &nbsp;<font class="error">*&nbsp;<?php echo $errors['daydatetime_format']; ?></font>
                        <em><?php echo Format::date($config['daydatetime_format'],$gmtime,$config['timezone_offset'],$config['enable_daylight_saving']); ?></em>
            </td>
        </tr>
        <tr><td width="220" class="required"><?= _('Default Time Zone')?>:</td>
            <td>
                <select name="default_timezone_id">
                    <option value="">&mdash; <?= _('Select Default Time Zone')?> &mdash;</option>
                    <?php
                    $sql='SELECT id, offset,timezone FROM '.TIMEZONE_TABLE.' ORDER BY id';
                    if(($res=db_query($sql)) && db_num_rows($res)){
                        while(list($id,$offset, $tz)=db_fetch_row($res)){
                            $sel=($config['default_timezone_id']==$id)?'selected="selected"':'';
                            echo sprintf('<option value="%d" %s>GMT %s - %s</option>',$id,$sel,$offset,$tz);
                        }
                    }
                    ?>
                </select>
                &nbsp;<font class="error">*&nbsp;<?php echo $errors['default_timezone_id']; ?></font>
            </td>
        </tr>
        <tr>
            <td width="220"><?= _('Daylight Saving')?>:</td>
            <td>
                <input type="checkbox" name="enable_daylight_saving" <?php echo $config['enable_daylight_saving'] ? 'checked="checked"': ''; ?>><?= _('Observe daylight savings')?>
            </td>
        </tr>
    </tbody>
</table>
<p style="padding-left:250px;">
    <input class="button" type="submit" name="submit" value="<?= _('Save Changes')?>">
    <input class="button" type="reset" name="reset" value="<?= _('Reset Changes')?>">
</p>
</form>
