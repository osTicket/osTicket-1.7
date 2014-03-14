<?php
if(!defined('OSTADMININC') || !$thisstaff->isAdmin()) die(lang('access_denied'));
//Get the config info.
$config=($errors && $_POST)?Format::input($_POST):$cfg->getConfigInfo();
?>
<table width="100%" border="0" cellspacing=0 cellpadding=0>
    <form action="admin.php?t=attach" method="post">
    <input type="hidden" name="t" value="attach">
    <tr>
      <td>
        <table width="100%" border="0" cellspacing=0 cellpadding=2 class="tform">
          <tr class="header">
            <td colspan=2>&nbsp;<?php echo lang('attachment_set'); ?></td>
          </tr>
          <tr class="subheader">
            <td colspan=2">
                <?php echo lang('sure_understand'); ?></td>
          </tr>
          <tr>
            <th width="165"><?php echo lang('allow_attachments'); ?>:</th>
            <td>
              <input type="checkbox" name="allow_attachments" <?php echo $config['allow_attachments'] ?'checked':''; ?>><b><?php echo lang('allow_attachments'); ?></b>
                &nbsp; (<i><?php echo lang('global_setting'); ?></i>)
                &nbsp;<font class="error">&nbsp;<?php echo $errors['allow_attachments']; ?></font>
            </td>
          </tr>
          <tr>
            <th><?php echo lang('emailed_attach'); ?>:</th>
            <td>
                <input type="checkbox" name="allow_email_attachments" <?php echo $config['allow_email_attachments'] ? 'checked':''; ?> > <?php echo lang('accept_emailed'); ?>
                    &nbsp;<font class="warn">&nbsp;<?php echo $warn['allow_email_attachments']; ?></font>
            </td>
          </tr>
         <tr>
            <th><?php echo lang('online_attach'); ?>:</th>
            <td>
                <input type="checkbox" name="allow_online_attachments" <?php echo $config['allow_online_attachments'] ?'checked':''; ?> >
                    <?php echo lang('allow_online_at'); ?><br/>&nbsp;&nbsp;&nbsp;&nbsp;
                <input type="checkbox" name="allow_online_attachments_onlogin" <?php echo $config['allow_online_attachments_onlogin'] ?'checked':''; ?> >
                    <?php echo lang('auth_users_only'); ?> (<i><?php echo lang('u_log_to_upload'); ?> </i>)
                    <font class="warn">&nbsp;<?php echo $warn['allow_online_attachments']; ?></font>
            </td>
          </tr>
          <tr>
            <th><?php echo lang('staff_response'); ?>:</th>
            <td>
                <input type="checkbox" name="email_attachments" <?php echo $config['email_attachments']?'checked':''; ?> ><?php echo lang('e_attachment_to_user'); ?>
            </td>
          </tr>
          <tr>
            <th nowrap><?php echo lang('max_file_size'); ?>:</th>
            <td>
              <input type="text" name="max_file_size" value="<?php echo $config['max_file_size']; ?>"> <i>bytes</i>
                <font class="error">&nbsp;<?php echo $errors['max_file_size']; ?></font>
            </td>
          </tr>
          <tr>
            <th><?php echo lang('attach_folder'); ?>:</th>
            <td>
                <?php echo lang('web_user_access'); ?> &nbsp;<font class="error">&nbsp;<?php echo $errors['upload_dir']; ?></font><br>
              <input type="text" size=60 name="upload_dir" value="<?php echo $config['upload_dir']; ?>"> 
              <font color=red>
              <?php echo $attwarn; ?>
              </font>
            </td>
          </tr>
          <tr>
            <th valign="top"><br/><?php echo lang('file_types_accept'); ?>:</th>
            <td>
                <?php echo lang('file_ext_s_by_c'); ?> <i>.doc, .pdf, </i> <br>
                <?php echo lang('acct_enter_wilc'); ?> <b><i>.*</i></b>&nbsp;&nbsp;i.e dotStar (<?php echo lang('not_recommended'); ?>).
                <textarea name="allowed_filetypes" cols="21" rows="4" style="width: 65%;" wrap=HARD ><?php echo $config['allowed_filetypes']; ?></textarea>
            </td>
          </tr>
        </table>
    </td></tr>
    <tr><td style="padding:10px 0 10px 200px">
        <input class="button" type="submit" name="submit" value="<?php echo lang('save_changes'); ?>">
        <input class="button" type="reset" name="reset" value="<?php echo lang('reset_changes'); ?>">
    </td></tr>
  </form>
</table>
