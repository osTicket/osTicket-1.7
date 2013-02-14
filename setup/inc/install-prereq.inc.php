<?php
if(!defined('SETUPINC')) die('Kwaheri!');

?>

    <div id="main">
            <h1><?= _('Thank You for Choosing osTicket!')?></h1>
            <div id="intro">
             <p><?= _('We are delighted you have chosen osTicket for your customer support ticketing system!')?></p>
            <p><?= _('The installer will guide you every step of the way in the installation process. You\'re minutes away from your awesome customer support system!')?></p>
            </div>
            <h2><?= _('Prerequisites.')?></h3>
            <p><?= _('Before we begin, we\'ll check your server configuration to make sure you meet the minimum requirements to install and run osTicket.')?></p>
            <h3><?= _('Required')?>: <font color="red"><?php echo $errors['prereq']; ?></font></h3>
            <?= _('These items are necessary in order to install and use osTicket.')?>
            <ul class="progress">
                <li class="<?php echo $installer->check_php()?'yes':'no'; ?>">
                <?= _('PHP v4.3 or greater')?> - (<small><b><?php echo PHP_VERSION; ?></b></small>)</li>
                <li class="<?php echo $installer->check_mysql()?'yes':'no'; ?>">
                <?= _('MySQL v4.4 or greater')?> - (<small><b><?php echo extension_loaded('mysql')?_('module loaded'):_('missing!'); ?></b></small>)</li>
            </ul>
            <h3><?= _('Recommended')?>:</h3>
            <?= _('You can use osTicket without these, but you may not be able to use all features.')?>
            <ul class="progress">
                <li class="<?php echo extension_loaded('mcrypt')?'yes':'no'; ?>"><?= _('Mcrypt extension')?></li>
                <li class="<?php echo extension_loaded('gd')?'yes':'no'; ?>"><?= _('Gdlib extension')?></li>
                <li class="<?php echo extension_loaded('imap')?'yes':'no'; ?>"><?= _('PHP IMAP extension')?></li>
            </ul>
            <div id="bar">
                <form method="post" action="install.php">
                    <input type="hidden" name="s" value="prereq">
                    <input class="btn"  type="submit" name="submit" value="<?= _('Continue')?> &raquo;">
                </form>
            </div>
    </div>
    <div id="sidebar">
            <h3><?= _('Need Help?')?></h3>
            <p>
            <?= _('If you are looking for a greater level of support, we provide <u>professional installation services</u> and commercial support with guaranteed response times, and access to the core development team. We can also help customize osTicket or even add new features to the system to meet your unique needs.')?> <a target="_blank" href="http://osticket.com/support/professional_services.php"><?= _('Learn More!')?></a>
            </p>
    </div>
