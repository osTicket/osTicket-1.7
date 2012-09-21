<?php
if(!defined('OSTADMININC') || !$thisstaff || !$thisstaff->isAdmin() || !$config) die(_('Access Denied'));
?>
<h2><?= _('Knowledge Base Settings and Options')?></h2>
<form action="settings.php?t=kb" method="post" id="save">
<?php csrf_token(); ?>
<input type="hidden" name="t" value="kb" >
<table class="form_table settings_table" width="940" border="0" cellspacing="0" cellpadding="2">
    <thead>
        <tr>
            <th colspan="2">
                <h4><?= _('Knowledge Base Settings')?></h4>
                <em><?= _('Disabling knowledge base disables clients\' interface.')?></em>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td width="180"><?= _('Knowledge base status')?>:</td>
            <td>
              <input type="checkbox" name="enable_kb" value="1" <?php echo $config['enable_kb']?'checked="checked"':''; ?>>
              <?= _('Enable Knowledge base')?>&nbsp;<em><?= _('(Client interface)')?></em>
              &nbsp;<font class="error">&nbsp;<?php echo $errors['enable_kb']; ?></font>
            </td>
        </tr>
        <tr>
            <td width="180"><?= _('Canned Responses')?>:</td>
            <td>
                <input type="checkbox" name="enable_premade" value="1" <?php echo $config['enable_premade']?'checked="checked"':''; ?> >
                <?= _('Enable canned responses')?>&nbsp;<em><?= _('(Available on ticket reply)')?></em>
                &nbsp;<font class="error">&nbsp;<?php echo $errors['enable_premade']; ?></font>
            </td>
        </tr>
    </tbody>
</table>
<p style="padding-left:210px;">
    <input class="button" type="submit" name="submit" value="<?= _('Save Changes')?>">
    <input class="button" type="reset" name="reset" value="<?= _('Reset Changes')?>">
</p>
</form>
