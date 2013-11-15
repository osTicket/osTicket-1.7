<?php
/*********************************************************************
    login.php

    Client Login 

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require_once('client.inc.php');
if(!defined('INCLUDE_DIR')) die('Fatal Error');
define('CLIENTINC_DIR',INCLUDE_DIR.'client/');
define('OSTCLIENTINC',TRUE); //make includes happy

require_once(INCLUDE_DIR.'class.client.php');
require_once(INCLUDE_DIR.'class.ticket.php');
require_once(INCLUDE_DIR.'class.ldap.php');

if($_POST||(LDAP::ldapClientActive()&&LDAP::useSSO()&&(isset($_SERVER[LDAP::ldapGetAuthvar()])&&$_SERVER[LDAP::ldapGetAuthvar()]!=""))) {
	$tmp_user=trim($_POST['lemail']);
	$tmp_pw=trim($_POST['lticket']);
	if(LDAP::ldapClientActive()==true)
	{
		$ldap_useSSO=LDAP::useSSO();
		if(LDAP::ldapAuthenticate($tmp_user,$tmp_pw)||$ldap_useSSO&&(isset($_SERVER[LDAP::ldapGetAuthvar()])&&$_SERVER[LDAP::ldapGetAuthvar()]!=""))
		{
			$tmp_email="";
			//check if auth var contains a username or an email address
			if(LDAP::useSSO()&&(isset($_SERVER[LDAP::ldapGetAuthvar()])&&$_SERVER[LDAP::ldapGetAuthvar()]!=""))
			{
				//check if authvar contains a backslash and remove the domain\ part
				$authvar=$_SERVER[LDAP::ldapGetAuthvar()];
				if(strpos($authvar, '\\')!==false)
				{
					$tmpvar=explode("\\", $authvar);
					$authvar=$tmpvar[1];
				}
				$tmp_email=LDAP::ldapGetEmail($authvar);
				if($tmp_email=="")//if tmp_email is empty at this point the auth var probably contains an email eddress, lets just assume that....
				{
					$tmp_email=strtolower($authvar);
				}
			}
			else
			{
				$tmp_email=strtolower(LDAP::ldapGetEmail($tmp_user));
			}
			$sqlquery='SELECT '. TABLE_PREFIX . 'ticket.ticket_id, ' . TABLE_PREFIX . 'ticket.ticketID, ' . TABLE_PREFIX . 'ticket.email from ' . TABLE_PREFIX . 'ticket WHERE email LIKE "' . $tmp_email .'";';
			if(($tmp_res=db_query($sqlquery)) && db_num_rows($tmp_res)>0)
			{
				$tmp_ht=db_fetch_array($tmp_res);
				$tmp_pw=trim($tmp_ht['ticketID']);
				$tmp_user=$tmp_ht['email'];
				if($ldap_useSSO)
				{
					$user=Client::login($tmp_pw, $tmp_user, md5($tmp_ht['ticket_id'].$tmp_user. SECRET_SALT), $errors);
				}
				else
				{
					$user=Client::login($tmp_pw, $tmp_user, null, $errors);
				}
				if($user) {
					if(LDAP::getTemporaryTicketNum($tmp_user)>0)
					{
						@header('Location: open.php');
						require_once('open.php'); //Just in case of 'header already sent' error.
						exit;
					}
					else
					{
						//XXX: Ticket owner is assumed.
						@header('Location: tickets.php');
						require_once('tickets.php'); //Just in case of 'header already sent' error.
						exit;
					}
				} elseif(!$errors['err']) {
					$errors['err'] = 'Authentication error - try again!';
				}
			}
			else
			{
				if(LDAP::ldapClientAutofill()==true)
				{
					$tmp_ticketID=Ticket::genExtRandID();
					if($ldap_useSSO)
					{
						$tmp_user=LDAP::ldapGetUsernameFromEmail($tmp_email);
					}
					$sqlquery='INSERT INTO '.TICKET_TABLE.' SET ticketID='.$tmp_ticketID.', dept_id=1, sla_id=1, priority_id=1, topic_id=1, staff_id=0, team_id=0, email="'. $tmp_email.'", name="'.LDAP::ldapGetName($tmp_user).'"';
					$sqlquery.=', subject="ldap_temporary", phone="'.LDAP::ldapGetPhone($tmp_user).'", phone_ext="'.LDAP::ldapGetPhoneExt($tmp_user).'", status="closed", source="Other"';
					if(!db_query($sqlquery))
						$errors['err'] = 'Failed creating a temporary ticket';
					if($ldap_useSSO)
					{
						$sqlquery='SELECT '. TABLE_PREFIX . 'ticket.ticket_id from ' . TABLE_PREFIX . 'ticket WHERE email LIKE "' . $tmp_email .'";';
						if(($tmp_res=db_query($sqlquery)) && db_num_rows($tmp_res)>0)
						{
							$tmp_ht=db_fetch_array($tmp_res);
							if(($user=Client::login($tmp_ticketID, $tmp_email, md5($tmp_ht['ticket_id'].$tmp_email. SECRET_SALT), $errors))) {
								//XXX: Ticket owner is assumed.
								@header('Location: open.php');
								require_once('open.php'); //Just in case of 'header already sent' error.
								exit;
							} elseif(!$errors['err']) {
								$errors['err'] = 'Authentication error - try again!';
							}
						}
					}
					else
					{
						if(($user=Client::login($tmp_ticketID, $tmp_email, null, $errors))) {
							//XXX: Ticket owner is assumed.
							@header('Location: open.php');
							require_once('open.php'); //Just in case of 'header already sent' error.
							exit;
						} elseif(!$errors['err']) {
							$errors['err'] = 'Authentication error - try again!';
						}
					}
				}
				else
				{
					@header('Location: open.php');
					require_once('open.php'); //Just in case of 'header already sent' error.
					exit;
				}
			}
		}
	}
	if(($user=Client::login($tmp_pw/*trim($_POST['lticket'])*/, $tmp_user/*trim($_POST['lemail'])*/, null, $errors))) {
		//XXX: Ticket owner is assumed.
		@header('Location: tickets.php?id='.$user->getTicketID());
		require_once('tickets.php'); //Just in case of 'header already sent' error.
		exit;
	} elseif(!$errors['err']) {
		$errors['err'] = 'Authentication error - try again!';
	}
}

$nav = new UserNav();
$nav->setActiveNav('status');
require(CLIENTINC_DIR.'header.inc.php');
require(CLIENTINC_DIR.'login.inc.php');
require(CLIENTINC_DIR.'footer.inc.php');
?>
