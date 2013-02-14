<?php
if(!defined('SETUPINC')) die('Kwaheri!');
?>
    <div id="main">
            <h1 style="color:#FF7700;"><?= _('osTicket is already installed?')?></h1>
            <div id="intro">
             <p><?=_("Configuration file already changed - which could mean osTicket is already installed or the config file is currupted. If you are trying to upgrade osTicket, then go to")?> <a href="../scp/admin.php"><?=_("Admin Panel")?></a>.</p>

             <p><?= _('If you believe this is in error, please try replacing the config file with a unchanged template copy and try again or get technical help.')?></p>
             <p><?= _('Refer to the')?> <a target="_blank" href="http://osticket.com/wiki/Installation"><?= _('Installation Guide')?></a> <?= _('on the wiki for more information.')?></p>
            </div>
    </div>
    <div id="sidebar">
            <h3><?= _('Need Help?')?></h3>
            <p>
            <?= _('We provide <u>professional installation services</u> and commercial support with guaranteed response times, and access to the core development team.')?> <a target="_blank" href="http://osticket.com/support/professional_services.php"><?= _('Learn More!')?></a>
            </p>
    </div>
