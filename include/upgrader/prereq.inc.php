<?php
if(!defined('OSTSCPINC') || !$thisstaff || !$thisstaff->isAdmin()) die('Access Denied');
?>
<h2><?php echo _('osTicket Upgrader');?></h2>
<div id="upgrader">
    
    <div id="main">
            <div id="intro">
             <p><?php echo _('Thank you for being a loyal osTicket user!');?></p>
             <p><?php echo _("The upgrade wizard will guide you every step of the way in the upgrade process. While we try to ensure that the upgrade process is straightforward and painless, we can't guarantee it will be the case for every user.");?></p>
            </div>
            <h2><?php echo _('Getting ready!');?></h2>
            <p><?php echo _("Before we begin, we'll check your server configuration to make sure you meet the minimum requirements to run the latest version of osTicket.");?></p>
            <h3><?php echo _('Prerequisites');?>: <font color="red"><?php echo $errors['prereq']; ?></font></h3>
            <?php echo _('These items are necessary in order to run the latest version of osTicket.');?>
            <ul class="progress">
                <li class="<?php echo $upgrader->check_php()?'yes':'no'; ?>">
                <?php echo sprintf(_('%s or greater'), 'PHP v4.3');?> - (<small><b><?php echo PHP_VERSION; ?></b></small>)</li>
                <li class="<?php echo $upgrader->check_mysql()?'yes':'no'; ?>">
                <?php echo sprintf(_('%s or greater'), 'MySQL v4.4');?> - (<small><b><?php echo extension_loaded('mysql')?_('module loaded'):_('missing!'); ?></b></small>)</li>
            </ul>
            <h3><?php echo _('Higly Recommended');?>:</h3>
            <?php echo _('We hightly recommend that you follow the steps below.');?>
            <ul>
                <li><?php echo _("Backup the current database, if you haven't done so already.");?></li>
                <li><?php echo _('Be patient the upgrade process will take a couple of seconds.');?></li>
            </ul>
            <div id="bar">
                <form method="post" action="upgrade.php" id="prereq">
                    <?php csrf_token(); ?>
                    <input type="hidden" name="s" value="prereq">
                    <input class="btn"  type="submit" name="submit" value="<?php echo _('Start Upgrade Now');?> &raquo;">
                </form>
            </div>
    </div>
    <div id="sidebar">
            <h3><?php echo _('Upgrade Tips');?></h3>
            <p>1. <?php echo _('Remember to backup your osTicket database');?></p>
            <p>2. <?php echo sprintf(_('Refer to %1$s Upgrade Guide %2$s for the latest tips'), '<a href="http://osticket.com/wiki/Upgrade_and_Migration" target="_blank">', '</a>');?></p>
            <p>3. <?php echo _('If you experience any problems, you can always restore your files/database backup.');?></p>
            <p>4. <?php echo sprintf(_('We can help, feel free to %1$s contact us %2$s for professional help.'), '<a href="http://osticket.com/support/" target="_blank">', '</a>');?></p>

    </div>
    <div class="clear"></div>
</div>
    
<div id="overlay"></div>
<div id="loading">
    <h4><?php echo _('Doing stuff!');?></h4>
    <?php echo _('Please wait... while we upgrade your osTicket installation!');?>
    <div id="msg"></div>
</div>
