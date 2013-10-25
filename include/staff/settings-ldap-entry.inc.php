<?php
if(!defined('OSTADMININC') || !$thisstaff || !$thisstaff->isAdmin()) die('Access Denied');
$info=array();
$qstr='t='.$_REQUEST['t'];
if($_REQUEST['t']=='ldap' && $_REQUEST['a']!='add'){
    $title='Edit LDAP Connection';
    $action='update';
    $submit_text='Save Changes';
	$sql='SELECT * FROM ' . TABLE_PREFIX . 'ldap_config WHERE ldap_id='.$_REQUEST['id'];
	if(($res=db_query($sql)) && db_num_rows($res))
	{
		$info=db_fetch_array($res);
		$info['id']=$info['ldap_id'];
	}
    if($info['ldap_admin_pw'])
        $passwdtxt='To change password enter new password above.';
    $qstr.='&id='.$_REQUEST['id'];
}else {
    $title='Add New LDAP Connection';
    $action='create';
    $submit_text='Add';
    $qstr.='&a='.$_REQUEST['a'];
}
$info=Format::htmlchars(($errors && $_POST)?$_POST:$info);
?>
<h2>LDAP Connection</h2>
<form action="settings.php?<?php echo $qstr; ?>" method="post" id="save">
 <?php csrf_token(); ?>
 <input type="hidden" name="do" value="<?php echo $action; ?>">
 <input type="hidden" name="a" value="<?php echo Format::htmlchars($_REQUEST['a']); ?>">
 <input type="hidden" name="id" value="<?php echo $info['id']; ?>">
 <table class="form_table" width="940" border="0" cellspacing="0" cellpadding="2">
    <thead>
        <tr>
            <th colspan="2">
                <h4><?php echo $title; ?></h4>
                <em><strong>LDAP Connection</strong>:</em>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td width="180" class="required">
                LDAP Domain
            </td>
            <td>
                <input type="text" size="35" name="ldap_domain" value="<?php echo $info['ldap_domain']; ?>"> Example entry: "DC=company,DC=com"
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['ldap_domain']; ?></span>
            </td>
        </tr>
        <tr>
            <td width="180" class="required">
                LDAP Suffix
            </td>
            <td>
                <input type="text" size="35" name="ldap_suffix" value="<?php echo $info['ldap_suffix']; ?>"> Needed to create username@domain. Example entry: "@company.com"
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['ldap_suffix']; ?>&nbsp;</span>
            </td>
        </tr>
        <tr>
            <td width="180" class="required">
                LDAP Filter
            </td>
            <td>
                <input type="text" size="35" name="ldap_filter" value="<?php echo $info['ldap_filter']; ?>"> Needed to filter out the ldap results based on the user. The %USERNAME% will be replaced with the actual username. Example entry: "(&(sAMAccountName=%USERNAME%))" NOTE: it's caps sensitive.
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['ldap_filter']; ?>&nbsp;</span>
            </td>
        </tr>
        <tr>
            <td width="180" class="required">
                LDAP Controller
            </td>
            <td>
                <input type="text" size="35" name="ldap_controller" value="<?php echo $info['ldap_controller']; ?>"> Your LDAP Host/Server. Enter either FQDN or IP.
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['ldap_controller']; ?>&nbsp;</span>
            </td>
        </tr>
        <tr>
            <td width="180" class="required">
                LDAP Port
            </td>
            <td>
                <input type="text" size="35" name="ldap_port" value="<?php echo $info['ldap_port']; ?>"> Your LDAP Port on your Host/Server. Usually 389, SSL Port is 636
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['ldap_port']; ?>&nbsp;</span>
            </td>
        </tr>
        <tr><td>LDAPS (SSL)</td>
            <td>
                <label><input type="radio" name="ldap_ssl"  value="1"   <?php echo $info['ldap_ssl']?'checked="checked"':''; ?> /><strong>Enable</strong></label>
                &nbsp;&nbsp;
                <label><input type="radio" name="ldap_ssl"  value="0"   <?php echo !$info['ldap_ssl']?'checked="checked"':''; ?> />Disable</label>
                &nbsp;<font class="error">&nbsp;<?php echo $errors['ldap_ssl']; ?></font>
            </td>
        </tr>
        <tr>
            <td width="180">
                RDN Scheme
            </td>
            <td>
                <input type="text" size="35" name="ldap_rdn" value="<?php echo $info['ldap_rdn']; ?>"> 
                &nbsp;<span class="error">&nbsp;<?php echo $errors['ldap_rdn']; ?>&nbsp;</span>
				<br><em>Needed if you want to use RDN binding. Example entries: "<b>uid=%UID%,cn=%CN%,dc=my,dc=domain</b>" or "<b>cn=%CN%,cn=Users,dc=my,dc=domain</b>" NOTE: it's caps sensitive.</em>
            </td>
        </tr>
        <tr><td>Use RDN Binding</td>
            <td>
                <label><input type="radio" name="ldap_use_rdn"  value="1"   <?php echo $info['ldap_use_rdn']?'checked="checked"':''; ?> /><strong>Enable</strong></label>
                &nbsp;&nbsp;
                <label><input type="radio" name="ldap_use_rdn"  value="0"   <?php echo !$info['ldap_use_rdn']?'checked="checked"':''; ?> />Disable</label>
                &nbsp;<font class="error">&nbsp;<?php echo $errors['ldap_use_rdn']; ?></font>
				<br><em>Use RDN (uid=admin,cn=users,dc=my,dc=domain) style binding instead of username@domain.com</em>
            </td>
        </tr>
        <tr>
            <td width="180" class="required">
                LDAP Admin
            </td>
            <td>
                <input type="text" size="35" name="ldap_admin" value="<?php echo $info['ldap_admin']; ?>"> Enter an Admin-User with full LDAP access.
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['ldap_admin']; ?>&nbsp;</span>
            </td>
        </tr>
        <tr>
            <td width="180">
                LDAP Admins CN
            </td>
            <td>
                <input type="text" size="35" name="ldap_admin_cn" value="<?php echo $info['ldap_admin_cn']; ?>"> Enter the CN content of the Admin-User entered above. Used only if RDN binding is active.
                &nbsp;<span class="error">&nbsp;<?php echo $errors['ldap_admin_cn']; ?>&nbsp;</span>
            </td>
        </tr>
        <tr>
            <td width="180">
                LDAP Admin Password
            </td>
            <td>
                <input type="password" size="35" name="ldap_admin_pw" value="">
                &nbsp;<span class="error">&nbsp;<?php echo $errors['ldap_admin_pw']; ?>&nbsp;</span>
                <br><em><?php echo $passwdtxt; ?></em>
            </td>
        </tr>
        <tr>
            <th colspan="2">
                <em><strong>LDAP Settings</strong>: &nbsp;<font class="error">&nbsp;<?php echo $errors['ldap_id']; ?></font></em>
            </th>
        </tr>
        <tr><td>Status</td>
            <td>
                <label><input type="radio" name="ldap_active"  value="1"   <?php echo $info['ldap_active']?'checked="checked"':''; ?> /><strong>Enable</strong></label>
                &nbsp;&nbsp;
                <label><input type="radio" name="ldap_active"  value="0"   <?php echo !$info['ldap_active']?'checked="checked"':''; ?> />Disable</label>
                &nbsp;<font class="error">&nbsp;<?php echo $errors['ldap_active']; ?></font>
            </td>
        </tr>
        <tr><td class="required">LDAP Email Field</td>
            <td><input type="text" name="ldap_email_field" size=35 value="<?php echo $info['ldap_email_field']; ?>"> The email field name in ldap. Typically "mail"
                &nbsp;<font class="error">*&nbsp;<?php echo $errors['ldap_email_field']; ?></font>
            </td>
        </tr>
        <tr><td class="required">LDAP First Name Field</td>
            <td><input type="text" name="ldap_firstname_field" size=35 value="<?php echo $info['ldap_firstname_field']; ?>"> The firstname field in ldap. Typically "givenname"
                &nbsp;<font class="error">*&nbsp;<?php echo $errors['ldap_firstname_field']; ?></font>
            </td>
        </tr>
        <tr><td class="required">LDAP Last Name Field</td>
            <td><input type="text" name="ldap_lastname_field" size=35 value="<?php echo $info['ldap_lastname_field']; ?>"> The lastname field in ldap. Typically "sn"
                &nbsp;<font class="error">*&nbsp;<?php echo $errors['ldap_lastname_field']; ?></font>
            </td>
        </tr>
		<tr><td class="required">LDAP User Field</td>
            <td><input type="text" name="ldap_user_field" size=35 value="<?php echo $info['ldap_user_field']; ?>"> The user field in ldap. Typically "samaccountname" (caps sensitive)
                &nbsp;<font class="error">*&nbsp;<?php echo $errors['ldap_user_field']; ?></font>
            </td>
        </tr>
		<tr><td>PHP Server Auth Variable</td>
            <td><input type="text" name="ldap_auth_var" size=35 value="<?php echo $info['ldap_auth_var']; ?>"> The Auth Variable that your webserver fills. Examples: 'PHP_AUTH_USER' or 'AUTH_USER'
                &nbsp;<font class="error">&nbsp;<?php echo $errors['ldap_auth_var']; ?></font>
            </td>
        </tr>
        <tr><td class="required">LDAP Phone Field</td>
            <td><input type="text" name="ldap_phone_field" size=35 value="<?php echo $info['ldap_phone_field']; ?>"> The phone field in ldap. Typically "telephonenumber"
                &nbsp;<font class="error">*&nbsp;<?php echo $errors['ldap_phone_field']; ?></font>
            </td>
        </tr>
        <tr><td>LDAP Phone Ext Length</td>
            <td><input type="text" name="ldap_ext_length" size=6 value="<?php echo $info['ldap_ext_length']; ?>"> Enter the length of your phone ext here. LDAP only provides the full phone number+ext. It has to be cut to size.
                &nbsp;<font class="error">&nbsp;<?php echo $errors['ldap_ext_length']; ?></font>
            </td>
        </tr>
        <tr><td class="required">LDAP Connection Priority</td>
            <td><input type="text" name="priority" size=6 value="<?php echo $info['priority']; ?>"> Enter the priority of this connenction. 1=highest/first 99=lowest/last
                &nbsp;<font class="error">*&nbsp;<?php echo $errors['priority']; ?></font>
            </td>
        </tr>
        <tr><td>Use SSO</td>
            <td>
                <label><input type="radio" name="ldap_use_sso"  value="1"   <?php echo $info['ldap_use_sso']?'checked="checked"':''; ?> /><strong>Enable</strong></label>
                &nbsp;&nbsp;
                <label><input type="radio" name="ldap_use_sso"  value="0"   <?php echo !$info['ldap_use_sso']?'checked="checked"':''; ?> />Disable</label>
                &nbsp;<font class="error">&nbsp;<?php echo $errors['ldap_use_sso']; ?></font>
            </td>
        </tr>
        <tr><td>LDAP for Clientaccess</td>
            <td>
                <label><input type="radio" name="ldap_client_active"  value="1"   <?php echo $info['ldap_client_active']?'checked="checked"':''; ?> /><strong>Enable</strong></label>
                &nbsp;&nbsp;
                <label><input type="radio" name="ldap_client_active"  value="0"   <?php echo !$info['ldap_client_active']?'checked="checked"':''; ?> />Disable</label>
                &nbsp;<font class="error">&nbsp;<?php echo $errors['ldap_client_active']; ?></font>
            </td>
        </tr>
        <tr><td>Autofill for Clients</td>
            <td>
                <label><input type="radio" name="ldap_client_autofill"  value="1"   <?php echo $info['ldap_client_autofill']?'checked="checked"':''; ?> /><strong>Enable</strong></label>
                &nbsp;&nbsp;
                <label><input type="radio" name="ldap_client_autofill"  value="0"   <?php echo !$info['ldap_client_autofill']?'checked="checked"':''; ?> />Disable</label>
				<b>LDAP Clientaccess must be active</b>
                &nbsp;<font class="error">&nbsp;<?php echo $errors['ldap_client_autofill']; ?></font>
            </td>
        </tr>
        <tr><td>Force Clients to Log In</td>
            <td>
                <label><input type="radio" name="ldap_client_forcelogin"  value="1"   <?php echo $info['ldap_client_forcelogin']?'checked="checked"':''; ?> /><strong>Enable</strong></label>
                &nbsp;&nbsp;
                <label><input type="radio" name="ldap_client_forcelogin"  value="0"   <?php echo !$info['ldap_client_forcelogin']?'checked="checked"':''; ?> />Disable</label>
                &nbsp;<font class="error">&nbsp;<?php echo $errors['ldap_client_forcelogin']; ?></font>
            </td>
        </tr>
    </tbody>
</table>
<p style="padding-left:225px;">
    <input type="submit" name="submit" value="<?php echo $submit_text; ?>">
    <input type="reset"  name="reset"  value="Reset">
    <input type="button" name="cancel" value="Cancel" onclick='window.location.href="settings.php?t=ldap"'>
</p>
</form>
