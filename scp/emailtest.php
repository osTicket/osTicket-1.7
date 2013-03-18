<?php
/*********************************************************************
    emailtest.php

    Email Diagnostic 

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require('admin.inc.php');
include_once(INCLUDE_DIR.'class.email.php');
include_once(INCLUDE_DIR.'class.csrf.php');
$info=array();
$info['subj']='osTicket test email';

if($_POST){
    $errors=array();
    $email=null;
    if(!$_POST['email_id'] || !($email=Email::lookup($_POST['email_id'])))
        $errors['email_id']=_('Select from email address');

    if(!$_POST['email'] || !Validator::is_email($_POST['email']))
        $errors['email']=_('To email address required');

    if(!$_POST['subj'])
        $errors['subj']=_('Subject required');

    if(!$_POST['message'])
        $errors['message']=_('Message required');

    if(!$errors && $email){
        if($email->send($_POST['email'],$_POST['subj'],$_POST['message']))
            $msg=sprintf(_('Test email sent successfully to %s'), Format::htmlchars($_POST['email']));
        else
            $errors['err']=_('Error sending email - try again.');
    }elseif($errors['err']){
        $errors['err']=_('Error sending email - try again.');
    }
}
$info=Format::htmlchars(($errors && $_POST)?$_POST:$info);
$nav->setTabActive('emails');
require(STAFFINC_DIR.'header.inc.php');
?>
<form action="emailtest.php" method="post" id="save">
 <?php csrf_token(); ?>
 <input type="hidden" name="do" value="<?php echo $action; ?>">
 <h2><?php echo _('Test Outgoing Email');?></h2>
 <table class="form_table" width="940" border="0" cellspacing="0" cellpadding="2">
    <thead>
        <tr>
            <th colspan="2">
                <em><?php echo _('Emails delivery depends on your server settings (php.ini) and/or email SMTP configuration.');?></em>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td width="120" class="required">
                <?php echo _('From');?>:
            </td>
            <td>
                <select name="email_id">
                    <option value="0">&mdash; <?php echo _('Select FROM Email');?> &mdash;</option>
                    <?php
                    $sql='SELECT email_id,email,name,smtp_active FROM '.EMAIL_TABLE.' email ORDER by name';
                    if(($res=db_query($sql)) && db_num_rows($res)){
                        while(list($id,$email,$name,$smtp)=db_fetch_row($res)){
                            $selected=($info['email_id'] && $id==$info['email_id'])?'selected="selected"':'';
                            if($name)
                                $email=Format::htmlchars("$name <$email>");
                            if($smtp)
                                $email.=' ('._('SMTP').')';

                            echo sprintf('<option value="%d" %s>%s</option>',$id,$selected,$email);
                        }
                    }
                    ?>
                </select>
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['email_id']; ?></span>
            </td>
        </tr>
        <tr>
            <td width="120" class="required">
                <?php echo _('To');?>:
            </td>
            <td>
                <input type="text" size="60" name="email" value="<?php echo $info['email']; ?>">
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['email']; ?></span>
            </td>
        </tr>
        <tr>
            <td width="120" class="required">
                <?php echo _('Subject');?>:
            </td>
            <td>
                <input type="text" size="60" name="subj" value="<?php echo $info['subj']; ?>">
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['subj']; ?></span>
            </td>
        </tr>
        <tr>
            <td colspan=2>
                <em><strong><?php echo _('Message');?></strong>: <?php echo _('email message to send.');?></em>&nbsp;<span class="error">*&nbsp;<?php echo $errors['message']; ?></span><br>
                <textarea name="message" cols="21" rows="10" style="width: 90%;"><?php echo $info['message']; ?></textarea>
            </td>
        </tr>
    </tbody>
</table>
<p style="padding-left:225px;">
    <input type="submit" name="submit" value="<?php echo _('Send Message');?>">
    <input type="reset"  name="reset"  value="<?php echo _('Reset');?>">
    <input type="button" name="cancel" value="<?php echo _('Cancel');?>" onclick='window.location.href="emails.php"'>
</p>
</form>
<?php
include(STAFFINC_DIR.'footer.inc.php');
?>
