<?php
if(!defined('OSTADMININC') || !$thisstaff->isAdmin()) die(_('Access Denied'));
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
            <td colspan=2>&nbsp;<?= _('Attachments Settings') ?></td>
          </tr>
          <tr class="subheader">
            <td colspan=2">
                <?= _('Before enabling attachments make sure you understand the security settings and issues related to file uploads') ?>.</td>
          </tr>
          <tr>
            <th width="165"><?= _('Allow Attachments') ?>:</th>
            <td>
              <input type="checkbox" name="allow_attachments" <?php echo $config['allow_attachments'] ?'checked':''; ?>><b><?= _('Allow Attachments') ?></b>
                &nbsp; (<i><?= _('Global Setting') ?></i>)
                &nbsp;<font class="error">&nbsp;<?php echo $errors['allow_attachments']; ?></font>
            </td>
          </tr>
          <tr>
            <th><?= _('Emailed Attachments') ?>:</th>
            <td>
                <input type="checkbox" name="allow_email_attachments" <?php echo $config['allow_email_attachments'] ? 'checked':''; ?> ><?= _('Accept emailed files') ?>
                    &nbsp;<font class="warn">&nbsp;<?php echo $warn['allow_email_attachments']; ?></font>
            </td>
          </tr>
         <tr>
            <th><?= _('Online Attachments') ?>:</th>
            <td>
                <input type="checkbox" name="allow_online_attachments" <?php echo $config['allow_online_attachments'] ?'checked':''; ?> >
                   <?= _('Allow online attachments upload') ?><br/>&nbsp;&nbsp;&nbsp;&nbsp;
                <input type="checkbox" name="allow_online_attachments_onlogin" <?php echo $config['allow_online_attachments_onlogin'] ?'checked':''; ?> >
                   <?= _('Authenticated users Only.') ?> (<i><?= _('User must be logged in to upload files') ?> </i>)
                    <font class="warn">&nbsp;<?php echo $warn['allow_online_attachments']; ?></font>
            </td>
          </tr>
          <tr>
            <th><?= _('Staff Response Files') ?>:</th>
            <td>
                <input type="checkbox" name="email_attachments" <?php echo $config['email_attachments']?'checked':''; ?> ><?= _('Email attachments to the user') ?>
            </td>
          </tr>
          <tr>
            <th nowrap><?= _('Maximum File Size') ?>:</th>
            <td>
              <input type="text" name="max_file_size" value="<?php echo $config['max_file_size']; ?>"> <i>bytes</i>
                <font class="error">&nbsp;<?php echo $errors['max_file_size']; ?></font>
            </td>
          </tr>
          <tr>
            <th><?= _('Attachment Folder') ?>:</th>
            <td>
               <?php ('Web user (e.g apache) must have write access to the folder') ?>. &nbsp;<font class="error">&nbsp;<?php echo $errors['upload_dir']; ?></font><br>
              <input type="text" size=60 name="upload_dir" value="<?php echo $config['upload_dir']; ?>"> 
              <font color=red>
              <?php echo $attwarn; ?>
              </font>
            </td>
          </tr>
          <tr>
            <th valign="top"><br/><?= _('Accepted File Types') ?>:</th>
            <td>
               <?= _('Enter file extensions allowed separated by a comma. e.g') ?> <i>.doc, .pdf, </i> <br>
               <?= _(' To accept all files enter wildcard') ?> <b><i>.*</i></b>&nbsp;&nbsp;<?= _('i.e dotStar (NOT recommended).') ?>
                <textarea name="allowed_filetypes" cols="21" rows="4" style="width: 65%;" wrap=HARD ><?php echo $config['allowed_filetypes']; ?></textarea>
            </td>
          </tr>
        </table>
    </td></tr>
    <tr><td style="padding:10px 0 10px 200px">
        <input class="button" type="submit" name="submit" value="<?= _('Save Changes') ?>">
        <input class="button" type="reset" name="reset" value="<?= _('Reset Changes') ?>">
    </td></tr>
  </form>
</table>
