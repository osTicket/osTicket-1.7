<?php
/*********************************************************************
    settings.php

    Handles all admin settings.
    
    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require('admin.inc.php');
include_once(INCLUDE_DIR.'class.ldap.php');

$errors=array();
$settingOptions=array(
                'system' => 'System Settings',
                'tickets' => 'Ticket Settings and Options',
                'emails' => 'Email Settings',
                'kb' => 'Knowledgebase Settings',
                'autoresp' => 'Autoresponder Settings',
                'alerts' => 'Alerts and Notices Settings',
				'ldap' => 'LDAP Settings');
//Handle a POST.
if($_POST && !$errors) {
	if($_REQUEST['t']=='ldap'&&($_REQUEST['do']=='update'||$_REQUEST['do']=='create'||$_REQUEST['do']=='mass_process'))
	{
		$ldap_entry=null;
		if($_REQUEST['id'] && !($ldap_entry=LDAP::checkID($_REQUEST['id'])))
			$errors['err']='Unknown or invalid LDAP connectionID.';
		switch(strtolower($_POST['do'])){
			case 'update':
				if(!$ldap_entry){
					$errors['err']='Unknown or invalid LDAP connection.';
				}elseif(LDAP::update($_POST['id'],$_POST,$errors)){
					$msg='LDAP connection updated successfully';
				}elseif(!$errors['err']){
					$errors['err']='Error updating LDAP connection. Try again!';
				}
				break;
			case 'create':
				if(($id=LDAP::create($_POST,$errors))){
					$msg='LDAP connection added successfully';
					$_REQUEST['a']=null;
				}elseif(!$errors['err']){
					$errors['err']='Unable to add LDAP connection. Correct error(s) below and try again.';
				}
				break;
			case 'mass_process':
				if(!$_POST['ids'] || !is_array($_POST['ids']) || !count($_POST['ids'])) {
					$errors['err'] = 'You must select at least one LDAP connection';
				} else {
					$count=count($_POST['ids']);
					if(!strcasecmp($_POST['a'], 'delete')) {
						$i=0;
						foreach($_POST['ids'] as $k=>$v) {
							if(LDAP::delete($v))
								$i++;
						}

						if($i && $i==$count)
							$msg = 'Selected LDAP connections deleted successfully';
						elseif($i>0)
							$warn = sprintf('%1$d of %2$d selected LDAP connections deleted', $i, $count);
						elseif(!$errors['err'])
							$errors['err'] = 'Unable to delete selected LDAP connections';
						
					} else {
						$errors['err'] = 'Unknown action - get technical help';
					}
				}
				break;
			default:
				$errors['err'] = 'Unknown action/command';
				break;
		}
	}
	else
	{
		if($cfg && $cfg->updateSettings($_POST,$errors)) {
			$msg=sprintf('%s updated successfully',Format::htmlchars($settingOptions[$_POST['t']]));
			$cfg->reload();
		} elseif(!$errors['err']) {
			$errors['err']='Unable to update settings - correct errors below and try again';
		}
	}
}

$target=($_REQUEST['t'] && $settingOptions[$_REQUEST['t']])?$_REQUEST['t']:'system';
$config=($errors && $_POST)?Format::input($_POST):Format::htmlchars($cfg->getConfigInfo());

$nav->setTabActive('settings', ('settings.php?t='.$target));

if($_REQUEST['t'] == 'ldap' && (($_REQUEST['a'] && !strcasecmp($_REQUEST['a'],'add'))||(!$_REQUEST['a'] && $_REQUEST['id'])))
    $target='ldap-entry';

require_once(STAFFINC_DIR.'header.inc.php');
include_once(STAFFINC_DIR."settings-$target.inc.php");
include_once(STAFFINC_DIR.'footer.inc.php');
?>
