/**
 * Add a table for email filter settings which will make defining new
 * settings for the email filters not require database changes. This patch
 * will transfer settings for existing email filters to the new table for
 * filters that exist already.
 *
 * @version 1.7-dpr3 flexible-filter-settings
 */
DROP TABLE IF EXISTS `%TABLE_PREFIX%email_filter_setting`;
CREATE TABLE `%TABLE_PREFIX%email_filter_setting` (
  `filter_id` int(10) unsigned NOT NULL default '0',
  `setting` varchar(64) NOT NULL,
  `value` varchar(128) NOT NULL default '',
  PRIMARY KEY `filter_settings` (`filter_id`, `setting`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- use_replyto_email -> 'return-email':''
INSERT INTO `%TABLE_PREFIX%email_filter_setting`
    (`filter_id`, `section`, `value`)
  SELECT `filter_id`, 'return-email', 
      CASE WHEN `use_replyto_email` THEN 'reply-to' ELSE 'from' END
  FROM `%TICKET_PREFIX%email_filter`;

-- disable_autoresponder -> 'auto-response':''
INSERT INTO `%TABLE_PREFIX%email_filter_setting`
    (`filter_id`, `section`, `value`)
  SELECT `filter_id`, 'auto-response', 
      CASE WHEN `disable_autoresponder` THEN 'disable' ELSE 'enable' END
  FROM `%TICKET_PREFIX%email_filter`;

-- email_id -> 'system-email':''
INSERT INTO `%TABLE_PREFIX%email_filter_setting`
    (`filter_id`, `section`, `value`)
  SELECT `filter_id`, 'system-email', `email_id`
  FROM `%TICKET_PREFIX%email_filter`;

-- priority_id -> 'priority':''
INSERT INTO `%TABLE_PREFIX%email_filter_setting`
    (`filter_id`, `section`, `value`)
  SELECT `filter_id`, 'priority', `priority_id`
  FROM `%TICKET_PREFIX%email_filter`;

-- dept_id -> 'department':''
INSERT INTO `%TABLE_PREFIX%email_filter_setting`
    (`filter_id`, `section`, `value`)
  SELECT `filter_id`, 'department', `dept_id`
  FROM `%TICKET_PREFIX%email_filter`;

-- staff_id -> 'assignee':'staff'
INSERT INTO `%TABLE_PREFIX%email_filter_setting`
    (`filter_id`, `section`, `value`)
  SELECT `filter_id`, 'assignee:staff', `staff_id`
  FROM `%TICKET_PREFIX%email_filter`
  WHERE `staff_id`;
-- team_id -> 'assignee':'team'
INSERT INTO `%TABLE_PREFIX%email_filter_setting`
    (`filter_id`, `section`, `value`)
  SELECT `filter_id`, 'assignee:team', `team_id`
  FROM `%TICKET_PREFIX%email_filter`
  WHERE `team_id`;

-- sla_id -> 'sla':''
INSERT INTO `%TABLE_PREFIX%email_filter_setting`
    (`filter_id`, `section`, `value`)
  SELECT `filter_id`, 'sla', `sla_id`
  FROM `%TICKET_PREFIX%email_filter`;

-- Remove now-unused fields from email_filter
ALTER TABLE `%TABLE_PREFIX%email_filter`
  DROP `use_replyto_email`,
  DROP `disable_autoresponder`,
  DROP `email_id`,
  DROP `priority_id`,
  DROP `dept_id`,
  DROP `staff_id`,
  DROP `team_id`,
  DROP `sla_id`;

-- Finished with patch
UPDATE `%TABLE_PREFIX%config`
    SET `schema_signature`='93945f592aadd5225ae5c471492f8f0c';
