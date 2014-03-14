<?php
if(!defined('OSTADMININC') || !$thisstaff || !$thisstaff->isAdmin() || !$config) die('Access Denied');

$gmtime = Misc::gmtime();
?>
<h2><?php echo lang("system_settings"); ?> <?php echo lang("and_preferences"); ?> - <span>osTicket (v<?php echo $cfg->getVersion(); ?>)</span></h2>
<form action="settings.php?t=system" method="post" id="save">
<?php csrf_token(); ?>
<input type="hidden" name="t" value="system" >
<table class="form_table settings_table" width="940" border="0" cellspacing="0" cellpadding="2">
    <thead>
        <tr>
            <th colspan="2">
                <h4><?php echo lang("system_settings"); ?> <?php echo lang("and_preferences"); ?></h4>
                <em><b><?php echo lang("general_settings"); ?></b>: <?php echo lang("Offline_mode"); ?></em>
            </th>
        </tr>
    </thead>
    <tbody>

        <tr>
            <td width="220" class="required"><?php echo lang("helpdesk_status"); ?>:</td>
            <td>
                <input type="radio" name="isonline"  value="1"   <?php echo $config['isonline']?'checked="checked"':''; ?> /><b><?php echo lang('online'); ?></b> (<?php echo lang('Active'); ?>)
                <input type="radio" name="isonline"  value="0"   <?php echo !$config['isonline']?'checked="checked"':''; ?> /><b><?php echo lang('Offline'); ?></b> (<?php echo lang('Disabled'); ?>)
                &nbsp;<font class="error">&nbsp;<?php echo $config['isoffline']?'osTicket offline':''; ?></font>
            </td>
        </tr>
        <tr>
            <td width="220" class="required"><?php echo lang("helped_url"); ?>:</td>
            <td>
                <input type="text" size="40" name="helpdesk_url" value="<?php echo $config['helpdesk_url']; ?>">
                &nbsp;<font class="error">*&nbsp;<?php echo $errors['helpdesk_url']; ?></font></td>
        </tr>
        <tr>
            <td width="220" class="required"><?php echo lang("helpdesk_title"); ?>:</td>
            <td><input type="text" size="40" name="helpdesk_title" value="<?php echo $config['helpdesk_title']; ?>">
                &nbsp;<font class="error">*&nbsp;<?php echo $errors['helpdesk_title']; ?></font></td>
        </tr>
        <tr>
            <td width="220" class="required"><?php echo lang("default_department"); ?>:</td>
            <td>
                <select name="default_dept_id">
                    <option value="">&mdash; <?php echo lang("default_department"); ?>&mdash;</option>
                    <?php
                    $sql='SELECT dept_id,dept_name FROM '.DEPT_TABLE.' WHERE ispublic=1';
                    if(($res=db_query($sql)) && db_num_rows($res)){
                        while (list($id, $name) = db_fetch_row($res)){
                            $selected = ($config['default_dept_id']==$id)?'selected="selected"':''; ?>
                            <option value="<?php echo $id; ?>"<?php echo $selected; ?>><?php echo $name; ?> Dept</option>
                        <?php
                        }
                    } ?>
                </select>&nbsp;<font class="error">*&nbsp;<?php echo $errors['default_dept_id']; ?></font>
            </td>
        </tr>
        <tr>
            <td width="220" class="required"><?php echo lang("email_template"); ?>:</td>
            <td>
                <select name="default_template_id">
                    <option value="">&mdash; <?php echo lang("default_template"); ?> &mdash;</option>
                    <?php
                    $sql='SELECT tpl_id,name FROM '.EMAIL_TEMPLATE_GRP_TABLE.' WHERE isactive=1 ORDER BY name';
                    if(($res=db_query($sql)) && db_num_rows($res)){
                        while (list($id, $name) = db_fetch_row($res)){
                            $selected = ($config['default_template_id']==$id)?'selected="selected"':''; ?>
                            <option value="<?php echo $id; ?>"<?php echo $selected; ?>><?php echo $name; ?></option>
                        <?php
                        }
                    } ?>
                </select>&nbsp;<font class="error">*&nbsp;<?php echo $errors['default_template_id']; ?></font>
            </td>
        </tr>

        <tr><td><?php echo lang("default_language"); ?></td>
            <td>
                <select name="default_language">
                    <?php $default_language=getDefaultLanguage(); ?>
                    <?php foreach (getAssignedLanguages() as $key => $value): ?>
                        <option <?php echo $key.'.php' ==$default_language ?'selected="selected"':''; ?> value="<?php echo $key; ?>"><?php echo $value; ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>

        <tr><td><?php echo lang("Default Page Size"); ?>:</td>
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
            <td><?php echo lang("default_log_level"); ?>:</td>
            <td>
                <select name="log_level">
                    <option value=0 <?php echo $config['log_level'] == 0 ? 'selected="selected"':''; ?>> <?php echo lang('disable_logger'); ?></option>
                    <option value=3 <?php echo $config['log_level'] == 3 ? 'selected="selected"':''; ?>> <?php echo lang('debug'); ?></option>
                    <option value=2 <?php echo $config['log_level'] == 2 ? 'selected="selected"':''; ?>> <?php echo lang('warn'); ?></option>
                    <option value=1 <?php echo $config['log_level'] == 1 ? 'selected="selected"':''; ?>> <?php echo lang('error'); ?></option>
                </select>
                <font class="error">&nbsp;<?php echo $errors['log_level']; ?></font>
            </td>
        </tr>
        <tr>
            <td><?php echo lang("purge_logs"); ?>:</td>
            <td>
                <select name="log_graceperiod">
                    <option value=0 selected><?php echo lang('never_purge_logs'); ?></option>
                    <?php
                    for ($i = 1; $i <=12; $i++) {
                        ?>
                        <option <?php echo $config['log_graceperiod']==$i?'selected="selected"':''; ?> value="<?php echo $i; ?>">
                           <?php echo lang("after"); ?>&nbsp;<?php echo $i; ?>&nbsp;<?php echo ($i>1)?lang('months'):lang('month'); ?></option>
                        <?php
                    } ?>
                </select>
            </td>
        </tr>
        <tr>
            <th colspan="2">
                <em><b><?php echo lang("Authentication Settings"); ?></b></em>
            </th>
        </tr>
        <tr><td><?php echo lang("Password Change Policy"); ?>:</th>
            <td>
                <select name="passwd_reset_period">
                   <option value="0"> &mdash; <?php echo lang("none"); ?> &mdash;</option>
                  <?php
                    for ($i = 1; $i <= 12; $i++) {
                        echo sprintf('<option value="%d" %s>%s%s</option>',
                                $i,(($config['passwd_reset_period']==$i)?'selected="selected"':''), $i>1? lang("Every")." $i ":'', $i>1?' '.lang('Months'): lang('Monthly'));
                    }
                    ?>
                </select>
                &nbsp;<font class="error">&nbsp;<?php echo $errors['passwd_reset_period']; ?></font>
            </td>
        </tr>
        <tr><td><?php echo lang("Allow Password Resets"); ?>:</th>
            <td>
              <input type="checkbox" name="allow_pw_reset" <?php echo $config['allow_pw_reset']?'checked="checked"':''; ?>>
              <em><?php echo lang('Enables the') ?> <u><?php echo lang('Forgot my password') ?></u> <?php echo lang('link on the staff control panel') ?></em>
            </td>
        </tr>
        <tr><td><?php echo lang('Password Reset Window:') ?></th>
            <td>
              <input type="text" name="pw_reset_window" size="6" value="<?php
                    echo $config['pw_reset_window']; ?>">
                <?php echo lang('Maximum time')?> <em><?php echo lang('in minutes') ?></em> <?php echo lang('a password reset token can be valid.') ?>
                &nbsp;<font class="error">&nbsp;<?php echo $errors['pw_reset_window']; ?></font>
            </td>
        </tr>
        <tr><td><?php echo lang('Staff Excessive Logins') ?>:</td>
            <td>
                <select name="staff_max_logins">
                  <?php
                    for ($i = 1; $i <= 10; $i++) {
                        echo sprintf('<option value="%d" %s>%d</option>', $i,(($config['staff_max_logins']==$i)?'selected="selected"':''), $i);
                    }
                    ?>
                </select> <?php echo lang("fail_login_attempt"); ?>
                <select name="staff_login_timeout">
                  <?php
                    for ($i = 1; $i <= 10; $i++) {
                        echo sprintf('<option value="%d" %s>%d</option>', $i,(($config['staff_login_timeout']==$i)?'selected="selected"':''), $i);
                    }
                    ?>
                </select> <?php echo lang("minute_lock_out"); ?>.
            </td>
        </tr>
        <tr><td><?php echo lang("staff_sesion_tout"); ?>:</td>
            <td>
              <input type="text" name="staff_session_timeout" size=6 value="<?php echo $config['staff_session_timeout']; ?>">
                <?php echo lang('Maximum idle time in minutes before a staff member must log in again (enter 0 to disable).') ?>
            </td>
        </tr>
        <tr><td><?php echo lang("cli_exces_logins"); ?>:</td>
            <td>
                <select name="client_max_logins">
                  <?php
                    for ($i = 1; $i <= 10; $i++) {
                        echo sprintf('<option value="%d" %s>%d</option>', $i,(($config['client_max_logins']==$i)?'selected="selected"':''), $i);
                    }

                    ?>
                </select> <?php echo lang("fail_login_attempt"); ?>
                <select name="client_login_timeout">
                  <?php
                    for ($i = 1; $i <= 10; $i++) {
                        echo sprintf('<option value="%d" %s>%d</option>', $i,(($config['client_login_timeout']==$i)?'selected="selected"':''), $i);
                    }
                    ?>
                </select> <?php echo lang("minute_lock_out"); ?>. 
            </td>
        </tr>

        <tr><td><?php echo lang("cli_sesion_timeout"); ?>:</td>
            <td>
              <input type="text" name="client_session_timeout" size=6 value="<?php echo $config['client_session_timeout']; ?>">
                &nbsp;<?php echo lang("max_idle_time_cli"); ?>.
            </td>
        </tr>
        <tr><td><?php echo lang('Bind Staff Session to IP') ?>:</td>
            <td>
              <input type="checkbox" name="staff_ip_binding" <?php echo $config['staff_ip_binding']?'checked="checked"':''; ?>>
              <em>(<?php echo lang('binds staff session to originating IP address upon login') ?>)</em>
            </td>
        </tr>
        <tr>
            <th colspan="2">
                <em><b><?php echo lang("date_and_options"); ?></b>: <?php echo lang("refer_to"); ?> <a href="http://php.net/date" target="_blank"><?php echo lang("php_manual"); ?></a> <?php echo lang("support_paramet"); ?>.</em>
            </th>
        </tr>
        <tr><td width="220" class="required"><?php echo lang("time_format"); ?>:</td>
            <td>
                <input type="text" name="time_format" value="<?php echo $config['time_format']; ?>">
                    &nbsp;<font class="error">*&nbsp;<?php echo $errors['time_format']; ?></font>
                    <em><?php echo Format::date($config['time_format'], $gmtime, $config['tz_offset'], $config['enable_daylight_saving']); ?></em></td>
        </tr>
        <tr><td width="220" class="required"><?php echo lang("date_format"); ?>:</td>
            <td><input type="text" name="date_format" value="<?php echo $config['date_format']; ?>">
                        &nbsp;<font class="error">*&nbsp;<?php echo $errors['date_format']; ?></font>
                        <em><?php echo Format::date($config['date_format'], $gmtime, $config['tz_offset'], $config['enable_daylight_saving']); ?></em>
            </td>
        </tr>
        <tr><td width="220" class="required"><?php echo lang("time_format"); ?>:</td>
            <td><input type="text" name="datetime_format" value="<?php echo $config['datetime_format']; ?>">
                        &nbsp;<font class="error">*&nbsp;<?php echo $errors['datetime_format']; ?></font>
                        <em><?php echo Format::date($config['datetime_format'], $gmtime, $config['tz_offset'], $config['enable_daylight_saving']); ?></em>
            </td>
        </tr>
        <tr><td width="220" class="required"><?php echo lang("day"); ?>, <?php echo lang("time_format"); ?>:</td>
            <td><input type="text" name="daydatetime_format" value="<?php echo $config['daydatetime_format']; ?>">
                        &nbsp;<font class="error">*&nbsp;<?php echo $errors['daydatetime_format']; ?></font>
                        <em><?php echo Format::date($config['daydatetime_format'], $gmtime, $config['tz_offset'], $config['enable_daylight_saving']); ?></em>
            </td>
        </tr>
        <tr><td width="220" class="required"><?php echo lang("default_time_zone"); ?>:</td>
            <td>
                <select name="default_timezone_id">
                    <option value="">&mdash; <?php echo lang("select"); ?> <?php echo lang("default_time_zone"); ?> &mdash;</option>
                    <?php
                    $sql='SELECT id, offset,timezone FROM '.TIMEZONE_TABLE.' ORDER BY id';
                    if(($res=db_query($sql)) && db_num_rows($res)){
                        while(list($id, $offset, $tz)=db_fetch_row($res)){
                            $sel=($config['default_timezone_id']==$id)?'selected="selected"':'';
                            echo sprintf('<option value="%d" %s>GMT %s - %s</option>', $id, $sel, $offset, $tz);
                        }
                    }
                    ?>
                </select>
                &nbsp;<font class="error">*&nbsp;<?php echo $errors['default_timezone_id']; ?></font>
            </td>
        </tr>
        <tr>
            <td width="220"><?php echo lang("daylight_saving"); ?>:</td>
            <td>
                <input type="checkbox" name="enable_daylight_saving" <?php echo $config['enable_daylight_saving'] ? 'checked="checked"': ''; ?>><?php echo lang('observ_dayl_saving'); ?>
            </td>
        </tr>
    </tbody>
</table>
<p style="padding-left:250px;">
    <input class="button" type="submit" name="submit" value="<?php echo lang("save_changes"); ?>">
    <input class="button" type="reset" name="reset" value="<?php echo lang("reset_changes"); ?>">
</p>
</form>
