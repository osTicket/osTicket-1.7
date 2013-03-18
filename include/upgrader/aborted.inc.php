<?php
if(!defined('OSTSCPINC') || !$thisstaff || !$thisstaff->isAdmin()) die('Access Denied');
?>    
<div id="upgrader">
   <div id="main">
    <h1 style="color:#FF7700;"><?php echo _('Upgrade Aborted!');?></h1>
    <div id="intro">
        <p><strong><?php echo _('Upgrade aborted due to errors. Any errors at this stage are fatal.');?></strong></p>
        <p><?php echo sprintf(_('Please note the error(s), if any, when %1$s seeking help %2$s.'),'<a target="_blank" href="http://osticket.com/support/">','</a>');?><p>
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
        <p><b><?php echo sprintf(_('For details - please view %1$s system logs %2$s or check your email.'),'<a href="logs.php">','</a>');?></b></p>
        <br>
        <p><?php echo sprintf(_('Please refer to the %1$s Upgrade Guide %2$s for more information.'), '<a target="_blank" href="http://osticket.com/wiki/Upgrade_and_Migration">', '</a>');?></p>
    </div>
    <p><strong><?php echo _('Need Help?');?></strong> <?php echo sprintf(_('We provide %1$s professional upgrade services %2$s and commercial support.'), '<a target="_blank" href="http://osticket.com/support/professional_services.php"><u>','</u></a>'); echo sprintf(_('%1$s Contact us %2$s today for <u>expedited</u> help.'), '<a target="_blank" href="http://osticket.com/support/">','</a>');?></p>
  </div>    
  <div id="sidebar">
    <h3><?php echo _('What to do?');?></h3>
    <p><?php echo sprintf(_('Restore your previous version from backup and try again or %1$s seek help %2$s.'), '<a target="_blank" href="http://osticket.com/support/">','</a>');?></p>
  </div>
  <div class="clear"></div>
</div>
