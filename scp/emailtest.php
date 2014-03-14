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
require_once(INCLUDE_DIR.'languages/language_control/languages_processor.php');

$info=array();
$info['subj']=lang('ticket_test_email');

if($_POST){
    $errors=array();
    $email=null;
    if(!$_POST['email_id'] || !($email=Email::lookup($_POST['email_id'])))
        $errors['email_id']=lang('select_from_email');

    if(!$_POST['email'] || !Validator::is_email($_POST['email']))
        $errors['email']=lang('to_email_adress');

    if(!$_POST['subj'])
        $errors['subj']=lang('subject_required');

    if(!$_POST['message'])
        $errors['message']=lang('message_required');

    if(!$errors && $email){
        if($email->send($_POST['email'],$_POST['subj'],$_POST['message']))
            $msg=lang('test_email_succes').' '.Format::htmlchars($_POST['email']);
        else
            $errors['err']=lang('error_send_email');
    }elseif($errors['err']){
        $errors['err']=lang('error_send_email');
    }
}
$info=Format::htmlchars(($errors && $_POST)?$_POST:$info);
$nav->setTabActive('emails');
require(STAFFINC_DIR.'header.inc.php');
?>
<form action="emailtest.php" method="post" id="save">
 <?php csrf_token(); ?>
 <input type="hidden" name="do" value="<?php echo $action; ?>">
 <h2><?php echo lang('test_outg_emails'); ?></h2>
 <table class="form_table" width="940" border="0" cellspacing="0" cellpadding="2">
    <thead>
        <tr>
            <th colspan="2">
                <em><?php echo lang('email_delivery'); ?></em>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td width="120" class="required">
                <?php echo lang('from'); ?>:
            </td>
            <td>
                <select name="email_id">
                    <option value="0">&mdash; <?php echo lang('select_from_email'); ?> &mdash;</option>
                    <?php
                    $sql='SELECT email_id,email,name,smtp_active FROM '.EMAIL_TABLE.' email ORDER by name';
                    if(($res=db_query($sql)) && db_num_rows($res)){
                        while(list($id,$email,$name,$smtp)=db_fetch_row($res)){
                            $selected=($info['email_id'] && $id==$info['email_id'])?'selected="selected"':'';
                            if($name)
                                $email=Format::htmlchars("$name <$email>");
                            if($smtp)
                                $email.=' (SMTP)';

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
                <?php echo lang('to'); ?>:
            </td>
            <td>
                <input type="text" size="60" name="email" value="<?php echo $info['email']; ?>">
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['email']; ?></span>
            </td>
        </tr>
        <tr>
            <td width="120" class="required">
                <?php echo lang('subject'); ?>:
            </td>
            <td>
                <input type="text" size="60" name="subj" value="<?php echo $info['subj']; ?>">
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['subj']; ?></span>
            </td>
        </tr>
        <tr>
            <td colspan=2>
                <em><strong><?php echo lang('message'); ?></strong>: <?php echo lang('message_to_sent'); ?></em>&nbsp;<span class="error">*&nbsp;<?php echo $errors['message']; ?></span><br>
                <textarea name="message" cols="21" rows="10" style="width: 90%;"><?php echo $info['message']; ?></textarea>
            </td>
        </tr>
    </tbody>
</table>
<p style="padding-left:225px;">
    <input type="submit" name="submit" value="<?php echo lang('send_message'); ?>">
    <input type="reset"  name="reset"  value="<?php echo lang('reset'); ?>">
    <input type="button" name="cancel" value="<?php echo lang('subject'); ?>" onclick='window.location.href="emails.php"'>
</p>
</form>
<?php
include(STAFFINC_DIR.'footer.inc.php');
?>
