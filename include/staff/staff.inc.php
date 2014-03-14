<?php


if(!defined('OSTADMININC') || !$thisstaff || !$thisstaff->isAdmin()) die(lang('access_denied'));

$info=array();
$qstr='';
if($staff && $_REQUEST['a']!='add'){
    //Editing Department.
    $title=lang('update_staff');
    $action='update';
    $submit_text=lang('save_changes');
    $passwd_text=lang('reset_pass_enter');
    $info=$staff->getInfo();
    $info['id']=$staff->getId();
    $info['teams'] = $staff->getTeams();
    $qstr.='&id='.$staff->getId();
}else {
    $title=lang('add_new_staff');
    $action='create';
    $submit_text=lang('add_staff');
    $passwd_text=lang('temp_pass_requir').' &nbsp;<span class="error">&nbsp;*</span>';
    //Some defaults for new staff.
    $info['change_passwd']=1;
    $info['isactive']=1;
    $info['isvisible']=1;
    $info['isadmin']=0; 
    $qstr.='&a=add';
}
$info=Format::htmlchars(($errors && $_POST)?$_POST:$info);
?>
<form action="staff.php?<?php echo $qstr; ?>" method="post" id="save" autocomplete="off">
 <?php csrf_token(); ?>
 <input type="hidden" name="do" value="<?php echo $action; ?>">
 <input type="hidden" name="a" value="<?php echo Format::htmlchars($_REQUEST['a']); ?>">
 <input type="hidden" name="id" value="<?php echo $info['id']; ?>">
 <h2><?php echo lang("staff_account"); ?></h2>
 <table class="form_table" width="940" border="0" cellspacing="0" cellpadding="2">
    <thead>
        <tr>
            <th colspan="2">
                <h4><?php echo $title; ?></h4>
                <em><strong><?php echo lang("user_information"); ?></strong></em>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td width="180" class="required">
                <?php echo lang("username"); ?>:
            </td>
            <td>
                <input type="text" size="30" name="username" value="<?php echo $info['username']; ?>">
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['username']; ?></span>
            </td>
        </tr>

        <tr>
            <td width="180" class="required">
                <?php echo lang("first_name"); ?>:
            </td>
            <td>
                <input type="text" size="30" name="firstname" value="<?php echo $info['firstname']; ?>">
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['firstname']; ?></span>
            </td>
        </tr>
        <tr>
            <td width="180" class="required">
                <?php echo lang("last_name"); ?>:
            </td>
            <td>
                <input type="text" size="30" name="lastname" value="<?php echo $info['lastname']; ?>">
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['lastname']; ?></span>
            </td>
        </tr>
        <tr>
            <td width="180" class="required">
                <?php echo lang("email_address"); ?>:
            </td>
            <td>
                <input type="text" size="30" name="email" value="<?php echo $info['email']; ?>">
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['email']; ?></span>
            </td>
        </tr>
        <tr>
            <td width="180">
                <?php echo lang("phone_number"); ?>:
            </td>
            <td>
                <input type="text" size="18" name="phone" value="<?php echo $info['phone']; ?>">
                &nbsp;<span class="error">&nbsp;<?php echo $errors['phone']; ?></span>
                Ext <input type="text" size="5" name="phone_ext" value="<?php echo $info['phone_ext']; ?>">
                &nbsp;<span class="error">&nbsp;<?php echo $errors['phone_ext']; ?></span>
            </td>
        </tr>
        <tr>
            <td width="180">
                <?php echo lang("mobile_number"); ?>:
            </td>
            <td>
                <input type="text" size="18" name="mobile" value="<?php echo $info['mobile']; ?>">
                &nbsp;<span class="error">&nbsp;<?php echo $errors['mobile']; ?></span>
            </td>
        </tr>
        <tr>
            <th colspan="2">
                <em><strong><?php echo lang("account_pass"); ?></strong>: <?php echo $passwd_text; ?> &nbsp;<span class="error">&nbsp;<?php echo $errors['temppasswd']; ?></span></em>
            </th>
        </tr>
        <tr>
            <td width="180">
                <?php echo lang('password'); ?>:
            </td>
            <td>
                <input type="password" size="18" name="passwd1" value="<?php echo $info['passwd1']; ?>">
                &nbsp;<span class="error">&nbsp;<?php echo $errors['passwd1']; ?></span>
            </td>
        </tr>
        <tr>
            <td width="180">
                <?php echo lang('confirm_pass'); ?>:
            </td>
            <td>
                <input type="password" size="18" name="passwd2" value="<?php echo $info['passwd2']; ?>">
                &nbsp;<span class="error">&nbsp;<?php echo $errors['passwd2']; ?></span>
            </td>
        </tr>

        <tr>
            <td width="180">
                <?php echo lang('forced_pass_change'); ?>:
            </td>
            <td>
                <input type="checkbox" name="change_passwd" value="0" <?php echo $info['change_passwd']?'checked="checked"':''; ?>>
                <strong><?php echo lang('force'); ?></strong> <?php echo lang('pass_chang_next_log'); ?>
            </td>
        </tr>
        <tr>
            <th colspan="2">
                <em><strong><?php echo lang('staff_signature'); ?></strong>: <?php echo lang('opt_sig_out_email'); ?>. &nbsp;<span class="error">&nbsp;<?php echo $errors['signature']; ?></span></em>
            </th>
        </tr>
        <tr>
            <td colspan=2>
                <textarea name="signature" cols="21" rows="5" style="width: 60%;"><?php echo $info['signature']; ?></textarea>
                <br><em><?php echo lang('signature_on_ticket'); ?></em>
            </td>
        </tr>
        <tr>
            <th colspan="2">
                <em><strong><?php echo lang('account_status'); ?></strong>: <?php echo lang('dep_acces_permission'); ?></em>
            </th>
        </tr>
        <tr>
            <td width="180" class="required">
                <?php echo lang('account_type'); ?>:
            </td>
            <td>
                <input type="radio" name="isadmin" value="1" <?php echo $info['isadmin']?'checked="checked"':''; ?>>
                    <font color="red"><strong><?php echo lang('admin'); ?></strong></font>
                <input type="radio" name="isadmin" value="0" <?php echo !$info['isadmin']?'checked="checked"':''; ?>><strong><?php echo lang('staff'); ?></strong>
                &nbsp;<span class="error">&nbsp;<?php echo $errors['isadmin']; ?></span>
            </td>
        </tr>
        <tr>
            <td width="180" class="required">
                <?php echo lang('account_status_only'); ?>:
            </td>
            <td>
                <input type="radio" name="isactive" value="1" <?php echo $info['isactive']?'checked="checked"':''; ?>><strong><?php echo lang('active'); ?></strong>
                <input type="radio" name="isactive" value="0" <?php echo !$info['isactive']?'checked="checked"':''; ?>><strong><?php echo lang('locked'); ?></strong>
                &nbsp;<span class="error">&nbsp;<?php echo $errors['isactive']; ?></span>
            </td>
        </tr>
        <tr>
            <td width="180" class="required">
                <?php echo lang('assigned_group'); ?>:
            </td>
            <td>
                <select name="group_id" id="group_id">
                    <option value="0">&mdash; <?php echo lang('select_group'); ?> &mdash;</option>
                    <?php
                    $sql='SELECT group_id, group_name, group_enabled as isactive FROM '.GROUP_TABLE.' ORDER BY group_name';
                    if(($res=db_query($sql)) && db_num_rows($res)){
                        while(list($id,$name,$isactive)=db_fetch_row($res)){
                            $sel=($info['group_id']==$id)?'selected="selected"':'';
                            echo sprintf('<option value="%d" %s>%s %s</option>',$id,$sel,$name,($isactive?'':' ('.lang('disabled').')'));
                        }
                    }
                    ?>
                </select>
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['group_id']; ?></span>
            </td>
        </tr>
        <tr>
            <td width="180" class="required">
                <?php echo lang('primary_department'); ?>:
            </td>
            <td>
                <select name="dept_id" id="dept_id">
                    <option value="0">&mdash; <?php echo lang('select_department'); ?>&mdash;</option>
                    <?php
                    $sql='SELECT dept_id, dept_name FROM '.DEPT_TABLE.' ORDER BY dept_name';
                    if(($res=db_query($sql)) && db_num_rows($res)){
                        while(list($id,$name)=db_fetch_row($res)){
                            $sel=($info['dept_id']==$id)?'selected="selected"':'';
                            echo sprintf('<option value="%d" %s>%s</option>',$id,$sel,$name);
                        }
                    }
                    ?>
                </select>
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['dept_id']; ?></span>
            </td>
        </tr>
        <tr>
            <td width="180" class="required">
                <?php echo lang('staff_time_zone'); ?>:
            </td>
            <td>
                <select name="timezone_id" id="timezone_id">
                    <option value="0">&mdash; <?php echo lang('select_time_zone'); ?> &mdash;</option>
                    <?php
                    $sql='SELECT id, offset,timezone FROM '.TIMEZONE_TABLE.' ORDER BY id';
                    if(($res=db_query($sql)) && db_num_rows($res)){
                        while(list($id,$offset, $tz)=db_fetch_row($res)){
                            $sel=($info['timezone_id']==$id)?'selected="selected"':'';
                            echo sprintf('<option value="%d" %s>GMT %s - %s</option>',$id,$sel,$offset,$tz);
                        }
                    }
                    ?>
                </select>
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['timezone_id']; ?></span>
            </td>
        </tr>
        <tr>
            <td width="180">
               <?php echo lang('daylight_saving'); ?>:
            </td>
            <td>
                <input type="checkbox" name="daylight_saving" value="1" <?php echo $info['daylight_saving']?'checked="checked"':''; ?>>
                <?php echo lang('observ_dayl_saving'); ?>
                <em>(<?php echo lang('current_time'); ?>: <strong><?php echo Format::date($cfg->getDateTimeFormat(),Misc::gmtime(),$info['tz_offset'],$info['daylight_saving']); ?></strong>)</em>
            </td>
        </tr>
        <tr>
            <td width="180">
                <?php echo lang('limit_access'); ?>:
            </td>
            <td>
                <input type="checkbox" name="assigned_only" value="1" <?php echo $info['assigned_only']?'checked="checked"':''; ?>><?php echo lang('limit_tick_assig'); ?>
            </td>
        </tr>
        <tr>
            <td width="180">
                <?php echo lang('direct_listing'); ?>:
            </td>
            <td>
                <input type="checkbox" name="isvisible" value="1" <?php echo $info['isvisible']?'checked="checked"':''; ?>><?php echo lang('user_on_staffd'); ?>
            </td>
        </tr>
        <tr>
            <td width="180">
                <?php echo lang('vacation_mode'); ?>:
            </td>
            <td>
                <input type="checkbox" name="onvacation" value="1" <?php echo $info['onvacation']?'checked="checked"':''; ?>>
                    <?php echo lang('staff_vacati_mode'); ?> (<i><?php echo lang('no_ticket_assign'); ?></i>)
            </td>
        </tr>
        <?php
         //List team assignments.
         $sql='SELECT team.team_id, team.name, isenabled FROM '.TEAM_TABLE.' team  ORDER BY team.name';
         if(($res=db_query($sql)) && db_num_rows($res)){ ?>
        <tr>
            <th colspan="2">
                <em><strong><?php echo lang('assigned_teams'); ?></strong>: <?php echo lang('ticket_regardless'); ?> </em>
            </th>
        </tr>
        <?php
         while(list($id,$name,$isactive)=db_fetch_row($res)){
             $checked=($info['teams'] && in_array($id,$info['teams']))?'checked="checked"':'';
             echo sprintf('<tr><td colspan=2><input type="checkbox" name="teams[]" value="%d" %s>%s %s</td></tr>',
                     $id,$checked,$name,($isactive?'':' ('.lang('disabled').')'));
         }
        } ?>
        <tr>
            <th colspan="2">
                <em><strong><?php echo lang('admin_notes'); ?></strong>: <?php echo lang('internal_notes_view'); ?>&nbsp;</em>
            </th>
        </tr>
        <tr>
            <td colspan=2>
                <textarea name="notes" cols="28" rows="7" style="width: 80%;"><?php echo $info['notes']; ?></textarea>
            </td>
        </tr>
    </tbody>
</table>
<p style="padding-left:250px;">
    <input type="submit" name="submit" value="<?php echo $submit_text; ?>">
    <input type="reset"  name="reset"  value="<?php echo lang('reset'); ?>">
    <input type="button" name="cancel" value="<?php echo lang('cancel'); ?>" onclick='window.location.href="staff.php"'>
</p>
</form>
