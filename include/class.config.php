<?php
/*********************************************************************
    class.config.php

    osTicket config info manager.

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/

require_once(INCLUDE_DIR.'class.email.php');

class Config {
    var $config = array();

    var $section = null;                    # Default namespace ('core')
    var $table = 'config';                  # Table name (with prefix)
    var $section_column = 'namespace';      # namespace column name

    var $session = null;                    # Session-backed configuration

    function Config($section=null) {
        if ($section)
            $this->section = $section;

        if ($this->section === null)
            return false;

        if (!isset($_SESSION['cfg:'.$this->section]))
            $_SESSION['cfg:'.$this->section] = array();
        $this->session = &$_SESSION['cfg:'.$this->section];

        $sql='SELECT id, `key`, value FROM '.$this->table
            .' WHERE `'.$this->section_column.'` = '.db_input($this->section);

        if(($res=db_query($sql)) && db_num_rows($res))
            while ($row = db_fetch_array($res))
                $this->config[$row['key']] = $row;
    }

    function getNamespace() {
        return $this->section;
    }

    function get($key, $default=null) {
        if (isset($this->config[$key]))
            return $this->config[$key]['value'];
        elseif (isset($this->session[$key]))
            return $this->session[$key];
        elseif ($default !== null)
            return $this->set($key, $default);
        return null;
    }

    function exists($key) {
        return $this->get($key, null) ? true : false;
    }

    function set($key, $value) {
        return ($this->update($key, $value)) ? $value : null;
    }

    function persist($key, $value) {
        $this->session[$key] = $value;
        return true;
    }

    function create($key, $value) {
        $sql = 'INSERT INTO '.$this->table
            .' SET `'.$this->section_column.'`='.db_input($this->section)
            .', `key`='.db_input($key)
            .', value='.db_input($value);
        if (!db_query($sql) || !($id=db_insert_id()))
            return false;

        $this->config[$key] = array('key'=>$key, 'value'=>$value, 'id'=>$id);
        return true;
    }

    function update($key, $value) {
        if (!isset($this->config[$key]))
            return $this->create($key, $value);

        $setting = &$this->config[$key];
        if ($setting['value'] == $value)
            return true;

        if (!db_query('UPDATE '.$this->table.' SET updated=NOW(), value='
                .db_input($value).' WHERE id='.db_input($setting['id'])))
            return false;

        $setting['value'] = $value;
        return true;
    }

    function updateAll($updates) {
        foreach ($updates as $key=>$value)
            if (!$this->update($key, $value))
                return false;
        return true;
    }
}

class OsticketConfig extends Config {
    var $table = CONFIG_TABLE;
    var $section = 'core';

    var $defaultDept;   //Default Department
    var $defaultSLA;   //Default SLA
    var $defaultEmail;  //Default Email
    var $alertEmail;  //Alert Email
    var $defaultSMTPEmail; //Default  SMTP Email

    function OsticketConfig($section=null) {
        parent::Config($section);

        if (count($this->config) == 0) {
            // Fallback for osticket < 1.7@852ca89e
            $sql='SELECT * FROM '.$this->table.' WHERE id = 1';
            if (($res=db_query($sql)) && db_num_rows($res))
                foreach (db_fetch_array($res) as $key=>$value)
                    $this->config[$key] = array('value'=>$value);
        }

        //Get the default time zone
        // We can't JOIN timezone table above due to upgrade support.
        if ($this->get('default_timezone_id')) {
            if (!$this->exists('tz_offset'))
                $this->persist('tz_offset',
                    Timezone::getOffsetById($this->get('default_timezone_id')));
        } else
            // Previous osTicket versions saved the offset value instead of
            // a timezone instance. This is compatibility for the upgrader
            $this->persist('tz_offset', 0);

        return true;
    }

    function isHelpDeskOffline() {
        return !$this->isOnline();
    }

    function isHelpDeskOnline() {
        return $this->isOnline();
    }

    function isOnline() {
        return ($this->get('isonline'));
    }

    function isKnowledgebaseEnabled() {
        require_once(INCLUDE_DIR.'class.faq.php');
        return ($this->get('enable_kb') && FAQ::countPublishedFAQs());
    }

    function getVersion() {
        return THIS_VERSION;
    }

    function getSchemaSignature($section=null) {

        if (!$section && ($v=$this->get('schema_signature')))
            return $v;

        // 1.7 after namespaced configuration, other namespace
        if ($section) {
            $sql='SELECT value FROM '.$this->table
                .' WHERE `key` = "schema_signature" and namespace='.db_input($section);
            if (($res=db_query($sql, false)) && db_num_rows($res))
                return db_result($res);
        }

        // 1.7 before namespaced configuration
        $sql='SELECT `schema_signature` FROM '.$this->table
            .' WHERE id=1';
        if (($res=db_query($sql, false)) && db_num_rows($res))
            return db_result($res);

        // old version 1.6
        return md5(self::getDBVersion());
    }

    function getDBTZoffset() {
        if (!$this->exists('db_tz_offset')) {
            $sql='SELECT (TIME_TO_SEC(TIMEDIFF(NOW(), UTC_TIMESTAMP()))/3600) as db_tz_offset';
            if(($res=db_query($sql)) && db_num_rows($res))
                $this->persist('db_tz_offset', db_result($res));
        }
        return $this->get('db_tz_offset');
    }

    /* Date & Time Formats */
    function observeDaylightSaving() {
        return ($this->get('enable_daylight_saving'));
    }
    function getTimeFormat() {
        return $this->get('time_format');
    }
    function getDateFormat() {
        return $this->get('date_format');
    }

    function getDateTimeFormat() {
        return $this->get('datetime_format');
    }

    function getDayDateTimeFormat() {
        return $this->get('daydatetime_format');
    }

    function getConfigInfo() {
        $info = array();
        foreach ($this->config as $key=>$setting)
            $info[$key] = $setting['value'];
        return $info;
    }

    function getTitle() {
        return $this->get('helpdesk_title');
    }

    function getUrl() {
        return $this->get('helpdesk_url');
    }

    function getBaseUrl() { //Same as above with no trailing slash.
        return rtrim($this->getUrl(),'/');
    }

    function getTZOffset() {
        return $this->get('tz_offset');
    }

    function getPageSize() {
        return $this->get('max_page_size');
    }

    function getGracePeriod() {
        return $this->get('overdue_grace_period');
    }

    function getPasswdResetPeriod() {
        return $this->get('passwd_reset_period');
    }

    function showRelatedTickets() {
        return $this->get('show_related_tickets');
    }

    function showNotesInline(){
        return $this->get('show_notes_inline');
    }

    function getClientTimeout() {
        return $this->getClientSessionTimeout();
    }

    function getClientSessionTimeout() {
        return $this->get('client_session_timeout')*60;
    }

    function getClientLoginTimeout() {
        return $this->get('client_login_timeout')*60;
    }

    function getClientMaxLogins() {
        return $this->get('client_max_logins');
    }

    function getStaffTimeout() {
        return $this->getStaffSessionTimeout();
    }

    function getStaffSessionTimeout() {
        return $this->get('staff_session_timeout')*60;
    }

    function getStaffLoginTimeout() {
        return $this->get('staff_login_timeout')*60;
    }

    function getStaffMaxLogins() {
        return $this->get('staff_max_logins');
    }

    function getLockTime() {
        return $this->get('autolock_minutes');
    }

    function getDefaultDeptId() {
        return $this->get('default_dept_id');
    }

    function getDefaultDept() {

        if(!$this->defaultDept && $this->getDefaultDeptId())
            $this->defaultDept=Dept::lookup($this->getDefaultDeptId());

        return $this->defaultDept;
    }

    function getDefaultEmailId() {
        return $this->get('default_email_id');
    }

    function getDefaultEmail() {

        if(!$this->defaultEmail && $this->getDefaultEmailId())
            $this->defaultEmail=Email::lookup($this->getDefaultEmailId());

        return $this->defaultEmail;
    }

    function getDefaultEmailAddress() {
        $email=$this->getDefaultEmail();
        return $email?$email->getAddress():null;
    }

    function getDefaultSLAId() {
        return $this->get('default_sla_id');
    }

    function getDefaultSLA() {

        if(!$this->defaultSLA && $this->getDefaultSLAId())
            $this->defaultSLA=SLA::lookup($this->getDefaultSLAId());

        return $this->defaultSLA;
    }

    function getAlertEmailId() {
        return $this->get('alert_email_id');
    }

    function getAlertEmail() {

        if(!$this->alertEmail && $this->get('alert_email_id'))
            $this->alertEmail= new Email($this->get('alert_email_id'));
        return $this->alertEmail;
    }

    function getDefaultSMTPEmail() {

        if(!$this->defaultSMTPEmail && $this->get('default_smtp_id'))
            $this->defaultSMTPEmail= new Email($this->get('default_smtp_id'));
        return $this->defaultSMTPEmail;
    }

    function getDefaultPriorityId() {
        return $this->get('default_priority_id');
    }

    function getDefaultTemplateId() {
        return $this->get('default_template_id');
    }

    function getDefaultTemplate() {

        if(!$this->defaultTemplate && $this->getDefaultTemplateId())
            $this->defaultTemplate = Template::lookup($this->getDefaultTemplateId());

        return $this->defaultTemplate;
    }

    function getMaxOpenTickets() {
         return $this->get('max_open_tickets');
    }

    function getMaxFileSize() {
        return $this->get('max_file_size');
    }

    function getStaffMaxFileUploads() {
        return $this->get('max_staff_file_uploads');
    }

    function getClientMaxFileUploads() {
        //TODO: change max_user_file_uploads to max_client_file_uploads
        return $this->get('max_user_file_uploads');
    }

    function getLogLevel() {
        return $this->get('log_level');
    }

    function getLogGracePeriod() {
        return $this->get('log_graceperiod');
    }

    function logTicketActivity() {
        return $this->get('log_ticket_activity');
    }

    function clickableURLS() {
        return ($this->get('clickable_urls'));
    }

    function enableStaffIPBinding() {
        return ($this->get('staff_ip_binding'));
    }

    function isCaptchaEnabled() {
        return (extension_loaded('gd') && function_exists('gd_info') && $this->get('enable_captcha'));
    }

    function isAutoCronEnabled() {
        return ($this->get('enable_auto_cron'));
    }

    function isEmailPollingEnabled() {
        return ($this->get('enable_mail_polling'));
    }

    function allowPriorityChange() {
        return ($this->get('allow_priority_change'));
    }


    function useEmailPriority() {
        return ($this->get('use_email_priority'));
    }

    function getAdminEmail() {
         return $this->get('admin_email');
    }

    function getReplySeparator() {
        return $this->get('reply_separator');
    }

    function stripQuotedReply() {
        return ($this->get('strip_quoted_reply'));
    }

    function saveEmailHeaders() {
        return true; //No longer an option...hint: big plans for headers coming!!
    }

    function useRandomIds() {
        return ($this->get('random_ticket_ids'));
    }

    /* autoresponders  & Alerts */
    function autoRespONNewTicket() {
        return ($this->get('ticket_autoresponder'));
    }

    function autoRespONNewMessage() {
        return ($this->get('message_autoresponder'));
    }

    function notifyONNewStaffTicket() {
        return ($this->get('ticket_notice_active'));
    }

    function alertONNewMessage() {
        return ($this->get('message_alert_active'));
    }

    function alertLastRespondentONNewMessage() {
        return ($this->get('message_alert_laststaff'));
    }

    function alertAssignedONNewMessage() {
        return ($this->get('message_alert_assigned'));
    }

    function alertDeptManagerONNewMessage() {
        return ($this->get('message_alert_dept_manager'));
    }

    function alertONNewNote() {
        return ($this->get('note_alert_active'));
    }

    function alertLastRespondentONNewNote() {
        return ($this->get('note_alert_laststaff'));
    }

    function alertAssignedONNewNote() {
        return ($this->get('note_alert_assigned'));
    }

    function alertDeptManagerONNewNote() {
        return ($this->get('note_alert_dept_manager'));
    }

    function alertONNewTicket() {
        return ($this->get('ticket_alert_active'));
    }

    function alertAdminONNewTicket() {
        return ($this->get('ticket_alert_admin'));
    }

    function alertDeptManagerONNewTicket() {
        return ($this->get('ticket_alert_dept_manager'));
    }

    function alertDeptMembersONNewTicket() {
        return ($this->get('ticket_alert_dept_members'));
    }

    function alertONTransfer() {
        return ($this->get('transfer_alert_active'));
    }

    function alertAssignedONTransfer() {
        return ($this->get('transfer_alert_assigned'));
    }

    function alertDeptManagerONTransfer() {
        return ($this->get('transfer_alert_dept_manager'));
    }

    function alertDeptMembersONTransfer() {
        return ($this->get('transfer_alert_dept_members'));
    }

    function alertONAssignment() {
        return ($this->get('assigned_alert_active'));
    }

    function alertStaffONAssignment() {
        return ($this->get('assigned_alert_staff'));
    }

    function alertTeamLeadONAssignment() {
        return ($this->get('assigned_alert_team_lead'));
    }

    function alertTeamMembersONAssignment() {
        return ($this->get('assigned_alert_team_members'));
    }


    function alertONOverdueTicket() {
        return ($this->get('overdue_alert_active'));
    }

    function alertAssignedONOverdueTicket() {
        return ($this->get('overdue_alert_assigned'));
    }

    function alertDeptManagerONOverdueTicket() {
        return ($this->get('overdue_alert_dept_manager'));
    }

    function alertDeptMembersONOverdueTicket() {
        return ($this->get('overdue_alert_dept_members'));
    }

    function autoAssignReopenedTickets() {
        return ($this->get('auto_assign_reopened_tickets'));
    }

    function showAssignedTickets() {
        return ($this->get('show_assigned_tickets'));
    }

    function showAnsweredTickets() {
        return ($this->get('show_answered_tickets'));
    }

    function hideStaffName() {
        return ($this->get('hide_staff_name'));
    }

    function sendOverLimitNotice() {
        return ($this->get('overlimit_notice_active'));
    }

    /* Error alerts sent to admin email when enabled */
    function alertONSQLError() {
        return ($this->get('send_sql_errors'));
    }
    function alertONLoginError() {
        return ($this->get('send_login_errors'));
    }

    function alertONMailParseError() {
        return ($this->get('send_mailparse_errors'));
    }



    /* Attachments */
    function getAllowedFileTypes() {
        return trim($this->get('allowed_filetypes'));
    }

    function emailAttachments() {
        return ($this->get('email_attachments'));
    }

    function allowAttachments() {
        return ($this->get('allow_attachments'));
    }

    function allowOnlineAttachments() {
        return ($this->allowAttachments() && $this->get('allow_online_attachments'));
    }

    function allowAttachmentsOnlogin() {
        return ($this->allowOnlineAttachments() && $this->get('allow_online_attachments_onlogin'));
    }

    function allowEmailAttachments() {
        return ($this->allowAttachments() && $this->get('allow_email_attachments'));
    }

    //TODO: change db field to allow_api_attachments - which will include  email/json/xml attachments
    //       terminology changed on the UI
    function allowAPIAttachments() {
        return $this->allowEmailAttachments();
    }

    /* Needed by upgrader on 1.6 and older releases upgrade - not not remove */
    function getUploadDir() {
        return $this->get('upload_dir');
    }

    function updateSettings($vars, &$errors) {

        if(!$vars || $errors)
            return false;

        switch(strtolower($vars['t'])) {
            case 'system':
                return $this->updateSystemSettings($vars, $errors);
                break;
            case 'tickets':
                return $this->updateTicketsSettings($vars, $errors);
                break;
            case 'emails':
                return $this->updateEmailsSettings($vars, $errors);
                break;
           case 'autoresp':
                return $this->updateAutoresponderSettings($vars, $errors);
                break;
            case 'alerts':
                return $this->updateAlertsSettings($vars, $errors);
                break;
            case 'kb':
                return $this->updateKBSettings($vars, $errors);
                break;
            default:
                $errors['err']='Unknown setting option. Get technical support.';
        }

        return false;
    }

    function updateSystemSettings($vars, &$errors) {

        $f=array();
        $f['helpdesk_url']=array('type'=>'string',   'required'=>1, 'error'=>'Helpdesk URl required');
        $f['helpdesk_title']=array('type'=>'string',   'required'=>1, 'error'=>'Helpdesk title required');
        $f['default_dept_id']=array('type'=>'int',   'required'=>1, 'error'=>'Default Dept. required');
        $f['default_template_id']=array('type'=>'int',   'required'=>1, 'error'=>'You must select template.');
        $f['staff_session_timeout']=array('type'=>'int',   'required'=>1, 'error'=>'Enter idle time in minutes');
        $f['client_session_timeout']=array('type'=>'int',   'required'=>1, 'error'=>'Enter idle time in minutes');
        //Date & Time Options
        $f['time_format']=array('type'=>'string',   'required'=>1, 'error'=>'Time format required');
        $f['date_format']=array('type'=>'string',   'required'=>1, 'error'=>'Date format required');
        $f['datetime_format']=array('type'=>'string',   'required'=>1, 'error'=>'Datetime format required');
        $f['daydatetime_format']=array('type'=>'string',   'required'=>1, 'error'=>'Day, Datetime format required');
        $f['default_timezone_id']=array('type'=>'int',   'required'=>1, 'error'=>'Default Timezone required');


        if(!Validator::process($f, $vars, $errors) || $errors)
            return false;

        return $this->updateAll(array(
            'isonline'=>$vars['isonline'],
            'helpdesk_title'=>$vars['helpdesk_title'],
            'helpdesk_url'=>$vars['helpdesk_url'],
            'default_dept_id'=>$vars['default_dept_id'],
            'default_template_id'=>$vars['default_template_id'],
            'max_page_size'=>$vars['max_page_size'],
            'log_level'=>$vars['log_level'],
            'log_graceperiod'=>$vars['log_graceperiod'],
            'passwd_reset_period'=>$vars['passwd_reset_period'],
            'staff_max_logins'=>$vars['staff_max_logins'],
            'staff_login_timeout'=>$vars['staff_login_timeout'],
            'staff_session_timeout'=>$vars['staff_session_timeout'],
            'staff_ip_binding'=>isset($vars['staff_ip_binding'])?1:0,
            'client_max_logins'=>$vars['client_max_logins'],
            'client_login_timeout'=>$vars['client_login_timeout'],
            'client_session_timeout'=>$vars['client_session_timeout'],
            'time_format'=>$vars['time_format'],
            'date_format'=>$vars['date_format'],
            'datetime_format'=>$vars['datetime_format'],
            'daydatetime_format'=>$vars['daydatetime_format'],
            'default_timezone_id'=>$vars['default_timezone_id'],
            'enable_daylight_saving'=>isset($vars['enable_daylight_saving'])?1:0,
        ));
    }

    function updateTicketsSettings($vars, &$errors) {


        $f=array();
        $f['default_sla_id']=array('type'=>'int',   'required'=>1, 'error'=>'Selection required');
        $f['default_priority_id']=array('type'=>'int',   'required'=>1, 'error'=>'Selection required');
        $f['max_open_tickets']=array('type'=>'int',   'required'=>1, 'error'=>'Enter valid numeric value');
        $f['autolock_minutes']=array('type'=>'int',   'required'=>1, 'error'=>'Enter lock time in minutes');


        if($vars['enable_captcha']) {
            if (!extension_loaded('gd'))
                $errors['enable_captcha']='The GD extension required';
            elseif(!function_exists('imagepng'))
                $errors['enable_captcha']='PNG support required for Image Captcha';
        }

        if($vars['allow_attachments']) {

            if(!ini_get('file_uploads'))
                $errors['err']='The \'file_uploads\' directive is disabled in php.ini';

            if(!is_numeric($vars['max_file_size']))
                $errors['max_file_size']='Maximum file size required';

            if(!$vars['allowed_filetypes'])
                $errors['allowed_filetypes']='Allowed file extentions required';

            if(!($maxfileuploads=ini_get('max_file_uploads')))
                $maxfileuploads=DEFAULT_MAX_FILE_UPLOADS;

            if(!$vars['max_user_file_uploads'] || $vars['max_user_file_uploads']>$maxfileuploads)
                $errors['max_user_file_uploads']='Invalid selection. Must be less than '.$maxfileuploads;

            if(!$vars['max_staff_file_uploads'] || $vars['max_staff_file_uploads']>$maxfileuploads)
                $errors['max_staff_file_uploads']='Invalid selection. Must be less than '.$maxfileuploads;
        }



        if(!Validator::process($f, $vars, $errors) || $errors)
            return false;

        return $this->updateAll(array(
            'random_ticket_ids'=>$vars['random_ticket_ids'],
            'default_priority_id'=>$vars['default_priority_id'],
            'default_sla_id'=>$vars['default_sla_id'],
            'max_open_tickets'=>$vars['max_open_tickets'],
            'autolock_minutes'=>$vars['autolock_minutes'],
            'allow_priority_change'=>isset($vars['allow_priority_change'])?1:0,
            'use_email_priority'=>isset($vars['use_email_priority'])?1:0,
            'enable_captcha'=>isset($vars['enable_captcha'])?1:0,
            'log_ticket_activity'=>isset($vars['log_ticket_activity'])?1:0,
            'auto_assign_reopened_tickets'=>isset($vars['auto_assign_reopened_tickets'])?1:0,
            'show_assigned_tickets'=>isset($vars['show_assigned_tickets'])?1:0,
            'show_answered_tickets'=>isset($vars['show_answered_tickets'])?1:0,
            'show_related_tickets'=>isset($vars['show_related_tickets'])?1:0,
            'show_notes_inline'=>isset($vars['show_notes_inline'])?1:0,
            'clickable_urls'=>isset($vars['clickable_urls'])?1:0,
            'hide_staff_name'=>isset($vars['hide_staff_name'])?1:0,
            'allow_attachments'=>isset($vars['allow_attachments'])?1:0,
            'allowed_filetypes'=>strtolower(preg_replace("/\n\r|\r\n|\n|\r/", '',trim($vars['allowed_filetypes']))),
            'max_file_size'=>$vars['max_file_size'],
            'max_user_file_uploads'=>$vars['max_user_file_uploads'],
            'max_staff_file_uploads'=>$vars['max_staff_file_uploads'],
            'email_attachments'=>isset($vars['email_attachments'])?1:0,
            'allow_email_attachments'=>isset($vars['allow_email_attachments'])?1:0,
            'allow_online_attachments'=>isset($vars['allow_online_attachments'])?1:0,
            'allow_online_attachments_onlogin'=>isset($vars['allow_online_attachments_onlogin'])?1:0,
        ));
    }


    function updateEmailsSettings($vars, &$errors) {

        $f=array();
        $f['default_email_id']=array('type'=>'int',   'required'=>1, 'error'=>'Default email required');
        $f['alert_email_id']=array('type'=>'int',   'required'=>1, 'error'=>'Selection required');
        $f['admin_email']=array('type'=>'email',   'required'=>1, 'error'=>'System admin email required');

        if($vars['strip_quoted_reply'] && !$vars['reply_separator'])
            $errors['reply_separator']='Reply separator required to strip quoted reply.';

        if($vars['admin_email'] && Email::getIdByEmail($vars['admin_email'])) //Make sure admin email is not also a system email.
            $errors['admin_email']='Email already setup as system email';

        if(!Validator::process($f,$vars,$errors) || $errors)
            return false;

        return $this->updateAll(array(
            'default_email_id'=>$vars['default_email_id'],
            'alert_email_id'=>$vars['alert_email_id'],
            'default_smtp_id'=>$vars['default_smtp_id'],
            'admin_email'=>$vars['admin_email'],
            'enable_auto_cron'=>isset($vars['enable_auto_cron'])?1:0,
            'enable_mail_polling'=>isset($vars['enable_mail_polling'])?1:0,
            'strip_quoted_reply'=>isset($vars['strip_quoted_reply'])?1:0,
            'reply_separator'=>$vars['reply_separator'],
         ));
    }

    function updateAutoresponderSettings($vars, &$errors) {

        if($errors) return false;

        return $this->updateAll(array(
            'ticket_autoresponder'=>$vars['ticket_autoresponder'],
            'message_autoresponder'=>$vars['message_autoresponder'],
            'ticket_notice_active'=>$vars['ticket_notice_active'],
            'overlimit_notice_active'=>$vars['overlimit_notice_active'],
        ));
    }


    function updateKBSettings($vars, &$errors) {

        if($errors) return false;

        return $this->updateAll(array(
            'enable_kb'=>isset($vars['enable_kb'])?1:0,
               'enable_premade'=>isset($vars['enable_premade'])?1:0,
        ));
    }


    function updateAlertsSettings($vars, &$errors) {


       if($vars['ticket_alert_active']
                && (!isset($vars['ticket_alert_admin'])
                    && !isset($vars['ticket_alert_dept_manager'])
                    && !isset($vars['ticket_alert_dept_members']))) {
            $errors['ticket_alert_active']='Select recipient(s)';
        }
        if($vars['message_alert_active']
                && (!isset($vars['message_alert_laststaff'])
                    && !isset($vars['message_alert_assigned'])
                    && !isset($vars['message_alert_dept_manager']))) {
            $errors['message_alert_active']='Select recipient(s)';
        }

        if($vars['note_alert_active']
                && (!isset($vars['note_alert_laststaff'])
                    && !isset($vars['note_alert_assigned'])
                    && !isset($vars['note_alert_dept_manager']))) {
            $errors['note_alert_active']='Select recipient(s)';
        }

        if($vars['transfer_alert_active']
                && (!isset($vars['transfer_alert_assigned'])
                    && !isset($vars['transfer_alert_dept_manager'])
                    && !isset($vars['transfer_alert_dept_members']))) {
            $errors['transfer_alert_active']='Select recipient(s)';
        }

        if($vars['overdue_alert_active']
                && (!isset($vars['overdue_alert_assigned'])
                    && !isset($vars['overdue_alert_dept_manager'])
                    && !isset($vars['overdue_alert_dept_members']))) {
            $errors['overdue_alert_active']='Select recipient(s)';
        }

        if($vars['assigned_alert_active']
                && (!isset($vars['assigned_alert_staff'])
                    && !isset($vars['assigned_alert_team_lead'])
                    && !isset($vars['assigned_alert_team_members']))) {
            $errors['assigned_alert_active']='Select recipient(s)';
        }

        if($errors) return false;

        return $this->updateAll(array(
            'ticket_alert_active'=>$vars['ticket_alert_active'],
            'ticket_alert_admin'=>isset($vars['ticket_alert_admin'])?1:0,
            'ticket_alert_dept_manager'=>isset($vars['ticket_alert_dept_manager'])?1:0,
            'ticket_alert_dept_members'=>isset($vars['ticket_alert_dept_members'])?1:0,
            'message_alert_active'=>$vars['message_alert_active'],
            'message_alert_laststaff'=>isset($vars['message_alert_laststaff'])?1:0,
            'message_alert_assigned'=>isset($vars['message_alert_assigned'])?1:0,
            'message_alert_dept_manager'=>isset($vars['message_alert_dept_manager'])?1:0,
            'note_alert_active'=>$vars['note_alert_active'],
            'note_alert_laststaff'=>isset($vars['note_alert_laststaff'])?1:0,
            'note_alert_assigned'=>isset($vars['note_alert_assigned'])?1:0,
            'note_alert_dept_manager'=>isset($vars['note_alert_dept_manager'])?1:0,
            'assigned_alert_active'=>$vars['assigned_alert_active'],
            'assigned_alert_staff'=>isset($vars['assigned_alert_staff'])?1:0,
            'assigned_alert_team_lead'=>isset($vars['assigned_alert_team_lead'])?1:0,
            'assigned_alert_team_members'=>isset($vars['assigned_alert_team_members'])?1:0,
            'transfer_alert_active'=>$vars['transfer_alert_active'],
            'transfer_alert_assigned'=>isset($vars['transfer_alert_assigned'])?1:0,
            'transfer_alert_dept_manager'=>isset($vars['transfer_alert_dept_manager'])?1:0,
            'transfer_alert_dept_members'=>isset($vars['transfer_alert_dept_members'])?1:0,
            'overdue_alert_active'=>$vars['overdue_alert_active'],
            'overdue_alert_assigned'=>isset($vars['overdue_alert_assigned'])?1:0,
            'overdue_alert_dept_manager'=>isset($vars['overdue_alert_dept_manager'])?1:0,
            'overdue_alert_dept_members'=>isset($vars['overdue_alert_dept_members'])?1:0,
            'send_sys_errors'=>isset($vars['send_sys_errors'])?1:0,
            'send_sql_errors'=>isset($vars['send_sql_errors'])?1:0,
            'send_login_errors'=>isset($vars['send_login_errors'])?1:0,
        ));
    }

    //Used to detect version prior to 1.7 (useful during upgrade)
    /* static */ function getDBVersion() {
        $sql='SELECT `ostversion` FROM '.TABLE_PREFIX.'config '
            .'WHERE id=1';
        return db_result(db_query($sql));
    }
}
?>
