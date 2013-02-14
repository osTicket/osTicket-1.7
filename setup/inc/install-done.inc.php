<?php if(!defined('SETUPINC')) die('Kwaheri!');
$url=URL;

?>    
    <div id="main">
        <h1 style="color:green;"><?= _('Congratulations!')?></h1>
        <div id="intro">
        <p><?= _('Your osTicket installation has been completed successfully. Your next step is to fully configure your new support ticket system for use, but before you get to it please take a minute to cleanup.')?></p>
        
        <h2><?= _('Config file permission')?>:</h2>
        <?= _('Change permission of ost-config.php to remove write access as shown below.')?>
        <ul>
            <li><b>CLI</b>:<br><i>chmod 0664  include/ost-config.php</i></li>
            <li><b>FTP</b>:<br><?= _('Using WS_FTP this would be right hand clicking on the file, selecting chmod, and then remove write access')?></li>
            <li><b>Cpanel</b>:<br><?= _('Click on the file, select change permission, and then remove write access.')?></li>
        </ul>
        </div>
        <p><?= _('Below, you\'ll find some useful links regarding your installation.')?></p>
        <table border="0" cellspacing="0" cellpadding="5" width="580" id="links">
            <tr>
                    <td width="50%">
                        <strong><?= _('Your osTicket URL:')?></strong><Br>
                        <a href="<?php echo $url; ?>"><?php echo $url; ?></a>
                    </td>
                    <td width="50%">
                        <strong><?= _('Your Staff Control Panel:')?></strong><Br>
                        <a href="../scp/admin.php"><?php echo $url; ?>scp</a>
                    </td>
                </tr>
                <tr>
                    <td width="50%">
                        <strong><?= _('osTicket Forums:')?></strong><Br>
                        <a href="#">http://osticket.com/forums/</a>
                    </td>
                    <td width="50%">
                        <strong><?= _('osTicket Community Wiki:')?></strong><Br>
                        <a href="#">http://osticket.com/wiki/</a>
                    </td>
                </tr>
            </table>
            <p><b>PS</b>: <?= _('Don\'t just make customers happy, make happy customers!')?></p>
    </div>
    <div id="sidebar">
            <h3><?= _('What\'s Next?')?></h3>
            <p><b><?= _('Post-Install Setup')?></b>: <?= _('You can now log in to')?> <a href="../scp/admin.php" target="_blank"><?= _('Admin Panel')?></a> <?= _('with the username and password you created during the install process. After a successful log in, you can proceed with post-install setup. For complete and upto date guide see')?> <a href="http://osticket.com/wiki/Post-Install_Setup_Guide" target="_blank"><?= _('osTicket wiki')?></a></p>

            <p><b><?= _('Commercial Support Available')?></b>: <?= _('Don\'t let technical problems impact your osTicket implementation. Get guidance and hands-on expertise to address unique challenges and make sure your osTicket runs smoothly, efficiently, and securely.')?> <a target="_blank" href="http://osticket.com/support/commercial_support.php.php"><?= _('Learn More!')?></a></p>
   </div>
