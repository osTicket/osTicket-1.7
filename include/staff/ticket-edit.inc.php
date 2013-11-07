<?php
if(!defined('OSTSCPINC') || !$thisstaff || !$thisstaff->canEditTickets() || !$ticket) die(lang('access_denied'));

$info=Format::htmlchars(($errors && $_POST)?$_POST:$ticket->getUpdateInfo());
?>
<form action="tickets.php?id=<?php echo $ticket->getId(); ?>&a=edit" method="post" id="save"  enctype="multipart/form-data">
 <?php csrf_token(); ?>
 <input type="hidden" name="do" value="update">
 <input type="hidden" name="a" value="edit">
 <input type="hidden" name="id" value="<?php echo $ticket->getId(); ?>">
 <h2>Update Ticket# <?php echo $ticket->getExtId(); ?></h2>
 <table class="form_table" width="940" border="0" cellspacing="0" cellpadding="2">
    <thead>
        <tr>
            <th colspan="2">
                <h4><?php echo lang('ticket_update'); ?></h4>
                <em><strong><?php echo lang('user_information'); ?></strong>: <?php echo lang('sure_email_valid'); ?></em>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td width="160" class="required">
                <?php echo lang('full_name'); ?>:
            </td>
            <td>
                <input type="text" size="50" name="name" value="<?php echo $info['name']; ?>">
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['name']; ?></span>
            </td>
        </tr>
        <tr>
            <td width="160" class="required">
                <?php echo lang('email_address'); ?>:
            </td>
            <td>
                <input type="text" size="50" name="email" value="<?php echo $info['email']; ?>">
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['email']; ?></span>
            </td>
        </tr>
        <tr>
            <td width="160">
                <?php echo lang('phone_number'); ?>:
            </td>
            <td>
                <input type="text" size="20" name="phone" value="<?php echo $info['phone']; ?>">
                &nbsp;<span class="error">&nbsp;<?php echo $errors['phone']; ?></span>
                <?php echo lang('ext'); ?> <input type="text" size="6" name="phone_ext" value="<?php echo $info['phone_ext']; ?>">
                &nbsp;<span class="error">&nbsp;<?php echo $errors['phone_ext']; ?></span>
            </td>
        </tr>
        <tr>
            <th colspan="2">
                <em><strong><?php echo lang('ticket_info'); ?></strong>: <?php echo lang('sure_email_valid'); ?></em>
            </th>
        </tr>
        <tr>
            <td width="160" class="required">
                <?php echo lang('ticket_source'); ?>:
            </td>
            <td>
                <select name="source">
                    <option value="" selected >&mdash; <?php echo lang('select_source'); ?> &mdash;</option>
                    <option value="Phone" <?php echo ($info['source']=='Phone')?'selected="selected"':''; ?>><?php echo lang('phone'); ?></option>
                    <option value="Email" <?php echo ($info['source']=='Email')?'selected="selected"':''; ?>><?php echo lang('email'); ?></option>
                    <option value="Web"   <?php echo ($info['source']=='Web')?'selected="selected"':''; ?>><?php echo lang('web'); ?></option>
                    <option value="API"   <?php echo ($info['source']=='API')?'selected="selected"':''; ?>><?php echo lang('api'); ?></option>
                    <option value="Other" <?php echo ($info['source']=='Other')?'selected="selected"':''; ?>><?php echo lang('other'); ?></option>
                </select>
                &nbsp;<font class="error"><b>*</b>&nbsp;<?php echo $errors['source']; ?></font>
            </td>
        </tr>
        <tr>
            <td width="160" class="required">
                <?php echo lang('help_topic'); ?>:
            </td>
            <td>
                <select name="topicId">
                    <option value="" selected >&mdash; <?php echo lang('select_help_topic'); ?> &mdash;</option>
                    <?php
                    if($topics=Topic::getHelpTopics()) {
                        foreach($topics as $id =>$name) {
                            echo sprintf('<option value="%d" %s>%s</option>',
                                    $id, ($info['topicId']==$id)?'selected="selected"':'',$name);
                        }
                    }
                    ?>
                </select>
                &nbsp;<font class="error"><b>*</b>&nbsp;<?php echo $errors['topicId']; ?></font>
            </td>
        </tr>
        <tr>
            <td width="160" class="required">
                <?php echo lang('priority_level'); ?>:
            </td>
            <td>
                <select name="priorityId">
                    <option value="" selected >&mdash; <?php echo lang('select_priority'); ?> &mdash;</option>
                    <?php
                    if($priorities=Priority::getPriorities()) {
                        foreach($priorities as $id =>$name) {
                            echo sprintf('<option value="%d" %s>%s</option>',
                                    $id, ($info['priorityId']==$id)?'selected="selected"':'',$name);
                        }
                    }
                    ?>
                </select>
                &nbsp;<font class="error">*&nbsp;<?php echo $errors['priorityId']; ?></font>
            </td>
        </tr>
        <tr>
            <td width="160" class="required">
                <?php echo lang('subject'); ?>:
            </td>
            <td>
                 <input type="text" name="subject" size="60" value="<?php echo $info['subject']; ?>">
                 &nbsp;<font class="error">*&nbsp;<?php $errors['subject']; ?></font>
            </td>
        </tr>
        <tr>
            <td width="160">
                <?php echo lang('sla_plan'); ?>:
            </td>
            <td>
                <select name="slaId">
                    <option value="0" selected="selected" >&mdash; <?php echo lang('none'); ?> &mdash;</option>
                    <?php
                    if($slas=SLA::getSLAs()) {
                        foreach($slas as $id =>$name) {
                            echo sprintf('<option value="%d" %s>%s</option>',
                                    $id, ($info['slaId']==$id)?'selected="selected"':'',$name);
                        }
                    }
                    ?>
                </select>
                &nbsp;<font class="error">&nbsp;<?php echo $errors['slaId']; ?></font>
            </td>
        </tr>
        <tr>
            <td width="160">
                <?php echo lang('due_date'); ?>:
            </td>
            <td>
                <input class="dp" id="duedate" name="duedate" value="<?php echo Format::htmlchars($info['duedate']); ?>" size="12" autocomplete=OFF>
                &nbsp;&nbsp;
                <?php
                $min=$hr=null;
                if($info['time'])
                    list($hr, $min)=explode(':', $info['time']);

                echo Misc::timeDropdown($hr, $min, 'time');
                ?>
                &nbsp;<font class="error">&nbsp;<?php echo $errors['duedate']; ?>&nbsp;<?php echo $errors['time']; ?></font>
                <em><?php echo lang('time_zone_based'); ?> (GMT <?php echo $thisstaff->getTZoffset(); ?>)</em>
            </td>
        </tr>
        <tr>
            <th colspan="2">
                <em><strong><?php echo lang('no_note'); ?></strong>: <?php echo lang('reason_edit_tick'); ?> <font class="error">&nbsp;<?php echo $errors['note'];?></font></em>
            </th>
        </tr>
        <tr>
            <td colspan=2>
                <textarea name="note" cols="21" rows="6" style="width:80%;"><?php echo $info['note']; ?></textarea>
            </td>
        </tr>
    </tbody>
</table>
<p style="padding-left:250px;">
    <input type="submit" name="submit" value="<?php echo lang('save'); ?>">
    <input type="reset"  name="reset"  value="<?php echo lang('reset'); ?>">
    <input type="button" name="cancel" value="<?php echo lang('cancel'); ?>" onclick='window.location.href="tickets.php?id=<?php echo $ticket->getId(); ?>"'>
</p>
</form>
