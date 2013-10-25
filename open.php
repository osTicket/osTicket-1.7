<?php
/*********************************************************************
    open.php

    New tickets handle.

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require('client.inc.php');
require_once(INCLUDE_DIR.'class.ldap.php');
define('SOURCE','Web'); //Ticket source.
$inc='open.inc.php';    //default include.
$errors=array();
if(LDAP::ldapActive())
{
	if(LDAP::ldapClientForceLogin())
	{
		if(!$thisclient)
		{
			//XXX: Ticket owner is assumed.
			@header('Location: login.php');
			require_once('login.php'); //Just in case of 'header already sent' error.
			exit;
		}
	}
}
if($_POST):
    $vars = $_POST;
    // $vars['deptId']=$vars['emailId']=0; //Just Making sure we don't accept crap...only topicId is expected.
    if($thisclient) {
        $vars['name']=$thisclient->getName();
        $vars['email']=$thisclient->getEmail();
    } elseif($cfg->isCaptchaEnabled()) {
        if(!$_POST['captcha'])
            $errors['captcha']='Enter text shown on the image';
        elseif(strcmp($_SESSION['captcha'],md5($_POST['captcha'])))
            $errors['captcha']='Invalid - try again!';
    }

    if(!$errors && $cfg->allowOnlineAttachments() && $_FILES['attachments'])
        $vars['files'] = AttachmentFile::format($_FILES['attachments'], true);

    //Ticket::create...checks for errors..
    if(($ticket=Ticket::create($vars, $errors, SOURCE))){
        $msg='Support ticket request created';
        //Logged in...simply view the newly created ticket.
        if($thisclient && $thisclient->isValid()) {
			if(LDAP::ldapClientActive()==true)
			{
				$_SESSION['_client']['key']= $ticket->getExtId();
				$sqlquery='DELETE FROM ost_ticket WHERE email LIKE "'.$thisclient->getEmail().'" AND subject LIKE "ldap_temporary";';
				if(!db_query($sqlquery))
					$errors['err'] = 'Failed deleting a temporary ticket';
			}
            if(!$cfg->showRelatedTickets())
                $_SESSION['_client']['key']= $ticket->getExtId(); //Resetting login Key to the current ticket!
            session_write_close();
            session_regenerate_id();
            @header('Location: tickets.php?id='.$ticket->getExtId());
        }
        //Thank the user and promise speedy resolution!
        $inc='thankyou.inc.php';
    }else{
        $errors['err']=$errors['err']?$errors['err']:'Unable to create a ticket. Please correct errors below and try again!';
    }
endif;

//page
$nav->setActiveNav('new');
require(CLIENTINC_DIR.'header.inc.php');
require(CLIENTINC_DIR.$inc);
require(CLIENTINC_DIR.'footer.inc.php');
?>
