<?php
if(!defined('SETUPINC')) die('Kwaheri!');
?>
    <div id="main">
            <h1 style="color:#FF7700;"><?php echo _('Configuration file is not writable');?></h1>
            <div id="intro">
             <p>
             <?php echo _('osTicket installer requires ability to write to the configuration file <b>include/ost-config.php</b>. ');?>
             </p>
            </div>
            <h3><?php echo _('Solution');?>: <font color="red"><?php echo $errors['err']; ?></font></h3>
            <?php echo _('Please follow the instructions below to give read and write access to the web server user.');?>
            <ul>
                <li><b><?php echo _('CLI');?></b>:<br><i>chmod 0666  include/ost-config.php</i></li>
                <li><b><?php echo _('FTP');?></b>:<br><?php echo _('Using WS_FTP this would be right hand clicking on the fil, selecting chmod, and then giving all permissions to the file.');?></li>
                <li><b><?php echo _('Cpanel');?></b>:<br><?php echo _('Click on the file, select change permission, and then giving all permissions to the file.');?></li>
            </ul>

            <p><i><?php echo _('Don\'t worry! We\'ll remind you to take away the write access post-install');?></i>.</p>
            <div id="bar">
                <form method="post" action="install.php">
                    <input type="hidden" name="s" value="config">
                    <input class="btn"  type="submit" name="submit" value="<?php echo _('Done? Continue');?> &raquo;">
                </form>
            </div>
    </div>
    <div id="sidebar">
            <h3><?php echo _('Need Help?');?></h3>
            <p>
            <?php echo _('If you are looking for a greater level of support, we provide <u>professional installation services</u> and commercial support with guaranteed response times, and access to the core development team. We can also help customize osTicket or even add new features to the system to meet your unique needs.');?> <a target="_blank" href="http://osticket.com/support/professional_services.php"><?php echo _('Learn More!');?></a>
            </p>
    </div>
