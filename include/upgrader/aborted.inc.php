<?php
if(!defined('OSTSCPINC') || !$thisstaff || !$thisstaff->isAdmin()) die(_('Access Denied'));
?>    
<div id="upgrader">
   <div id="main">
    <h1 style="color:#FF7700;"><?= _('Upgrade Aborted!')?></h1>
    <div id="intro">
        <p><strong><?= _('Upgrade aborted due to errors. Any errors at this stage are fatal.')?></strong></p>
        <p><?= _('Please note the error(s), if any, when')?> <a target="_blank" href="http://osticket.com/support/"><?= _('seeking help')?></a>.<p>
        <?php
        if($upgrader && ($errors=$upgrader->getErrors())) {
            if($errors['err'])
                echo sprintf('<b><font color="red">%s</font></b>',$errors['err']);
            echo '<ul class="error">';
            unset($errors['err']);
            foreach($errors as $k => $error)
                echo sprintf('<li>%s</li>',$error);
            echo '</ul>';
        } else {
            echo '<b><font color="red">'._('Internal error occurred - get technical help.').'</font></b>';
        }
        ?>
        <p><b><?= _('For detailed - please view')?> <a href="logs.php"><?= _('system logs')?></a> <?= _('or check your email.')?></b></p>
        <br>
        <p><?= _('Please, refer to the')?> <a target="_blank" href="http://osticket.com/wiki/Upgrade_and_Migration"><?= _('Upgrade Guide')?></a> <?= _('on the wiki for more information.')?></p>
    </div>
    <p><strong><?= _('Need Help?')?></strong> <?= _('We provide')?> <a target="_blank" href="http://osticket.com/support/professional_services.php"><u><?= _('professional upgrade services')?></u></a> <?= _('and commercial support.')?> <a target="_blank" href="http://osticket.com/support/"><?= _('Contact us')?></a> <?= _('today for <u>expedited</u> help.')?></p>
  </div>    
  <div id="sidebar">
    <h3><?= _('What to do?')?></h3>
    <p><?= _('Restore your previous version from backup and try again or')?> <a target="_blank" href="http://osticket.com/support/"><?= _('seek help')?></a>.</p>
  </div>
  <div class="clear"></div>
</div>
