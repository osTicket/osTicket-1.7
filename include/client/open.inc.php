<?php
if(!defined('OSTCLIENTINC')) die(lang('access_denied'));
$info=array();
if($thisclient && $thisclient->isValid()) {
    $info=array('name'=>$thisclient->getName(),
                'email'=>$thisclient->getEmail(),
                'phone'=>$thisclient->getPhone(),
                'phone_ext'=>$thisclient->getPhoneExt());
}

$info=($_POST && $errors)?Format::htmlchars($_POST):$info;
?>
<style type="text/css">
    #ticketForm {}
    #ticketForm table tr {}
    #ticketForm table input {padding: 2px}
</style>
<h1><?php echo lang('open_new_ticket'); ?></h1>
<p><?php echo lang('fill_form_open_tic'); ?></p>
<script src="./js/jquery.ui-1.8.23.custom.min.js"></script>
<link rel="stylesheet" href="./css/ui-lightness/jquery-ui-1.8.18.custom.css" media="screen">
<script type="text/javascript">
    $(function(){
        var user_tickets = {names: [], data: []};
        
        function search() {
            user_tickets = {names: [], data: []};
            $('#show-loading').show();
            $.post('autocomplete.php?method=user_ticket',{
                name: $('#name').val()
            },function(d){
                $('#show-loading').hide();
                if(d.result) {
                    user_tickets.data = d.data;
                    for(var i in d.data)
                        user_tickets.names.push(d.data[i].name);
                }
                
                $( "#name" ).autocomplete({
                  source: user_tickets.names,
                  select: function(event, ui){
                    fillAutocomleteByName(ui.item.value);
                  }
                });
            },'json');
        }

        function fillAutocomleteByName(name) {
            for(var i in user_tickets.data) {
                if(user_tickets.data[i].name == name) {
                    $('#email').val(user_tickets.data[i].email);
                    $('#phone').val(user_tickets.data[i].phone);
                    $('#ext').val(user_tickets.data[i].phone_ext);
                }
            }
        }

        var _timeout = null;
        $('#name').bind('keyup',function(k){
            if(k.which == 38 || k.which == 40)
                return;
            if(_timeout)
                window.clearTimeout(_timeout);            
            _timeout = window.setTimeout(search,100);
        });
    });
</script>
<form id="ticketForm" method="post" action="open.php" enctype="multipart/form-data">
  <?php csrf_token(); ?>
  <input type="hidden" name="a" value="open">
  <table width="800" cellpadding="1" cellspacing="0" border="0">
    <tr>
        <th class="required" width="160"><?php echo lang('full_name'); ?>:</th>
        <td>
            <?php
            if($thisclient && $thisclient->isValid()) {
                echo $thisclient->getName();
            } else { ?>
                <input id="name" type="text" name="name" autocomplete="off" size="30" value="<?php echo $info['name']; ?>">
                <font class="error">*&nbsp;<?php echo $errors['name']; ?></font>
            <?php
            } ?>
            <img id="show-loading" src="./images/FhHRx-Spinner.gif" style="display:none;width:20px;height:20px;">
        </td>
    </tr>
    <tr>
        <th class="required" width="160"><?php echo lang('email_address'); ?>:</th>
        <td>
            <?php
            if($thisclient && $thisclient->isValid()) { 
                echo $thisclient->getEmail();
            } else { ?>
                <input id="email" type="text" name="email" size="30" value="<?php echo $info['email']; ?>">
                <font class="error">*&nbsp;<?php echo $errors['email']; ?></font>
            <?php
            } ?>
        </td>
    </tr>
    <tr>
        <th><?php echo lang('telephone'); ?>:</th>
        <td>
            <input id="phone" type="text" name="phone" size="17" value="<?php echo $info['phone']; ?>">
            <font class="error">*&nbsp;<?php echo $errors['phone']; ?></font>            
        </td>
    </tr>
    <tr>
        <th><label for="ext" class="inline"><?php echo lang('ext'); ?>.:</label></th>
        <td>            
            <input id="ext" type="text" name="phone_ext" size="3" value="<?php echo $info['phone_ext']; ?>">
            <font class="error">*&nbsp;<?php echo $errors['phone_ext']; ?></font>
        </td>   
    </tr>
    <tr><td colspan=2>&nbsp;</td></tr>
    <tr>
        <td class="required"><?php echo lang('help_topic'); ?>: </td>
        <td>
            <select id="topicId" name="topicId">
                <option value="" selected="selected">&mdash; <?php echo lang('select_help_topic'); ?> &mdash;</option>
                <?php
                if($topics=Topic::getPublicHelpTopics()) {
                    foreach($topics as $id =>$name) {
                        echo sprintf('<option value="%d" %s>%s</option>',
                                $id, ($info['topicId']==$id)?'selected="selected"':'', $name);
                    }
                } else { ?>
                    <option value="0" ><?php echo lang('general_inquiry'); ?></option>
                <?php
                } ?>
            </select>
            <font class="error">*&nbsp;<?php echo $errors['topicId']; ?></font>
        </td>
    </tr>
    <tr>
        <td class="required"><?php echo lang('subject'); ?>:</td>
        <td>
            <input id="subject" type="text" name="subject" size="40" value="<?php echo $info['subject']; ?>">
            <font class="error">*&nbsp;<?php echo $errors['subject']; ?></font>
        </td>
    </tr>
    <tr>
        <td class="required"><?php echo lang('message'); ?>:</td>
        <td>
            <div><em><?php echo lang('provide_det_only'); ?></em> <font class="error">*&nbsp;<?php echo $errors['message']; ?></font></div>
            <textarea id="message" cols="60" rows="8" name="message"><?php echo $info['message']; ?></textarea>
        </td>
    </tr>

    <?php if(($cfg->allowOnlineAttachments() && !$cfg->allowAttachmentsOnlogin())
            || ($cfg->allowAttachmentsOnlogin() && ($thisclient && $thisclient->isValid()))) { ?>
    <tr>
        <td><?php echo lang('attachments'); ?>:</td>
        <td>
            <div class="uploads"></div><br>
            <input type="file" class="multifile" name="attachments[]" id="attachments" size="30" value="" />
            <font class="error">&nbsp;<?php echo $errors['attachments']; ?></font>
        </td>
    </tr>
    <tr><td colspan=2>&nbsp;</td></tr>
    <?php } ?>
    <?php
    if($cfg->allowPriorityChange() && ($priorities=Priority::getPriorities())) { ?>
    <tr>
        <td><?php echo lang('tique_priority'); ?>:</td>
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
            <font class="error">&nbsp;<?php echo $errors['priorityId']; ?></font>
        </td>
    </tr>
    <?php
    }
    ?>
    <?php
    if($cfg && $cfg->isCaptchaEnabled() && (!$thisclient || !$thisclient->isValid())) {
        if($_POST && $errors && !$errors['captcha'])
            $errors['captcha']=lang('reenter_text');
        ?>
    <tr class="captchaRow">
        <td class="required"><?php echo lang('captcha_text'); ?>:</td>
        <td>
            <span class="captcha"><img src="captcha.php" border="0" align="left"></span>
            &nbsp;&nbsp;
            <input id="captcha" type="text" name="captcha" size="6">
            <em><?php echo lang('enter_text_show'); ?></em>
            <font class="error">*&nbsp;<?php echo $errors['captcha']; ?></font>
        </td>
    </tr>
    <?php
    } ?>
    <tr><td colspan=2>&nbsp;</td></tr>
  </table>
  <p style="padding-left:150px;">
        <input type="submit" value="<?php echo lang('create_ticket'); ?>">
        <input type="reset" value="<?php echo lang('reset'); ?>">
        <input type="button" value="<?php echo lang('cancel'); ?>" onClick='window.location.href="index.php"'>
  </p>
</form>
