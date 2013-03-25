<?php
if(!defined('OSTSCPINC') || !$thisstaff || !$thisstaff->isAdmin()) die('Access Denied');
//Destroy the upgrader - we're done! 
$_SESSION['ost_upgrader']=null;
?> 
<div id="upgrader">
    <div id="main">
        <h1 style="color:green;"><?php echo __('Upgrade Completed!');?></h1>
        <div id="intro">
        <p><?php echo __('Congratulations osTicket upgrade has been completed successfully.');?></p>
        <p><?php echo sprintf(__('Please refer to %1$s Release Notes %2$s for more information about changes and/or new features.'), '<a href="http://osticket.com/wiki/Release_Notes" target="_blank">','</a>');?></p>
        </div>
        <p><?php echo __('Once again, thank you for choosing osTicket.');?></p>
        <p><?php echo sprintf(__('Please feel free to %1$s let us know %2$s of any other improvements and features you would like to see in osTicket, so that we may add them in the future as we continue to develop better and better versions of osTicket.'), '<a target="_blank" href="http://osticket.com/support/">', '</a>');?></p>
        <p><?php echo __("We take user feedback seriously and we're dedicated to making changes based on your input.");?></p>
        <p><?php echo __('Good luck.');?><p>
        <p><?php echo __('osTicket Team.');?></p>
        <br>
        <p><b><?php echo __('PS');?></b>: <?php echo __("Don't just make customers happy, make happy customers!");?></p>
    </div>
    <div id="sidebar">
            <h3><?php echo __("What's Next?");?></h3>
            <p><b><?php echo __('Post-upgrade');?></b>: <?php echo sprintf(__('You can now go to %1$s Admin Panel %2$s to enable the system and explore the new features. For complete and upto date release notes see %3$s osTicket wiki %4$s'),'<a href="scp/settings.php" target="_blank">','</a>','<a href="http://osticket.com/wiki/Release_Notes" target="_blank">','</a>');?></p>
            <p><b><?php echo __('Stay up to date');?></b>: <?php echo __("It's important to keep your osTicket installation up to date. Get announcements, security updates and alerts delivered directly to you!");?> 
            <?php echo sprintf(__('%1$s Get in the loop %2$s today and stay informed!'), '<a target="_blank" href="http://osticket.com/support/subscribe.php">', '</a>');?></p>
            <p><b><?php echo __('Commercial support available');?></b>: <?php echo sprintf(__('Get guidance and hands-on expertise to address unique challenges and make sure your osTicket runs smoothly, efficiently, and securely. %1$s Learn More! %2$s'), '<a target="_blank" href="http://osticket.com/support/commercial_support.php.php">','</a>');?></p>
   </div>
   <div class="clear"></div>
</div>
