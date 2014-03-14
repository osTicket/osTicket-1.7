<?php
if(!defined('OSTSTAFFINC') || !$staff || !$thisstaff) die(lang('access_denied'));

$info=$staff->getInfo();
$info=Format::htmlchars(($errors && $_POST)?$_POST:$info);
$info['id']=$staff->getId();
?>
<form action="profile.php" method="post" id="save" autocomplete="off">
 <?php csrf_token(); ?>
 <input type="hidden" name="do" value="update">
 <input type="hidden" name="id" value="<?php echo $info['id']; ?>">
 <h2><?php echo lang('account_profile'); ?></h2>
 <table class="form_table" width="940" border="0" cellspacing="0" cellpadding="2">
    <thead>
        <tr>
            <th colspan="2">
                <h4><?php echo lang('account_info'); ?></h4>
                <em><?php echo lang('contact_info'); ?>.</em>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td width="180" class="required">
                <?php echo lang('username'); ?>:
            </td>
            <td><b><?php echo $staff->getUserName(); ?></b></td>
        </tr>

        <tr>
            <td width="180" class="required">
                <?php echo lang('first_name'); ?>:
            </td>
            <td>
                <input type="text" size="34" name="firstname" value="<?php echo $info['firstname']; ?>">
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['firstname']; ?></span>
            </td>
        </tr>
        <tr>
            <td width="180" class="required">
                <?php echo lang('last_name'); ?>:
            </td>
            <td>
                <input type="text" size="34" name="lastname" value="<?php echo $info['lastname']; ?>">
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['lastname']; ?></span>
            </td>
        </tr>
        <tr>
            <td width="180" class="required">
                <?php echo lang('email_address'); ?>:
            </td>
            <td>
                <input type="text" size="34" name="email" value="<?php echo $info['email']; ?>">
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['email']; ?></span>
            </td>
        </tr>
        <tr>
            <td width="180">
                <?php echo lang('phone_number'); ?>:
            </td>
            <td>
                <input type="text" size="22" name="phone" value="<?php echo $info['phone']; ?>">
                &nbsp;<span class="error">&nbsp;<?php echo $errors['phone']; ?></span>
                <?php echo lang('ext'); ?> <input type="text" size="5" name="phone_ext" value="<?php echo $info['phone_ext']; ?>">
                &nbsp;<span class="error">&nbsp;<?php echo $errors['phone_ext']; ?></span>
            </td>
        </tr>
        <tr>
            <td width="180">
                <?php echo lang('mobile_number'); ?>:
            </td>
            <td>
                <input type="text" size="22" name="mobile" value="<?php echo $info['mobile']; ?>">
                &nbsp;<span class="error">&nbsp;<?php echo $errors['mobile']; ?></span>
            </td>
        </tr>
        <tr>
            <th colspan="2">
                <em><strong><?php echo lang('preferences'); ?></strong>: <?php echo lang('preferences_set'); ?></em>
            </th>
        </tr>
        <tr>
            <td width="180" class="required">
                <?php echo lang('time_zone'); ?>:
            </td>
            <td>
                <select name="timezone_id" id="timezone_id">
                    <option value="0">&mdash; <?php echo lang('select'); ?> <?php echo lang('time_zone'); ?> &mdash;</option>
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
            <td width="180"><?php echo lang('max_page_site'); ?>:</td>
            <td>
                <select name="max_page_size">
                    <option value="0">&mdash; <?php echo lang('system_default'); ?> &mdash;</option>
                    <?php
                    $pagelimit=$info['max_page_size']?$info['max_page_size']:$cfg->getPageSize();
                    for ($i = 5; $i <= 50; $i += 5) {
                        $sel=($pagelimit==$i)?'selected="selected"':'';
                         echo sprintf('<option value="%d" %s>'.lang('show').' %s '.lang('records').'</option>',$i,$sel,$i);
                    } ?>
                </select> <?php echo lang('per_page'); ?>
            </td>
        </tr>
        <tr>
            <td width="180"><?php echo lang('auto_refreshr'); ?>:</td>
            <td>
                <select name="auto_refresh_rate">
                  <option value="0">&mdash; <?php echo lang('disable'); ?> &mdash;</option>
                  <?php
                  $y=1;
                   for($i=1; $i <=30; $i+=$y) {
                     $sel=($info['auto_refresh_rate']==$i)?'selected="selected"':'';
                     echo sprintf('<option value="%d" %s>'.lang('every').'%s %s</option>',$i,$sel,$i,($i>1?lang('mins'):lang('min')));
                     if($i>9)
                        $y=2;
                   } ?>
                </select>
                <em>(<?php echo lang('refresh_in_minutes'); ?>)</em>
            </td>
        </tr>
        <tr>
            <td width="180"><?php echo lang('default_signature'); ?>:</td>
            <td>
                <select name="default_signature_type">
                  <option value="none" selected="selected">&mdash; <?php echo lang('none'); ?> &mdash;</option>
                  <?php
                  $options=array('mine'=>lang('my_signature'),'dept'=>lang('dept_signature'));
                  foreach($options as $k=>$v) {
                      echo sprintf('<option value="%s" %s>%s</option>',
                                $k,($info['default_signature_type']==$k)?'selected="selected"':'',$v);
                  }
                  ?>
                </select>
                <em>(<?php echo lang('change_selection'); ?>)</em>
                &nbsp;<span class="error">&nbsp;<?php echo $errors['default_signature_type']; ?></span>
            </td>
        </tr>
        <tr>
            <td width="180"><?php echo lang('paper_size'); ?>:</td>
            <td>
                <select name="default_paper_size">
                  <option value="none" selected="selected">&mdash; <?php echo lang('none'); ?> &mdash;</option>
                  <?php
                  $options=array('Letter', 'Legal', 'A4', 'A3');
                  foreach($options as $v) {
                      echo sprintf('<option value="%s" %s>%s</option>',
                                $v,($info['default_paper_size']==$v)?'selected="selected"':'',$v);
                  }
                  ?>
                </select>
                <em><?php echo lang('tickets_to_pdf'); ?></em>
                &nbsp;<span class="error">&nbsp;<?php echo $errors['default_paper_size']; ?></span>
            </td>
        </tr>
        <?php
        //Show an option to show assigned tickets to admins & managers.
        if($staff->isAdmin() || $staff->isManager()){ ?>
        <tr>
            <td><?php echo lang('show_assigned_tick'); ?>:</td>
            <td>
                <input type="checkbox" name="show_assigned_tickets" <?php echo $info['show_assigned_tickets']?'checked="checked"':''; ?>>
                <em><?php echo lang('tickets_on_open_q'); ?></em>
            </td>
        </tr>
        <?php } ?>
        <tr>
            <th colspan="2">
                <em><strong><?php echo lang('password'); ?></strong>: <?php echo lang('reset_password'); ?>&nbsp;<span class="error">&nbsp;<?php echo $errors['passwd']; ?></span></em>
            </th>
        </tr>
        <tr>
            <td width="180">
                <?php echo lang('current_pass'); ?>:
            </td>
            <td>
                <input type="password" size="18" name="cpasswd" value="<?php echo $info['cpasswd']; ?>">
                &nbsp;<span class="error">&nbsp;<?php echo $errors['cpasswd']; ?></span>
            </td>
        </tr>
        <tr>
            <td width="180">
                <?php echo lang('new_pass'); ?>:
            </td>
            <td>
                <input type="password" size="18" name="passwd1" value="<?php echo $info['passwd1']; ?>">
                &nbsp;<span class="error">&nbsp;<?php echo $errors['passwd1']; ?></span>
            </td>
        </tr>
        <tr>
            <td width="180">
                <?php echo lang('confirm_new_pass'); ?>:
            </td>
            <td>
                <input type="password" size="18" name="passwd2" value="<?php echo $info['passwd2']; ?>">
                &nbsp;<span class="error">&nbsp;<?php echo $errors['passwd2']; ?></span>
            </td>
        </tr>
        <tr>
            <th colspan="2">
                <em><strong><?php echo lang('signature'); ?></strong>: <?php echo lang('op_signat_out_mails'); ?>
                &nbsp;<span class="error">&nbsp;<?php echo $errors['signature']; ?></span></em>
            </th>
        </tr>
        <tr>
            <td colspan=2>
                <textarea name="signature" cols="21" rows="5" style="width: 60%;"><?php echo $info['signature']; ?></textarea>
                <br><em><?php echo lang('signature_on_ticket'); ?></em>
            </td>
        </tr>
    </tbody>
</table>
<p style="padding-left:200px;">
    <input type="submit" name="submit" value="<?php echo lang('save_changes'); ?>">
    <input type="reset"  name="reset"  value="<?php echo lang('reset_changes'); ?>">
    <input type="button" name="cancel" value="<?php echo lang('cancel_changes'); ?>" onclick='window.location.href="index.php"'>
</p>
</form>
