<?php
if(!defined('OSTSCPINC') || !$thisstaff || !$thisstaff->isAdmin()) die(_('Access Denied'));
//Destroy the upgrader - we're done! 
$_SESSION['ost_upgrader']=null;
?> 
<div id="upgrader">
    <div id="main">
        <h1 style="color:green;"><?= _('Upgrade Completed!')?></h1>
        <div id="intro">
        <p><?= _('Congratulations osTicket upgrade has been completed successfully.')?></p>
        <p><?= _('Please refer to')?> <a href="http://osticket.com/wiki/Release_Notes" target="_blank"><?= _('Release Notes')?></a> <?= _('for more information about changes and/or new features.')?></p>
        </div>
        <p><?= _('Once again, thank you for choosing osTicket.')?></p>
        <p><?= _('Please feel free to')?> <a target="_blank" href="http://osticket.com/support/"><?= _('let us know')?></a> <?= _('of any other improvements and features you would like to see in osTicket, so that we may add them in the future as we continue to develop better and better versions of osTicket.')?></p>
        <p><?= _('We take user feedback seriously and we\'re dedicated to making changes based on your input.')?></p>
        <p><?= _('Good luck.')?><p>
        <p><?= _('osTicket Team.')?></p>
        <br>
        <p><b><?= _('PS')?></b>: <?= _('Don\'t just make customers happy, make happy customers!')?></p>
    </div>
    <div id="sidebar">
            <h3><?= _('What\'s Next?')?></h3>
            <p><b><?= _('Post-upgrade')?></b>: <?= _('You can now go to')?> <a href="scp/settings.php" target="_blank"><?= _('Admin Panel')?></a> <?= _('to enable the system and explore the new features. For complete and upto date release notes see')?> <a href="http://osticket.com/wiki/Release_Notes" target="_blank"><?= _('osTicket wiki')?></a></p>
            <p><b><?= _('Stay up to date')?></b>: <?= _('It\'s important to keep your osTicket installation up to date. Get announcements, security updates and alerts delivered directly to you!')?> 
            <a target="_blank" href="http://osticket.com/support/subscribe.php"><?= _('Get in the loop')?></a> <?= _('today and stay informed!')?></p>
            <p><b><?= _('Commercial support available')?></b>: <?= _('Get guidance and hands-on expertise to address unique challenges and make sure your osTicket runs smoothly, efficiently, and securely.')?> <a target="_blank" href="http://osticket.com/support/commercial_support.php.php"><?= _('Learn More!')?></a></p>
   </div>
   <div class="clear"></div>
</div>
