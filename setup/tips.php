<html>
<?php
require_once('setup.inc.php');
?>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<body>
<div id="t1">
<b><?php echo _('Helpdesk Name');?></b>
<p><?php echo _('The name of your support system e.g [Company Name] Support');?></p>
</div>
<div id="t2">
<b><?php echo _('Default System Email');?></b>
<p><?php echo _('Default email address e.g support@yourcompany.com - you can add more later!');?></p>
</div>
<div id="t3">
<b><?php echo _('First Name');?></b>
<p><?php echo _("Admin's first name");?></p>
</div>
<div id="t4">
<b><?php echo _('Last Name');?></b>
<p><?php echo _("Admin's last name");?></p>
</div>
<div id="t5">
<b><?php echo _('Email Address');?></b>
<p><?php echo _("Admin's personal email address. Must be different from system's default email.");?></p>
</div>
<div id="t6">
<b><?php echo _('Username');?></b>
<p><?php echo _("Admin's login name. Must be at least three (3) characters.");?></p>
</div>
<div id="t7">
<b><?php echo _('Password');?></b>
<p><?php echo _("Admin's password.  Must be five (5) characters or more.");?></p>
</div>
<div id="t8">
<b><?php echo _('Confirm Password');?></b>
<p><?php echo _("Retype admin's password. Must match.");?></p>
</div>
<div id="t9">
<b><?php echo _('MySQL Table Prefix.');?></b>
<p><?php echo _('osTicket requires table prefix in order to avoid possible table conflicts in a shared database.');?></p>
</div>
<div id="t10">
<b><?php echo _('MySQL Hostname');?></b>
<p><?php echo _("Most hosts use 'localhost' for local database hostname. Check with your host if localhost fails. Default port set in php.ini is assumed.");?></p>
</div>
<div id="t11">
<b><?php echo _('MySQL Database');?></b>
<p><?php echo _('Name of the database osTicket will use.');?></p>
</div>
<div id="t12">
<b><?php echo _('MySQL Username');?></b> 
<p><?php echo _('The MySQL user must have full rights to the database.');?></p>
</div>
<div id="t13">
<b><?php echo _('MySQL Password');?></b>
<p><?php echo _('MySQL password associated with above user.');?></p>
</div>
</body>
</html>