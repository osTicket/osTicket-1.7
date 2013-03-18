<?php
if(!defined('OSTADMININC') || !$thisstaff || !$thisstaff->isAdmin()) die('Access Denied');
$info=array();
$qstr='';
if($group && $_REQUEST['a']!='add'){
    $title=_('Update Group');
    $action='update';
    $submit_text=_('Save Changes');
    $info=$group->getInfo();
    $info['id']=$group->getId();
    $info['depts']=$group->getDepartments();
    $qstr.='&id='.$group->getId();
}else {
    $title=_('Add New Group');
    $action='create';
    $submit_text=_('Create Group');
    $info['isactive']=isset($info['isactive'])?$info['isactive']:1;
    $info['can_create_tickets']=isset($info['can_create_tickets'])?$info['can_create_tickets']:1;
    $qstr.='&a='.$_REQUEST['a'];
}
$info=Format::htmlchars(($errors && $_POST)?$_POST:$info);
?>
<form action="groups.php?<?php echo $qstr; ?>" method="post" id="save" name="group">
 <?php csrf_token(); ?>
 <input type="hidden" name="do" value="<?php echo $action; ?>">
 <input type="hidden" name="a" value="<?php echo Format::htmlchars($_REQUEST['a']); ?>">
 <input type="hidden" name="id" value="<?php echo $info['id']; ?>">
 <h2><?php echo _('User Group');?></h2>
 <table class="form_table" width="940" border="0" cellspacing="0" cellpadding="2">
    <thead>
        <tr>
            <th colspan="2">
                <h4><?php echo $title; ?></h4>
                <em><strong><?php echo _('Group Information');?></strong>: <?php echo _('Disabled group will limit staff members access. Admins are exempted.');?></em>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td width="180" class="required">
                <?php echo _('Name:');?>
            </td>
            <td>
                <input type="text" size="30" name="name" value="<?php echo $info['name']; ?>">
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['name']; ?></span>
            </td>
        </tr>
        <tr>
            <td width="180" class="required">
                <?php echo _('Status:');?>
            </td>
            <td>
                <input type="radio" name="isactive" value="1" <?php echo $info['isactive']?'checked="checked"':''; ?>><strong><?php echo _('Active');?></strong>
                <input type="radio" name="isactive" value="0" <?php echo !$info['isactive']?'checked="checked"':''; ?>><strong><?php echo _('Disabled');?></strong>
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['status']; ?></span>
            </td>
        </tr>
        <tr>
            <th colspan="2">
                <em><strong><?php echo _('Group Permissions');?></strong>: <?php echo _('Applies to all group members');?>&nbsp;</em>
            </th>
        </tr>
        <tr><td><?php echo _('Can <b>Create</b> Tickets');?></td>
            <td>
                <input type="radio" name="can_create_tickets"  value="1"   <?php echo $info['can_create_tickets']?'checked="checked"':''; ?> /><?php echo _('Yes');?>
                &nbsp;&nbsp;
                <input type="radio" name="can_create_tickets"  value="0"   <?php echo !$info['can_create_tickets']?'checked="checked"':''; ?> /><?php echo _('No');?>
                &nbsp;&nbsp;<i><?php echo _('Ability to open tickets on behalf of clients.');?></i>
            </td>
        </tr>
        <tr><td><?php echo _('Can <b>Edit</b> Tickets</td>');?>
            <td>
                <input type="radio" name="can_edit_tickets"  value="1"   <?php echo $info['can_edit_tickets']?'checked="checked"':''; ?> /><?php echo _('Yes');?>
                &nbsp;&nbsp;
                <input type="radio" name="can_edit_tickets"  value="0"   <?php echo !$info['can_edit_tickets']?'checked="checked"':''; ?> /><?php echo _('No');?>
                &nbsp;&nbsp;<i><?php echo _('Ability to edit tickets.');?></i>
            </td>
        </tr>
        <tr><td><?php echo _('Can <b>Post Reply</b>');?></td>
            <td>
                <input type="radio" name="can_post_ticket_reply"  value="1"   <?php echo $info['can_post_ticket_reply']?'checked="checked"':''; ?> /><?php echo _('Yes');?>
                &nbsp;&nbsp;
                <input type="radio" name="can_post_ticket_reply"  value="0"   <?php echo !$info['can_post_ticket_reply']?'checked="checked"':''; ?> /><?php echo _('No');?>
                &nbsp;&nbsp;<i><?php echo _('Ability to post a ticket reply.');?></i>
            </td>
        </tr>
        <tr><td><?php echo _('Can <b>Close</b> Tickets');?></td>
            <td>
                <input type="radio" name="can_close_tickets"  value="1" <?php echo $info['can_close_tickets']?'checked="checked"':''; ?> /><?php echo _('Yes');?>
                &nbsp;&nbsp;
                <input type="radio" name="can_close_tickets"  value="0" <?php echo !$info['can_close_tickets']?'checked="checked"':''; ?> /><?php echo _('No');?>
                &nbsp;&nbsp;<i><?php echo _('Ability to close tickets. Staff can still post a response.');?></i>
            </td>
        </tr>
        <tr><td><?php echo _('Can <b>Assign</b> Tickets');?></td>
            <td>
                <input type="radio" name="can_assign_tickets"  value="1" <?php echo $info['can_assign_tickets']?'checked="checked"':''; ?> /><?php echo _('Yes');?>
                &nbsp;&nbsp;
                <input type="radio" name="can_assign_tickets"  value="0" <?php echo !$info['can_assign_tickets']?'checked="checked"':''; ?> /><?php echo _('No');?>
                &nbsp;&nbsp;<i><?php echo _('Ability to assign tickets to staff members.');?></i>
            </td>
        </tr>
        <tr><td><?php echo _('Can <b>Transfer</b> Tickets');?></td>
            <td>
                <input type="radio" name="can_transfer_tickets"  value="1" <?php echo $info['can_transfer_tickets']?'checked="checked"':''; ?> /><?php echo _('Yes');?>
                &nbsp;&nbsp;
                <input type="radio" name="can_transfer_tickets"  value="0" <?php echo !$info['can_transfer_tickets']?'checked="checked"':''; ?> /><?php echo _('No');?>
                &nbsp;&nbsp;<i><?php echo _('Ability to transfer tickets between departments.');?></i>
            </td>
        </tr>
        <tr><td><?php echo _('Can <b>Delete</b> Tickets');?></td>
            <td>
                <input type="radio" name="can_delete_tickets"  value="1"   <?php echo $info['can_delete_tickets']?'checked="checked"':''; ?> /><?php echo _('Yes');?>
                &nbsp;&nbsp;
                <input type="radio" name="can_delete_tickets"  value="0"   <?php echo !$info['can_delete_tickets']?'checked="checked"':''; ?> /><?php echo _('No');?>
                &nbsp;&nbsp;<i><?php echo _("Ability to delete tickets (Deleted tickets can't be recovered!)");?></i>
            </td>
        </tr>
        <tr><td><?php echo _('Can Ban Emails');?></td>
            <td>
                <input type="radio" name="can_ban_emails"  value="1" <?php echo $info['can_ban_emails']?'checked="checked"':''; ?> /><?php echo _('Yes');?>
                &nbsp;&nbsp;
                <input type="radio" name="can_ban_emails"  value="0" <?php echo !$info['can_ban_emails']?'checked="checked"':''; ?> /><?php echo _('No');?>
                &nbsp;&nbsp;<i><?php echo _('Ability to add/remove emails from banlist via ticket interface.');?></i>
            </td>
        </tr>
        <tr><td><?php echo _('Can Manage Premade');?></td>
            <td>
                <input type="radio" name="can_manage_premade"  value="1" <?php echo $info['can_manage_premade']?'checked="checked"':''; ?> /><?php echo _('Yes');?>
                &nbsp;&nbsp;
                <input type="radio" name="can_manage_premade"  value="0" <?php echo !$info['can_manage_premade']?'checked="checked"':''; ?> /><?php echo _('No');?>
                &nbsp;&nbsp;<i><?php echo _('Ability to add/update/disable/delete canned responses and attachments.');?></i>
            </td>
        </tr>
        <tr><td><?php echo _('Can Manage FAQ');?></td>
            <td>
                <input type="radio" name="can_manage_faq"  value="1" <?php echo $info['can_manage_faq']?'checked="checked"':''; ?> /><?php echo _('Yes');?>
                &nbsp;&nbsp;
                <input type="radio" name="can_manage_faq"  value="0" <?php echo !$info['can_manage_faq']?'checked="checked"':''; ?> /><?php echo _('No');?>
                &nbsp;&nbsp;<i><?php echo _('Ability to add/update/disable/delete knowledgebase categories and FAQs.');?></i>
            </td>
        </tr>
        <tr><td><?php echo _('Can View Staff Stats.');?></td>
            <td>
                <input type="radio" name="can_view_staff_stats"  value="1" <?php echo $info['can_view_staff_stats']?'checked="checked"':''; ?> /><?php echo _('Yes');?>
                &nbsp;&nbsp;
                <input type="radio" name="can_view_staff_stats"  value="0" <?php echo !$info['can_view_staff_stats']?'checked="checked"':''; ?> /><?php echo _('No');?>
                &nbsp;&nbsp;<i><?php echo _('Ability to view stats of other staff members in allowed departments.');?></i>
            </td>
        </tr>
        <tr>
            <th colspan="2">
                <em><strong><?php echo _('Department Access');?></strong>: <?php echo _('Check all departments the group members are allowed to access.');?>&nbsp;&nbsp;&nbsp;<a id="selectAll" href="#deptckb"><?php echo _('Select All');?></a>&nbsp;&nbsp;<a id="selectNone" href="#deptckb"><?php echo _('Select None');?></a>&nbsp;&nbsp;</em>
            </th>
        </tr>
        <?php
         $sql='SELECT dept_id,dept_name FROM '.DEPT_TABLE.' ORDER BY dept_name';
         if(($res=db_query($sql)) && db_num_rows($res)){
            while(list($id,$name) = db_fetch_row($res)){
                $ck=($info['depts'] && in_array($id,$info['depts']))?'checked="checked"':'';
                echo sprintf('<tr><td colspan=2>&nbsp;&nbsp;<input type="checkbox" class="deptckb" name="depts[]" value="%d" %s>%s</td></tr>',$id,$ck,$name);
            }
         }
        ?>
        <tr>
            <th colspan="2">
                <em><strong><?php echo _('Admin Notes');?></strong>: <?php echo _('Internal notes viewable by all admins.');?>&nbsp;</em>
            </th>
        </tr>
        <tr>
            <td colspan=2>
                <textarea name="notes" cols="21" rows="8" style="width: 80%;"><?php echo $info['notes']; ?></textarea>
            </td>
        </tr>
    </tbody>
</table>
<p style="padding-left:225px;">
    <input type="submit" name="submit" value="<?php echo $submit_text; ?>">
    <input type="reset"  name="reset"  value="<?php echo _('Reset');?>">
    <input type="button" name="cancel" value="<?php echo _('Cancel');?>" onclick='window.location.href="groups.php"'>
</p>
</form>
