<?php
if(!defined('OSTSCPINC') || !$thisstaff || !$thisstaff->isAdmin()) die(_('Access Denied'));
?>
<div id="upgrader">
    <br>
    <h2 style="color:#FF7700;"><?= _('Configuration file rename required!')?></h2>
    <div id="main">
            <div id="intro">
             <p><?= _('To avoid possible conflicts, please take a minute to rename configuration file as shown below.')?></p>
            </div>
            <h3><?= _('Solution')?>:</h3>
            <?= _('Rename file')?> <b>include/settings.php</b> <?= _('to')?> <b>include/ost-config.php</b> <?= _('and click continue below.')?>
            <ul>
                <li><b>CLI:</b><br><i>mv include/settings.php include/ost-config.php</i></li>
                <li><b>FTP:</b><br> </li>
                <li><b>Cpanel:</b><br> </li>
            </ul>
            <p><?= _('Please refer to the')?> <a target="_blank" href="http://osticket.com/wiki/Upgrade_and_Migration"><?= _('Upgrade Guide')?></a> <?= _('for more information.')?></p>
            <div id="bar">
                <form method="post" action="upgrade.php">
                    <?php csrf_token(); ?>
                    <input type="hidden" name="s" value="prereq">
                    <input class="btn" type="submit" name="submit" value="<?= _('Continue')?> &raquo;">
                </form>
            </div>
    </div>
    <div id="sidebar">
            <h3><?= _('Need Help?')?></h3>
            <p>
            <?= _('If you are looking for a greater level of support, we provide <u>professional upgrade</u> and commercial support with guaranteed response times, and access to the core development team. We can also help customize osTicket or even add new features to the system to meet your unique needs.')?> <a target="_blank" href="http://osticket.com/support/professional_services.php"><?= _('Learn More!')?></a>
            </p>
    </div>
    <div class="clear"></div>
</div>
