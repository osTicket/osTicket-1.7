
DROP TABLE IF EXISTS `%TABLE_PREFIX%api_key`;
CREATE TABLE `%TABLE_PREFIX%api_key` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `isactive` tinyint(1) NOT NULL default '1',
  `ipaddr` varchar(64) NOT NULL,
  `apikey` varchar(255) NOT NULL,
  `can_create_tickets` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT  '1',
  `can_exec_cron` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT  '1',
  `notes` text,
  `updated` datetime NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `ipaddr` (`ipaddr`),
  UNIQUE KEY `apikey` (`apikey`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `%TABLE_PREFIX%faq`;
CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%faq` (
  `faq_id` int(10) unsigned NOT NULL auto_increment,
  `category_id` int(10) unsigned NOT NULL default '0',
  `ispublished` tinyint(1) unsigned NOT NULL default '0',
  `question` varchar(255) NOT NULL,
  `answer` text NOT NULL,
  `keywords` tinytext,
  `notes` text,
  `created` date NOT NULL,
  `updated` date NOT NULL,
  PRIMARY KEY  (`faq_id`),
  UNIQUE KEY `question` (`question`),
  KEY `category_id` (`category_id`),
  KEY `ispublished` (`ispublished`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `%TABLE_PREFIX%faq_attachment`;
CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%faq_attachment` (
  `faq_id` int(10) unsigned NOT NULL,
  `file_id` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`faq_id`,`file_id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `%TABLE_PREFIX%faq_category`;
CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%faq_category` (
  `category_id` int(10) unsigned NOT NULL auto_increment,
  `ispublic` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0',
  `name` varchar(125) default NULL,
  `description` TEXT NOT NULL,
  `notes` tinytext NOT NULL,
  `created` date NOT NULL,
  `updated` date NOT NULL,
  PRIMARY KEY  (`category_id`),
  KEY (`ispublic`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `%TABLE_PREFIX%faq_topic`;
CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%faq_topic` (
  `faq_id` int(10) unsigned NOT NULL,
  `topic_id` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`faq_id`,`topic_id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `%TABLE_PREFIX%sla`;
CREATE TABLE `%TABLE_PREFIX%sla` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `isactive` tinyint(1) unsigned NOT NULL default '1',
  `enable_priority_escalation` tinyint(1) unsigned NOT NULL default '1',
  `disable_overdue_alerts` tinyint(1) unsigned NOT NULL default '0',
  `grace_period` int(10) unsigned NOT NULL default '0',
  `name` varchar(64) NOT NULL default '',
  `notes` text,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `%TABLE_PREFIX%config`;
CREATE TABLE `%TABLE_PREFIX%config` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `namespace` varchar(64) NOT NULL,
  `key` varchar(64) NOT NULL,
  `value` text NOT NULL,
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  UNIQUE KEY (`namespace`, `key`)
) DEFAULT CHARSET=utf8;

INSERT INTO `%TABLE_PREFIX%config` (`namespace`, `key`, `value`) VALUES
  ('core', 'isonline', '0'),
  ('core', 'staff_ip_binding', '1'),
  ('core', 'staff_max_logins', '4'),
  ('core', 'staff_login_timeout', '2'),
  ('core', 'staff_session_timeout', '30'),
  ('core', 'passwd_reset_period', '0'),
  ('core', 'client_max_logins', '4'),
  ('core', 'client_login_timeout', '2'),
  ('core', 'client_session_timeout', '30'),
  ('core', 'max_page_size', '25'),
  ('core', 'max_open_tickets', '0'),
  ('core', 'max_file_size', '1048576'),
  ('core', 'max_user_file_uploads', ''),
  ('core', 'max_staff_file_uploads', ''),
  ('core', 'autolock_minutes', '3'),
  ('core', 'overdue_grace_period', '0'),
  ('core', 'alert_email_id', '0'),
  ('core', 'default_email_id', '0'),
  ('core', 'default_dept_id', '0'),
  ('core', 'default_sla_id', '0'),
  ('core', 'default_priority_id', '2'),
  ('core', 'default_template_id', '1'),
  ('core', 'default_smtp_id', '0'),
  ('core', 'allow_email_spoofing', '0'),
  ('core', 'clickable_urls', '1'),
  ('core', 'allow_priority_change', '0'),
  ('core', 'use_email_priority', '0'),
  ('core', 'enable_kb', '0'),
  ('core', 'enable_premade', '1'),
  ('core', 'enable_captcha', '0'),
  ('core', 'enable_auto_cron', '0'),
  ('core', 'enable_mail_polling', '0'),
  ('core', 'send_sys_errors', '1'),
  ('core', 'send_sql_errors', '1'),
  ('core', 'send_mailparse_errors', '1'),
  ('core', 'send_login_errors', '1'),
  ('core', 'save_email_headers', '1'),
  ('core', 'strip_quoted_reply', '1'),
  ('core', 'log_ticket_activity', '1'),
  ('core', 'ticket_autoresponder', '0'),
  ('core', 'message_autoresponder', '0'),
  ('core', 'ticket_notice_active', '0'),
  ('core', 'ticket_alert_active', '0'),
  ('core', 'ticket_alert_admin', '1'),
  ('core', 'ticket_alert_dept_manager', '1'),
  ('core', 'ticket_alert_dept_members', '0'),
  ('core', 'message_alert_active', '0'),
  ('core', 'message_alert_laststaff', '1'),
  ('core', 'message_alert_assigned', '1'),
  ('core', 'message_alert_dept_manager', '0'),
  ('core', 'note_alert_active', '0'),
  ('core', 'note_alert_laststaff', '1'),
  ('core', 'note_alert_assigned', '1'),
  ('core', 'note_alert_dept_manager', '0'),
  ('core', 'transfer_alert_active', '0'),
  ('core', 'transfer_alert_assigned', '0'),
  ('core', 'transfer_alert_dept_manager', '1'),
  ('core', 'transfer_alert_dept_members', '0'),
  ('core', 'overdue_alert_active', '0'),
  ('core', 'overdue_alert_assigned', '1'),
  ('core', 'overdue_alert_dept_manager', '1'),
  ('core', 'overdue_alert_dept_members', '0'),
  ('core', 'assigned_alert_active', '1'),
  ('core', 'assigned_alert_staff', '1'),
  ('core', 'assigned_alert_team_lead', '0'),
  ('core', 'assigned_alert_team_members', '0'),
  ('core', 'auto_assign_reopened_tickets', '1'),
  ('core', 'show_related_tickets', '1'),
  ('core', 'show_assigned_tickets', '1'),
  ('core', 'show_answered_tickets', '0'),
  ('core', 'show_notes_inline', '1'),
  ('core', 'hide_staff_name', '0'),
  ('core', 'overlimit_notice_active', '0'),
  ('core', 'email_attachments', '1'),
  ('core', 'allow_attachments', '0'),
  ('core', 'allow_email_attachments', '0'),
  ('core', 'allow_online_attachments', '0'),
  ('core', 'allow_online_attachments_onlogin', '0'),
  ('core', 'random_ticket_ids', '1'),
  ('core', 'log_level', '2'),
  ('core', 'log_graceperiod', '12'),
  ('core', 'admin_email', ''),
  ('core', 'helpdesk_title', 'osTicket Support Ticket System'),
  ('core', 'helpdesk_url', ''),
  ('core', 'schema_signature', '');

DROP TABLE IF EXISTS `%TABLE_PREFIX%department`;
CREATE TABLE `%TABLE_PREFIX%department` (
  `dept_id` int(11) unsigned NOT NULL auto_increment,
  `tpl_id` int(10) unsigned NOT NULL default '0',
  `sla_id` int(10) unsigned NOT NULL default '0',
  `email_id` int(10) unsigned NOT NULL default '0',
  `autoresp_email_id` int(10) unsigned NOT NULL default '0',
  `manager_id` int(10) unsigned NOT NULL default '0',
  `dept_name` varchar(128) NOT NULL default '',
  `dept_signature` tinytext NOT NULL,
  `ispublic` tinyint(1) unsigned NOT NULL default '1',
  `group_membership` tinyint(1) NOT NULL default '0',
  `ticket_auto_response` tinyint(1) NOT NULL default '1',
  `message_auto_response` tinyint(1) NOT NULL default '0',
  `updated` datetime NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY  (`dept_id`),
  UNIQUE KEY `dept_name` (`dept_name`),
  KEY `manager_id` (`manager_id`),
  KEY `autoresp_email_id` (`autoresp_email_id`),
  KEY `tpl_id` (`tpl_id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `%TABLE_PREFIX%email`;
CREATE TABLE `%TABLE_PREFIX%email` (
  `email_id` int(11) unsigned NOT NULL auto_increment,
  `noautoresp` tinyint(1) unsigned NOT NULL default '0',
  `priority_id` tinyint(3) unsigned NOT NULL default '2',
  `dept_id` tinyint(3) unsigned NOT NULL default '0',
  `email` varchar(255) NOT NULL default '',
  `name` varchar(255) NOT NULL default '',
  `userid` varchar(255) NOT NULL,
  `userpass` varchar(125) NOT NULL,
  `mail_active` tinyint(1) NOT NULL default '0',
  `mail_host` varchar(255) NOT NULL,
  `mail_protocol` enum('POP','IMAP') NOT NULL default 'POP',
  `mail_encryption` enum('NONE','SSL') NOT NULL,
  `mail_port` int(6) default NULL,
  `mail_fetchfreq` tinyint(3) NOT NULL default '5',
  `mail_fetchmax` tinyint(4) NOT NULL default '30',
  `mail_archivefolder` varchar(255) default NULL,
  `mail_delete` tinyint(1) NOT NULL default '0',
  `mail_errors` tinyint(3) NOT NULL default '0',
  `mail_lasterror` datetime default NULL,
  `mail_lastfetch` datetime default NULL,
  `smtp_active` tinyint(1) default '0',
  `smtp_host` varchar(255) NOT NULL,
  `smtp_port` int(6) default NULL,
  `smtp_secure` tinyint(1) NOT NULL default '1',
  `smtp_auth` tinyint(1) NOT NULL default '1',
  `smtp_spoofing` tinyint(1) unsigned NOT NULL default '0',
  `notes` text,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  PRIMARY KEY  (`email_id`),
  UNIQUE KEY `email` (`email`),
  KEY `priority_id` (`priority_id`),
  KEY `dept_id` (`dept_id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `%TABLE_PREFIX%filter`;
CREATE TABLE `%TABLE_PREFIX%filter` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `execorder` int(10) unsigned NOT NULL default '99',
  `isactive` tinyint(1) unsigned NOT NULL default '1',
  `match_all_rules` tinyint(1) unsigned NOT NULL default '0',
  `stop_onmatch` tinyint(1) unsigned NOT NULL default '0',
  `reject_ticket` tinyint(1) unsigned NOT NULL default '0',
  `use_replyto_email` tinyint(1) unsigned NOT NULL default '0',
  `disable_autoresponder` tinyint(1) unsigned NOT NULL default '0',
  `canned_response_id` int(11) unsigned NOT NULL default '0',
  `email_id` int(10) unsigned NOT NULL default '0',
  `priority_id` int(10) unsigned NOT NULL default '0',
  `dept_id` int(10) unsigned NOT NULL default '0',
  `staff_id` int(10) unsigned NOT NULL default '0',
  `team_id` int(10) unsigned NOT NULL default '0',
  `sla_id` int(10) unsigned NOT NULL default '0',
  `target` ENUM(  'Any',  'Web',  'Email',  'API' ) NOT NULL DEFAULT  'Any',
  `name` varchar(32) NOT NULL default '',
  `notes` text,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `target` (`target`),
  KEY `email_id` (`email_id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `%TABLE_PREFIX%filter_rule`;
CREATE TABLE `%TABLE_PREFIX%filter_rule` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `filter_id` int(10) unsigned NOT NULL default '0',
  `what` enum('name','email','subject','body','header') NOT NULL,
  `how` enum('equal','not_equal','contains','dn_contain','starts','ends') NOT NULL,
  `val` varchar(255) NOT NULL,
  `isactive` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `notes` tinytext NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `filter_id` (`filter_id`),
  UNIQUE `filter` (`filter_id`, `what`, `how`, `val`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `%TABLE_PREFIX%email_template_group`;
CREATE TABLE `%TABLE_PREFIX%email_template_group` (
  `tpl_id` int(11) NOT NULL auto_increment,
  `isactive` tinyint(1) unsigned NOT NULL default '0',
  `name` varchar(32) NOT NULL default '',
  `lang` varchar(16) NOT NULL default 'en_US',
  `notes` text,
  `created` datetime NOT NULL,
  `updated` timestamp NOT NULL,
  PRIMARY KEY  (`tpl_id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `%TABLE_PREFIX%email_template`;
CREATE TABLE `%TABLE_PREFIX%email_template` (
  `id` int(11) UNSIGNED NOT NULL auto_increment,
  `tpl_id` int(11) UNSIGNED NOT NULL,
  `code_name` varchar(32) NOT NULL,
  `subject` varchar(255) NOT NULL default '',
  `body` text NOT NULL,
  `notes` text,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `template_lookup` (`tpl_id`, `code_name`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `%TABLE_PREFIX%file`;
CREATE TABLE `%TABLE_PREFIX%file` (
  `id` int(11) NOT NULL auto_increment,
  `ft` CHAR( 1 ) NOT NULL DEFAULT  'T',
  `type` varchar(255) NOT NULL default '',
  `size` varchar(25) NOT NULL default '',
  `hash` varchar(125) NOT NULL,
  `name` varchar(255) NOT NULL default '',
  `created` datetime NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `ft` (`ft`),
  KEY `hash` (`hash`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `%TABLE_PREFIX%file_chunk`;
CREATE TABLE `%TABLE_PREFIX%file_chunk` (
  `file_id` int(11) NOT NULL,
  `chunk_id` int(11) NOT NULL,
  `filedata` longblob NOT NULL,
  PRIMARY KEY (`file_id`, `chunk_id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `%TABLE_PREFIX%groups`;
CREATE TABLE `%TABLE_PREFIX%groups` (
  `group_id` int(10) unsigned NOT NULL auto_increment,
  `group_enabled` tinyint(1) unsigned NOT NULL default '1',
  `group_name` varchar(50) NOT NULL default '',
  `can_create_tickets` tinyint(1) unsigned NOT NULL default '1',
  `can_edit_tickets` tinyint(1) unsigned NOT NULL default '1',
  `can_post_ticket_reply` tinyint( 1 ) unsigned NOT NULL DEFAULT  '1',
  `can_delete_tickets` tinyint(1) unsigned NOT NULL default '0',
  `can_close_tickets` tinyint(1) unsigned NOT NULL default '1',
  `can_assign_tickets` tinyint(1) unsigned NOT NULL default '1',
  `can_transfer_tickets` tinyint(1) unsigned NOT NULL default '1',
  `can_ban_emails` tinyint(1) unsigned NOT NULL default '0',
  `can_manage_premade` tinyint(1) unsigned NOT NULL default '0',
  `can_manage_faq` tinyint(1) unsigned NOT NULL default '0',
  `can_view_staff_stats` tinyint( 1 ) unsigned NOT NULL DEFAULT  '0',
  `notes` text,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  PRIMARY KEY  (`group_id`),
  KEY `group_active` (`group_enabled`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `%TABLE_PREFIX%group_dept_access`;
CREATE TABLE `%TABLE_PREFIX%group_dept_access` (
  `group_id` int(10) unsigned NOT NULL default '0',
  `dept_id` int(10) unsigned NOT NULL default '0',
  UNIQUE KEY `group_dept` (`group_id`,`dept_id`),
  KEY `dept_id`  (`dept_id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `%TABLE_PREFIX%help_topic`;
CREATE TABLE `%TABLE_PREFIX%help_topic` (
  `topic_id` int(11) unsigned NOT NULL auto_increment,
  `topic_pid` int(10) unsigned NOT NULL default '0',
  `isactive` tinyint(1) unsigned NOT NULL default '1',
  `ispublic` tinyint(1) unsigned NOT NULL default '1',
  `noautoresp` tinyint(3) unsigned NOT NULL default '0',
  `priority_id` tinyint(3) unsigned NOT NULL default '0',
  `dept_id` tinyint(3) unsigned NOT NULL default '0',
  `staff_id` int(10) unsigned NOT NULL default '0',
  `team_id` int(10) unsigned NOT NULL default '0',
  `sla_id` int(10) unsigned NOT NULL default '0',
  `page_id` int(10) unsigned NOT NULL default '0',
  `topic` varchar(32) NOT NULL default '',
  `notes` text,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  PRIMARY KEY  (`topic_id`),
  UNIQUE KEY `topic` ( `topic` ,  `topic_pid` ),
  KEY `topic_pid` (`topic_pid`),
  KEY `priority_id` (`priority_id`),
  KEY `dept_id` (`dept_id`),
  KEY `staff_id` (`staff_id`,`team_id`),
  KEY `sla_id` (`sla_id`),
  KEY `page_id` (`page_id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `%TABLE_PREFIX%canned_response`;
CREATE TABLE `%TABLE_PREFIX%canned_response` (
  `canned_id` int(10) unsigned NOT NULL auto_increment,
  `dept_id` int(10) unsigned NOT NULL default '0',
  `isenabled` tinyint(1) unsigned NOT NULL default '1',
  `title` varchar(255) NOT NULL default '',
  `response` text NOT NULL,
  `lang` varchar(16) NOT NULL default 'en_US',
  `notes` text,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  PRIMARY KEY  (`canned_id`),
  UNIQUE KEY `title` (`title`),
  KEY `dept_id` (`dept_id`),
  KEY `active` (`isenabled`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `%TABLE_PREFIX%canned_attachment`;
CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%canned_attachment` (
  `canned_id` int(10) unsigned NOT NULL,
  `file_id` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`canned_id`,`file_id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `%TABLE_PREFIX%session`;
CREATE TABLE `%TABLE_PREFIX%session` (
  `session_id` varchar(255) collate ascii_general_ci NOT NULL default '',
  `session_data` blob,
  `session_expire` datetime default NULL,
  `session_updated` datetime default NULL,
  `user_id` int(10) unsigned NOT NULL default '0' COMMENT 'osTicket staff ID',
  `user_ip` varchar(64) NOT NULL,
  `user_agent` varchar(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`session_id`),
  KEY `updated` (`session_updated`),
  KEY `user_id` (`user_id`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `%TABLE_PREFIX%staff`;
CREATE TABLE `%TABLE_PREFIX%staff` (
  `staff_id` int(11) unsigned NOT NULL auto_increment,
  `group_id` int(10) unsigned NOT NULL default '0',
  `dept_id` int(10) unsigned NOT NULL default '0',
  `timezone_id` int(10) unsigned NOT NULL default '0',
  `username` varchar(32) NOT NULL default '',
  `firstname` varchar(32) default NULL,
  `lastname` varchar(32) default NULL,
  `passwd` varchar(128) default NULL,
  `email` varchar(128) default NULL,
  `phone` varchar(24) NOT NULL default '',
  `phone_ext` varchar(6) default NULL,
  `mobile` varchar(24) NOT NULL default '',
  `signature` tinytext NOT NULL,
  `notes` text,
  `isactive` tinyint(1) NOT NULL default '1',
  `isadmin` tinyint(1) NOT NULL default '0',
  `isvisible` tinyint(1) unsigned NOT NULL default '1',
  `onvacation` tinyint(1) unsigned NOT NULL default '0',
  `assigned_only` tinyint(1) unsigned NOT NULL default '0',
  `show_assigned_tickets` tinyint(1) unsigned NOT NULL default '0',
  `daylight_saving` tinyint(1) unsigned NOT NULL default '0',
  `change_passwd` tinyint(1) unsigned NOT NULL default '0',
  `max_page_size` int(11) unsigned NOT NULL default '0',
  `auto_refresh_rate` int(10) unsigned NOT NULL default '0',
  `default_signature_type` ENUM( 'none', 'mine', 'dept' ) NOT NULL DEFAULT 'none',
  `default_paper_size` ENUM( 'Letter', 'Legal', 'Ledger', 'A4', 'A3' ) NOT NULL DEFAULT 'Letter',
  `created` datetime NOT NULL,
  `lastlogin` datetime default NULL,
  `passwdreset` datetime default NULL,
  `updated` datetime NOT NULL,
  PRIMARY KEY  (`staff_id`),
  UNIQUE KEY `username` (`username`),
  KEY `dept_id` (`dept_id`),
  KEY `issuperuser` (`isadmin`),
  KEY `group_id` (`group_id`,`staff_id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `%TABLE_PREFIX%syslog`;
CREATE TABLE `%TABLE_PREFIX%syslog` (
  `log_id` int(11) unsigned NOT NULL auto_increment,
  `log_type` enum('Debug','Warning','Error') NOT NULL,
  `title` varchar(255) NOT NULL,
  `log` text NOT NULL,
  `logger` varchar(64) NOT NULL,
  `ip_address` varchar(64) NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  PRIMARY KEY  (`log_id`),
  KEY `log_type` (`log_type`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `%TABLE_PREFIX%team`;
CREATE TABLE `%TABLE_PREFIX%team` (
  `team_id` int(10) unsigned NOT NULL auto_increment,
  `lead_id` int(10) unsigned NOT NULL default '0',
  `isenabled` tinyint(1) unsigned NOT NULL default '1',
  `noalerts` tinyint(1) unsigned NOT NULL default '0',
  `name` varchar(125) NOT NULL default '',
  `notes` text,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  PRIMARY KEY  (`team_id`),
  UNIQUE KEY `name` (`name`),
  KEY `isnabled` (`isenabled`),
  KEY `lead_id` (`lead_id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `%TABLE_PREFIX%team_member`;
CREATE TABLE `%TABLE_PREFIX%team_member` (
  `team_id` int(10) unsigned NOT NULL default '0',
  `staff_id` int(10) unsigned NOT NULL,
  `updated` datetime NOT NULL,
  PRIMARY KEY  (`team_id`,`staff_id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `%TABLE_PREFIX%ticket`;
CREATE TABLE `%TABLE_PREFIX%ticket` (
  `ticket_id` int(11) unsigned NOT NULL auto_increment,
  `ticketID` int(11) unsigned NOT NULL default '0',
  `dept_id` int(10) unsigned NOT NULL default '0',
  `sla_id` int(10) unsigned NOT NULL default '0',
  `priority_id` int(10) unsigned NOT NULL default '0',
  `topic_id` int(10) unsigned NOT NULL default '0',
  `staff_id` int(10) unsigned NOT NULL default '0',
  `team_id` int(10) unsigned NOT NULL default '0',
  `email` varchar(255) NOT NULL default '',
  `name` varchar(255) NOT NULL default '',
  `subject` varchar(255) NOT NULL default '[no subject]',
  `phone` varchar(16) default NULL,
  `phone_ext` varchar(8) default NULL,
  `ip_address` varchar(64) NOT NULL default '',
  `status` enum('open','closed') NOT NULL default 'open',
  `source` enum('Web','Email','Phone','API','Other') NOT NULL default 'Other',
  `isoverdue` tinyint(1) unsigned NOT NULL default '0',
  `isanswered` tinyint(1) unsigned NOT NULL default '0',
  `duedate` datetime default NULL,
  `reopened` datetime default NULL,
  `closed` datetime default NULL,
  `lastmessage` datetime default NULL,
  `lastresponse` datetime default NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  PRIMARY KEY  (`ticket_id`),
  UNIQUE KEY `email_extid` (`ticketID`,`email`),
  KEY `dept_id` (`dept_id`),
  KEY `staff_id` (`staff_id`),
  KEY `team_id` (`staff_id`),
  KEY `status` (`status`),
  KEY `priority_id` (`priority_id`),
  KEY `created` (`created`),
  KEY `closed` (`closed`),
  KEY `duedate` (`duedate`),
  KEY `topic_id` (`topic_id`),
  KEY `sla_id` (`sla_id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `%TABLE_PREFIX%ticket_attachment`;
CREATE TABLE `%TABLE_PREFIX%ticket_attachment` (
  `attach_id` int(11) unsigned NOT NULL auto_increment,
  `ticket_id` int(11) unsigned NOT NULL default '0',
  `file_id` int(10) unsigned NOT NULL default '0',
  `ref_id` int(11) unsigned NOT NULL default '0',
  `ref_type` enum('M','R','N') NOT NULL default 'M',
  `created` datetime NOT NULL,
  PRIMARY KEY  (`attach_id`),
  KEY `ticket_id` (`ticket_id`),
  KEY `ref_type` (`ref_type`),
  KEY `ref_id` (`ref_id`),
  KEY `file_id` (`file_id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `%TABLE_PREFIX%ticket_lock`;
CREATE TABLE `%TABLE_PREFIX%ticket_lock` (
  `lock_id` int(11) unsigned NOT NULL auto_increment,
  `ticket_id` int(11) unsigned NOT NULL default '0',
  `staff_id` int(10) unsigned NOT NULL default '0',
  `expire` datetime default NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY  (`lock_id`),
  UNIQUE KEY `ticket_id` (`ticket_id`),
  KEY `staff_id` (`staff_id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `%TABLE_PREFIX%ticket_email_info`;
CREATE TABLE `%TABLE_PREFIX%ticket_email_info` (
  `message_id` int(11) unsigned NOT NULL,
  `email_mid` varchar(255) NOT NULL,
  `headers` text,
  KEY `message_id` (`email_mid`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `%TABLE_PREFIX%ticket_event`;
CREATE TABLE `%TABLE_PREFIX%ticket_event` (
  `ticket_id` int(11) unsigned NOT NULL default '0',
  `staff_id` int(11) unsigned NOT NULL,
  `team_id` int(11) unsigned NOT NULL,
  `dept_id` int(11) unsigned NOT NULL,
  `topic_id` int(11) unsigned NOT NULL,
  `state` enum('created','closed','reopened','assigned','transferred','overdue') NOT NULL,
  `staff` varchar(255) NOT NULL default 'SYSTEM',
  `annulled` tinyint(1) unsigned NOT NULL default '0',
  `timestamp` datetime NOT NULL,
  KEY `ticket_state` (`ticket_id`, `state`, `timestamp`),
  KEY `ticket_stats` (`timestamp`, `state`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `%TABLE_PREFIX%ticket_priority`;
CREATE TABLE `%TABLE_PREFIX%ticket_priority` (
  `priority_id` tinyint(4) NOT NULL auto_increment,
  `priority` varchar(60) NOT NULL default '',
  `priority_desc` varchar(30) NOT NULL default '',
  `priority_color` varchar(7) NOT NULL default '',
  `priority_urgency` tinyint(1) unsigned NOT NULL default '0',
  `ispublic` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`priority_id`),
  UNIQUE KEY `priority` (`priority`),
  KEY `priority_urgency` (`priority_urgency`),
  KEY `ispublic` (`ispublic`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `%TABLE_PREFIX%ticket_thread`;
CREATE TABLE `%TABLE_PREFIX%ticket_thread` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `pid` int(11) unsigned NOT NULL default '0',
  `ticket_id` int(11) unsigned NOT NULL default '0',
  `staff_id` int(11) unsigned NOT NULL default '0',
  `thread_type` enum('M','R','N') NOT NULL,
  `poster` varchar(128) NOT NULL default '',
  `source` varchar(32) NOT NULL default '',
  `title` varchar(255),
  `body` text NOT NULL,
  `ip_address` varchar(64) NOT NULL default '',
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `ticket_id` (`ticket_id`),
  KEY `staff_id` (`staff_id`),
  KEY `pid` (`pid`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `%TABLE_PREFIX%timezone`;
CREATE TABLE `%TABLE_PREFIX%timezone` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `offset` float(3,1) NOT NULL default '0.0',
  `timezone` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

INSERT INTO `%TABLE_PREFIX%timezone` (`offset`, `timezone`) VALUES
  (-12.0, 'Eniwetok, Kwajalein'),
  (-11.0, 'Midway Island, Samoa'),
  (-10.0, 'Hawaii'),
  (-9.0, 'Alaska'),
  (-8.0, 'Pacific Time (US & Canada)'),
  (-7.0, 'Mountain Time (US & Canada)'),
  (-6.0, 'Central Time (US & Canada), Mexico City'),
  (-5.0, 'Eastern Time (US & Canada), Bogota, Lima'),
  (-4.0, 'Atlantic Time (Canada), Caracas, La Paz'),
  (-3.5, 'Newfoundland'),
  (-3.0, 'Brazil, Buenos Aires, Georgetown'),
  (-2.0, 'Mid-Atlantic'),
  (-1.0, 'Azores, Cape Verde Islands'),
  (0.0, 'Western Europe Time, London, Lisbon, Casablanca'),
  (1.0, 'Brussels, Copenhagen, Madrid, Paris'),
  (2.0, 'Kaliningrad, South Africa'),
  (3.0, 'Baghdad, Riyadh, Moscow, St. Petersburg'),
  (3.5, 'Tehran'),
  (4.0, 'Abu Dhabi, Muscat, Baku, Tbilisi'),
  (4.5, 'Kabul'),
  (5.0, 'Ekaterinburg, Islamabad, Karachi, Tashkent'),
  (5.5, 'Bombay, Calcutta, Madras, New Delhi'),
  (6.0, 'Almaty, Dhaka, Colombo'),
  (7.0, 'Bangkok, Hanoi, Jakarta'),
  (8.0, 'Beijing, Perth, Singapore, Hong Kong'),
  (9.0, 'Tokyo, Seoul, Osaka, Sapporo, Yakutsk'),
  (9.5, 'Adelaide, Darwin'),
  (10.0, 'Eastern Australia, Guam, Vladivostok'),
  (11.0, 'Magadan, Solomon Islands, New Caledonia'),
  (12.0, 'Auckland, Wellington, Fiji, Kamchatka');

-- pages
CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%page` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `isactive` tinyint(1) unsigned NOT NULL default '0',
  `type` enum('landing','offline','thank-you','other') NOT NULL default 'other',
  `name` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `lang` varchar(16) NOT NULL default 'en_US',
  `notes` text,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) DEFAULT CHARSET=utf8;
