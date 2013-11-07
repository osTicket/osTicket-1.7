<?php
if(!defined('OSTADMININC') || !$thisstaff || !$thisstaff->isAdmin()) die(lang('access_denied'));
$info=array();
$qstr='';
if($group && $_REQUEST['a']!='add'){
    $title=lang('update_group');
    $action='update';
    $submit_text=lang('save_changes');
    $info=$group->getInfo();
    $info['id']=$group->getId();
    $info['depts']=$group->getDepartments();
    $qstr.='&id='.$group->getId();
}else {
    $title=lang('add_new_group');
    $action='create';
    $submit_text=lang('create_group');
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
 <h2><?php echo lang("user_group"); ?></h2>
 <table class="form_table" width="940" border="0" cellspacing="0" cellpadding="2">
    <thead>
        <tr>
            <th colspan="2">
                <h4><?php echo $title; ?></h4>
                <em><strong><?php echo lang("group_info"); ?></strong>: <?php echo lang("limit_staff_memb_acc"); ?>.</em>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td width="180" class="required">
                <?php echo lang("name"); ?>:
            </td>
            <td>
                <input type="text" size="30" name="name" value="<?php echo $info['name']; ?>">
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['name']; ?></span>
            </td>
        </tr>
        <tr>
            <td width="180" class="required">
                <?php echo lang("status"); ?>:
            </td>
            <td>
                <input type="radio" name="isactive" value="1" <?php echo $info['isactive']?'checked="checked"':''; ?>><strong><?php echo lang("active"); ?></strong>
                <input type="radio" name="isactive" value="0" <?php echo !$info['isactive']?'checked="checked"':''; ?>><strong><?php echo lang("disabled"); ?></strong>
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['status']; ?></span>
            </td>
        </tr>
        <tr>
            <th colspan="2">
                <em><strong><?php echo lang("group_permissions"); ?></strong>: <?php echo lang("applies_to_group_memb"); ?>&nbsp;</em>
            </th>
        </tr>
        <tr><td><?php echo lang("can_create_tickets"); ?></td>
            <td>
                <input type="radio" name="can_create_tickets"  value="1"   <?php echo $info['can_create_tickets']?'checked="checked"':''; ?> /><?php echo lang("yes"); ?>
                &nbsp;&nbsp;
                <input type="radio" name="can_create_tickets"  value="0"   <?php echo !$info['can_create_tickets']?'checked="checked"':''; ?> /><?php echo lang("no"); ?>
                &nbsp;&nbsp;<i><?php echo lang("ability_open_ticket"); ?>.</i>
            </td>
        </tr>
        <tr><td><?php echo lang("can_edit_tickets"); ?></td>
            <td>
                <input type="radio" name="can_edit_tickets"  value="1"   <?php echo $info['can_edit_tickets']?'checked="checked"':''; ?> /><?php echo lang("yes"); ?>
                &nbsp;&nbsp;
                <input type="radio" name="can_edit_tickets"  value="0"   <?php echo !$info['can_edit_tickets']?'checked="checked"':''; ?> /><?php echo lang("no"); ?>
                &nbsp;&nbsp;<i><?php echo lang("ability_open_ticket"); ?>.</i>
            </td>
        </tr>
        <tr><td><?php echo lang("can_post_reply"); ?></td>
            <td>
                <input type="radio" name="can_post_ticket_reply"  value="1"   <?php echo $info['can_post_ticket_reply']?'checked="checked"':''; ?> /><?php echo lang("yes"); ?>
                &nbsp;&nbsp;
                <input type="radio" name="can_post_ticket_reply"  value="0"   <?php echo !$info['can_post_ticket_reply']?'checked="checked"':''; ?> /><?php echo lang("no"); ?>
                &nbsp;&nbsp;<i><?php echo lang("ability_post_ticket"); ?>.</i>
            </td>
        </tr>
        <tr><td><?php echo lang("can_close_tickets"); ?></td>
            <td>
                <input type="radio" name="can_close_tickets"  value="1" <?php echo $info['can_close_tickets']?'checked="checked"':''; ?> /><?php echo lang("yes"); ?>
                &nbsp;&nbsp;
                <input type="radio" name="can_close_tickets"  value="0" <?php echo !$info['can_close_tickets']?'checked="checked"':''; ?> /><?php echo lang("no"); ?>
                &nbsp;&nbsp;<i><?php echo lang("ability_close_ticket"); ?>.</i>
            </td>
        </tr>
        <tr><td><?php echo lang("can_assign_ticket"); ?></td>
            <td>
                <input type="radio" name="can_assign_tickets"  value="1" <?php echo $info['can_assign_tickets']?'checked="checked"':''; ?> /><?php echo lang("yes"); ?>
                &nbsp;&nbsp;
                <input type="radio" name="can_assign_tickets"  value="0" <?php echo !$info['can_assign_tickets']?'checked="checked"':''; ?> /><?php echo lang("no"); ?>
                &nbsp;&nbsp;<i><?php echo lang("ability_assign_ticket"); ?>.</i>
            </td>
        </tr>
        <tr><td><?php echo lang("can_transfer_tickets"); ?></td>
            <td>
                <input type="radio" name="can_transfer_tickets"  value="1" <?php echo $info['can_transfer_tickets']?'checked="checked"':''; ?> /><?php echo lang("yes"); ?>
                &nbsp;&nbsp;
                <input type="radio" name="can_transfer_tickets"  value="0" <?php echo !$info['can_transfer_tickets']?'checked="checked"':''; ?> /><?php echo lang("no"); ?>
                &nbsp;&nbsp;<i><?php echo lang("ability_transfer_ticket"); ?>.</i>
            </td>
        </tr>
        <tr><td><?php echo lang("can_delete_tickets"); ?></td>
            <td>
                <input type="radio" name="can_delete_tickets"  value="1"   <?php echo $info['can_delete_tickets']?'checked="checked"':''; ?> /><?php echo lang("yes"); ?>
                &nbsp;&nbsp;
                <input type="radio" name="can_delete_tickets"  value="0"   <?php echo !$info['can_delete_tickets']?'checked="checked"':''; ?> /><?php echo lang("no"); ?>
                &nbsp;&nbsp;<i><?php echo lang("ability_delete_ticket"); ?> (<?php echo lang("cant_recover_d_ticket"); ?>!)</i>
            </td>
        </tr>
        <tr><td><?php echo lang("can_ban_emails"); ?></td>
            <td>
                <input type="radio" name="can_ban_emails"  value="1" <?php echo $info['can_ban_emails']?'checked="checked"':''; ?> /><?php echo lang("yes"); ?>
                &nbsp;&nbsp;
                <input type="radio" name="can_ban_emails"  value="0" <?php echo !$info['can_ban_emails']?'checked="checked"':''; ?> /><?php echo lang("no"); ?>
                &nbsp;&nbsp;<i><?php echo lang("ability_add_email"); ?>.</i>
            </td>
        </tr>
        <tr><td><?php echo lang("can_manage_premade"); ?></td>
            <td>
                <input type="radio" name="can_manage_premade"  value="1" <?php echo $info['can_manage_premade']?'checked="checked"':''; ?> /><?php echo lang("yes"); ?>
                &nbsp;&nbsp;
                <input type="radio" name="can_manage_premade"  value="0" <?php echo !$info['can_manage_premade']?'checked="checked"':''; ?> /><?php echo lang("no"); ?>
                &nbsp;&nbsp;<i><?php echo lang("attach_not_apply"); ?>.</i>
            </td>
        </tr>
        <tr><td><?php echo lang("can_manage_faq"); ?></td>
            <td>
                <input type="radio" name="can_manage_faq"  value="1" <?php echo $info['can_manage_faq']?'checked="checked"':''; ?> /><?php echo lang("yes"); ?>
                &nbsp;&nbsp;
                <input type="radio" name="can_manage_faq"  value="0" <?php echo !$info['can_manage_faq']?'checked="checked"':''; ?> /><?php echo lang("no"); ?>
                &nbsp;&nbsp;<i><?php echo lang("ability_knowledgbase"); ?>.</i>
            </td>
        </tr>
        <tr><td><?php echo lang("can_view_staff"); ?></td>
            <td>
                <input type="radio" name="can_view_staff_stats"  value="1" <?php echo $info['can_view_staff_stats']?'checked="checked"':''; ?> /><?php echo lang("yes"); ?>
                &nbsp;&nbsp;
                <input type="radio" name="can_view_staff_stats"  value="0" <?php echo !$info['can_view_staff_stats']?'checked="checked"':''; ?> /><?php echo lang("no"); ?>
                &nbsp;&nbsp;<i><?php echo lang("ability_view_stats"); ?>.</i>
            </td>
        </tr>
        <tr>
            <th colspan="2">
                <em><strong><?php echo lang("department_access"); ?></strong>: <?php echo lang("check_departm"); ?>.&nbsp;&nbsp;&nbsp;<a id="selectAll" href="#deptckb"><?php echo lang("select_all"); ?></a>&nbsp;&nbsp;<a id="selectNone" href="#deptckb">Select None</a>&nbsp;&nbsp;</em>
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
                <em><strong><?php echo lang("admin_notes"); ?></strong>: <?php echo lang("internal_notes_view"); ?>.&nbsp;</em>
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
    <input type="reset"  name="<?php echo lang("reset"); ?>"  value="<?php echo lang("reset"); ?>">
    <input type="button" name="cancel" value="<?php echo lang("cancel"); ?>" onclick='window.location.href="groups.php"'>
</p>
</form>
