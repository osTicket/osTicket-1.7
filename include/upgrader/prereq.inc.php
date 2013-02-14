<?php
if(!defined('OSTSCPINC') || !$thisstaff || !$thisstaff->isAdmin()) die(_('Access Denied'));
?>
<h2><?= _('osTicket Upgrader')?></h2>
<div id="upgrader">
    
    <div id="main">
            <div id="intro">
             <p><?= _('Thank you for being a loyal osTicket user!')?></p>
             <p><?= _('The upgrade wizard will guide you every step of the way in the upgrade process. While we try to ensure that the upgrade process is straightforward and painless, we can\'t guarantee it will be the case for every user.')?></p>
            </div>
            <h2><?= _('Getting ready!')?></h2>
            <p><?= _('Before we begin, we\'ll check your server configuration to make sure you meet the minimum requirements to run the latest version of osTicket.')?></p>
            <h3><?= _('Prerequisites')?>: <font color="red"><?php echo $errors['prereq']; ?></font></h3>
            <?= _('These items are necessary in order to run the latest version of osTicket.')?>
            <ul class="progress">
                <li class="<?php echo $upgrader->check_php()?_('yes'):_('no'); ?>">
                <?= _('PHP v4.3 or greater')?> - (<small><b><?php echo PHP_VERSION; ?></b></small>)</li>
                <li class="<?php echo $upgrader->check_mysql()?_('yes'):_('no'); ?>">
                <?= _('MySQL v4.4 or greater')?> - (<small><b><?php echo extension_loaded('mysql')?_('module loaded'):_('missing!'); ?></b></small>)</li>
            </ul>
            <h3><?= _('Higly Recommended')?>:</h3>
            <?= _('We hightly recommend that you follow the steps below.')?>
            <ul>
                <li><?= _('Backup the current database, if you haven\'t done so already.')?></li>
                <li><?= _('Be patient the upgrade process will take a couple of seconds.')?></li>
            </ul>
            <div id="bar">
                <form method="post" action="upgrade.php" id="prereq">
                    <?php csrf_token(); ?>
                    <input type="hidden" name="s" value="prereq">
                    <input class="btn"  type="submit" name="submit" value="<?= _('Start Upgrade Now')?> &raquo;">
                </form>
            </div>
    </div>
    <div id="sidebar">
            <h3><?= _('Upgrade Tips')?></h3>
            <p><?= _('1. Remember to backup your osTicket database')?></p>
            <p><?= _('2. Refer to')?> <a href="http://osticket.com/wiki/Upgrade_and_Migration" target="_blank"><?= _('Upgrade Guide')?></a> <?= _('for the latest tips')?></a>
            <p><?= _('3. If you experience any problems, you can always restore your files/database backup.')?></p>
            <p><?= _('4. We can help, feel free to')?> <a href="http://osticket.com/support/" target="_blank"><?= _('contact us')?></a> <?= _('for professional help.')?></p>

    </div>
    <div class="clear"></div>
</div>
    
<div id="overlay"></div>
<div id="loading">
    <h4><?= _('Doing stuff!')?></h4>
    <?= _('Please wait... while we upgrade your osTicket installation!')?>
    <div id="msg"></div>
</div>
