<?php
if(!defined('OSTADMININC') || !$thisstaff || !$thisstaff->isAdmin() || !$config) die(lang('access_denied'));
?>
<h2><?php echo lang("knowledge_set_opt"); ?> </h2>
<form action="settings.php?t=kb" method="post" id="save">
<?php csrf_token(); ?>
<input type="hidden" name="t" value="kb" >
<table class="form_table settings_table" width="940" border="0" cellspacing="0" cellpadding="2">
    <thead>
        <tr>
            <th colspan="2">
                <h4><?php echo lang("knowledge_settings"); ?> </h4>
                <em><?php echo lang("disabling_knowledge"); ?> .</em>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td width="180"><?php echo lang("knowledge_status"); ?> :</td>
            <td>
              <input type="checkbox" name="enable_kb" value="1" <?php echo $config['enable_kb']?'checked="checked"':''; ?>>
              <?php echo lang("enable"); ?>  <?php echo lang("knowledge_base"); ?> &nbsp;<em>(<?php echo lang('client_interface'); ?>)</em>
              &nbsp;<font class="error">&nbsp;<?php echo $errors['enable_kb']; ?></font>
            </td>
        </tr>
        <tr>
            <td width="180"><?php echo lang("canned_responses"); ?> :</td>
            <td>
                <input type="checkbox" name="enable_premade" value="1" <?php echo $config['enable_premade']?'checked="checked"':''; ?> >
                <?php echo lang("enable"); ?>  <?php echo lang("canned_responses"); ?>&nbsp;<em>(<?php echo lang("ticket_reply"); ?>)</em>
                &nbsp;<font class="error">&nbsp;<?php echo $errors['enable_premade']; ?></font>
            </td>
        </tr>
    </tbody>
</table>
<p style="padding-left:210px;">
    <input class="button" type="submit" name="submit" value="<?php echo lang("save_changes"); ?>">
    <input class="button" type="reset" name="reset" value="<?php echo lang("reset_changes"); ?>">
</p>
</form>
