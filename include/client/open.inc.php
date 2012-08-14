<?php
if(!defined('OSTCLIENTINC')) die('Access Denied!');
$info=array();
if($thisclient && $thisclient->isValid()) {
    $info=array('name'=>$thisclient->getName(),
                'email'=>$thisclient->getEmail(),
                'phone'=>$thisclient->getPhone(),
                'phone_ext'=>$thisclient->getPhoneExt());
}

$info=($_POST && $errors)?Format::htmlchars($_POST):$info;
?>
<h1>Open a New Ticket</h1>
<p class="intro">Please fill in the form below to open a new ticket.</p>

<form id="ticketForm" method="post" action="open.php" enctype="multipart/form-data">
  <?php csrf_token(); ?>
  <input type="hidden" name="a" value="open">

  <table cellpadding="0" cellspacing="0" class="table_form">
   <tbody>
    <tr class="required">
        <th>Full Name:</th>
        <td>
            <?php
            if($thisclient && $thisclient->isValid()) {
                echo $thisclient->getName();
            } else { ?>
                <input id="name" type="text" name="name" size="30" value="<?php echo $info['name']; ?>" />
                <span class="error">* <?php echo $errors['name']; ?></span>
            <?php
            } ?>
        </td>
    </tr>
    <tr class="required">
        <th>Email Address:</th>
        <td>
            <?php
            if($thisclient && $thisclient->isValid()) { 
                echo $thisclient->getEmail();
            } else { ?>
                <input id="email" type="text" name="email" size="30" value="<?php echo $info['email']; ?>" />
                <span class="error">* <?php echo $errors['email']; ?></span>
            <?php
            } ?>
        </td>
    </tr>
    <tr>
        <th>Telephone:</th>
        <td>
            <input id="phone" type="text" name="phone" size="17" value="<?php echo $info['phone']; ?>" />
            <label for="ext" class="inline">Ext.:</label>
            <input id="ext" type="text" name="phone_ext" size="3" value="<?php echo $info['phone_ext']; ?>" />
            <span class="error"><?php echo $errors['phone']; ?>&nbsp;&nbsp;<?php echo $errors['phone_ext']; ?></span>
        </td>   
    </tr>

    <tr class="tr_padding"><td colspan=2></td></tr>

    <tr class="required">
        <th>Help Topic:</th>
        <td>
            <select id="topicId" name="topicId">
                <option value="" selected="selected">&mdash; Select a Help Topics &mdash;</option>
                <?php
                if($topics=Topic::getPublicHelpTopics()) {
                    foreach($topics as $id =>$name) {
                        echo sprintf('<option value="%d" %s>%s</option>',
                                $id, ($info['topicId']==$id)?'selected="selected"':'', $name);
                    }
                } else { ?>
                    <option value="0" >General Inquiry</option>
                <?php
                } ?>
            </select>
            <span class="error">* <?php echo $errors['topicId']; ?></span>
        </td>
    </tr>
    <tr class="required">
        <th>Subject:</th>
        <td>
            <input id="subject" type="text" name="subject" size="40" value="<?php echo $info['subject']; ?>" />
            <span class="error">* <?php echo $errors['subject']; ?></span>
        </td>
    </tr>
    <tr class="required tr_message">
        <th>Message:</th>
        <td>
            <div class="textarea_desc">
				<em>Please provide as much details as possible so we can best assist you.</em> 
				<span class="error">* <?php echo $errors['message']; ?></span>
			</div>
            <textarea id="message" name="message"><?php echo $info['message']; ?></textarea>
        </td>
    </tr>

    <?php if(($cfg->allowOnlineAttachments() && !$cfg->allowAttachmentsOnlogin())
            || ($cfg->allowAttachmentsOnlogin() && ($thisclient && $thisclient->isValid()))) { ?>
    <tr>
        <th>Attachments:</th>
        <td>
            <div class="uploads"></div>
            <input type="file" class="multifile" name="attachments[]" id="attachments" size="30" value="" />
            <span class="error">&nbsp;<?php echo $errors['attachments']; ?></span>
        </td>
    </tr>

    <tr class="tr_padding"><td colspan=2></td></tr>

    <?php } ?>
    <?php
    if($cfg->allowPriorityChange() && ($priorities=Priority::getPriorities())) { ?>
    <tr>
        <th>Ticket Priority:</th>
        <td>
            <select id="priority" name="priorityId">
                <?php
                    if(!$info['priorityId'])
                        $info['priorityId'] = $cfg->getDefaultPriorityId(); //System default.
                    foreach($priorities as $id =>$name) {
                        echo sprintf('<option value="%d" %s>%s</option>',
                                        $id, ($info['priorityId']==$id)?'selected="selected"':'', $name);
                        
                    }
                ?>
            </select>
            <span class="error"><?php echo $errors['priorityId']; ?></span>
        </td>
    </tr>
    <?php
    }
    ?>
    <?php
    if($cfg && $cfg->isCaptchaEnabled() && (!$thisclient || !$thisclient->isValid())) {
        if($_POST && $errors && !$errors['captcha'])
            $errors['captcha']='Please re-enter the text again';
        ?>
    <tr class="required tr_captcha">
        <th>CAPTCHA Text:</th>
        <td>
            <span class="captcha"><img src="captcha.php" /></span>
            <input id="captcha" type="text" name="captcha" size="6">
            <span class='field_desc'>Enter the text shown on the image.</span>
            <span class="error">* <?php echo $errors['captcha']; ?></span>
        </td>
    </tr>
    <?php
    } ?>

    <tr class="tr_padding"><td colspan=2></td></tr>

   </tbody>
  </table>

  <p class="form_buttons">
        <input class='button submit' type="submit" value="Create Ticket">
        <input class='button reset' type="reset" value="Reset">
        <input class='button cancel' type="button" value="Cancel" onClick='window.location.href="index.php"'>
  </p>
</form>
