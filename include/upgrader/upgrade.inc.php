<?php
if(!defined('OSTSCPINC') || !$thisstaff || !$thisstaff->isAdmin()) die('Access Denied');

//See if we need to switch the mode of upgrade...e.g from ajax (default) to manual
if(($mode = $ost->get_var('m', $_GET)) &&  $mode!=$upgrader->getMode()) {
    //Set Persistent mode/
    $upgrader->setMode($mode);
    //Log warning about ajax calls - most likely culprit is AcceptPathInfo directive.
    if($mode=='manual')
        $ost->logWarning('Ajax calls are failing',
                'Make sure your server has AcceptPathInfo directive set to "ON" or get technical help');
}

$action=$upgrader->getNextAction();
?>
<h2><?php echo _('osTicket Upgrade');?></h2>
<div id="upgrader">
    <div id="main">
            <div id="intro">
             <p><?php echo _('Thank you for taking the time to upgrade your osTicket intallation!');?></p>
             <p><?php echo _("Please don't cancel or close the browser, any errors at this stage will be fatal.");?></p>
            </div>
            <h2 id="task"><?php echo $action ?></h2>
            <p><?php echo _('The upgrade wizard will now attempt to upgrade your database and core settings!');?></p>
            <ul>
                <li><?php echo _('Database enhancements');?></li>
                <li><?php echo _('New and updated features');?></li>
                <li><?php echo _('Enhance settings and security');?></li>
            </ul>
            <div id="bar">
                <form method="post" action="upgrade.php" id="upgrade">
                    <?php csrf_token(); ?>
                    <input type="hidden" name="s" value="upgrade">
                    <input type="hidden" id="mode" name="m" value="<?php echo $upgrader->getMode(); ?>">
                    <input type="hidden" name="sh" value="<?php echo $upgrader->getSchemaSignature(); ?>">
                    <input class="btn"  type="submit" name="submit" value="<?php echo _('Upgrade Now!');?>">
                </form>
            </div>
    </div>
    <div id="sidebar">
            <h3><?php echo _('Upgrade Tips');?></h3>
            <p>1. <?php echo _('Be patient the process will take a couple of minutes.');?></p>
            <p>2. <?php echo _('If you experience any problems, you can always restore your files/database backup.');?></p>
            <p>3. <?php echo sprintf(_('We can help, feel free to %1$s contact us %2$s for professional help.'), '<a href="http://osticket.com/support/" target="_blank">', '</a>');?></p>
    </div>
    <div class="clear"></div>
    <div id="upgrading">
        <h4 id="action"><?php echo $action; ?></h4>
        <?php echo _('Please wait... while we upgrade your osTicket installation!');?>
        <div id="msg" style="font-weight: bold;padding-top:10px;">
            <?php echo sprintf(_('%s - Relax!'), $thisstaff->getFirstName()); ?>
        </div>
    </div>
</div>
<div class="clear"></div>
