<?php
require('admin.inc.php');
include_once(INCLUDE_DIR.'class.ldap.php');
include_once(INCLUDE_DIR.'class.csrf.php');
$info=array();
$info['subj']='osTicket test ldap';

if($_POST){
    $errors=array();
    if(!$_POST['ldap_id'] || !(LDAP::checkID($_POST['ldap_id'])))
        $errors['ldap_id']='Select LDAP entry';

    if(!$_POST['ldap_field'])
        $errors['ldap_field']='LDAP Field is required';

    if(!$errors){
		$outp="";
        if(LDAP::ldapGetField($_POST['ldap_id'],$_POST['ldap_field'],$_POST['ldap_user'],$outp,true)==false)
		{
            $errors['err']='LDAP Connection failed.';
		}
		else
		{
			$email=LDAP::ldapGetEmail($_POST['ldap_user'],$outp,true);
			LDAP::ldapGetUsernameFromEmail($email,$outp,true);
		}
    }elseif($errors['err']){
        $errors['err']='Error - check your LDAP Settings.';
    }
}
$info=Format::htmlchars(($errors && $_POST)?$_POST:$info);
$nav->setTabActive('settings', ('settings.php?t=ldap'));
require(STAFFINC_DIR.'header.inc.php');
?>
<form action="ldaptest.php" method="post" id="save">
 <?php csrf_token(); ?>
 <input type="hidden" name="do" value="<?php echo $action; ?>">
 <h2>Test LDAP Connection</h2>
 <table class="form_table" width="940" border="0" cellspacing="0" cellpadding="2">
    <tbody>
        <tr>
            <td width="120" class="required">
                LDAP Entry:
            </td>
            <td>
                <select name="ldap_id">
                    <option value="0">&mdash; Select a LDAP Entry &mdash;</option>
                    <?php
                    $sql='SELECT ldap_id,ldap_domain,ldap_controller FROM '.TABLE_PREFIX . 'ldap_config ORDER by ldap_id';
                    if(($res=db_query($sql)) && db_num_rows($res)){
                        while(list($id,$domain,$controller)=db_fetch_row($res)){
                            $selected=($info['ldap_id'] && $id==$info['ldap_id'])?'selected="selected"':'';

                            echo sprintf('<option value="%d" %s>%s</option>',$id,$selected,$domain.' '.$controller);
                        }
                    }
                    ?>
                </select>
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['ldap_id']; ?></span>
            </td>
        </tr>
        <tr>
            <td width="120" class="required">
                Field:
            </td>
            <td>
                <input type="text" size="60" name="ldap_field" value="<?php echo $info['ldap_field']; ?>">
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['ldap_field']; ?></span>
            </td>
        </tr>
        <tr>
            <td width="120">
                Username:
            </td>
            <td>
                <input type="text" size="60" name="ldap_user" value="<?php echo $info['ldap_user']; ?>">
                &nbsp;<span class="error">&nbsp;<?php echo $errors['ldap_user']; ?></span>
            </td>
        </tr>
        <tr>
            <td colspan=2>
                <em><strong>Result</strong>:</em>&nbsp;<span class="error">&nbsp;<?php echo $errors['ldap_result']; ?></span> Leave empty to use the Administrator in LDAP Settings<br>
                <?php echo $outp; ?>
            </td>
        </tr>
    </tbody>
</table>
<p style="padding-left:225px;">
    <input type="submit" name="submit" value="Test">
    <input type="reset"  name="reset"  value="Reset">
    <input type="button" name="cancel" value="Cancel" onclick='window.location.href="settings.php?t=ldap"'>
</p>
</form>
<?php
include(STAFFINC_DIR.'footer.inc.php');
?>
