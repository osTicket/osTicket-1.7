#!/usr/bin/php -q
<?php
/*********************************************************************
    pipe.php

    Converts piped emails to ticket. Both local and remote!

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2012 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
@chdir(realpath(dirname(__FILE__)).'/'); //Change dir.
ini_set('memory_limit', '256M'); //The concern here is having enough mem for emails with attachments.
$apikey = null;
require('api.inc.php');
require_once(INCLUDE_DIR.'class.mailparse.php');
require_once(INCLUDE_DIR.'class.email.php');

//Make sure piping is enabled!
if(!$cfg->isEmailPipingEnabled())
    api_exit(EX_UNAVAILABLE,_('Email piping not enabled - check MTA settings.'));
elseif($apikey && !$apikey->canCreateTickets()) //apikey is ONLY set on remote post - local post don't need a key (for now).
    api_exit(EX_NOPERM, _('API key not authorized'));

//Get the input
$data=isset($_SERVER['HTTP_HOST'])?file_get_contents('php://input'):file_get_contents('php://stdin');
if(empty($data)){
    api_exit(EX_NOINPUT,'No data');
}

//Parse the email.
$parser= new Mail_Parse($data);
if(!$parser->decode()){ //Decode...returns false on decoding errors
    api_exit(EX_DATAERR,_('Email parse failed [').$parser->getError()."]\n\n".$data);    
}



//Check from address. make sure it is not a banned address.
$fromlist = $parser->getFromAddressList();
//Check for parsing errors on FROM address.
if(!$fromlist || PEAR::isError($fromlist)){
    api_exit(EX_DATAERR,_('Invalid FROM address [').$fromlist?$fromlist->getMessage():''."]\n\n".$data);
}

$from=$fromlist[0]; //Default.
foreach($fromlist as $fromobj){
    if(!Validator::is_email($fromobj->mailbox.'@'.$fromobj->host))
        continue;
    $from=$fromobj;
    break;
}

//TO Address:Try to figure out the email associated with the message.
$tolist = $parser->getToAddressList();
foreach ($tolist as $toaddr){
    if(($emailId=Email::getIdByEmail($toaddr->mailbox.'@'.$toaddr->host))){
        //We've found target email.
        break;
    }
}
if(!$emailId && ($cclist=$parser->getCcAddressList())) {
    foreach ($cclist as $ccaddr){
        if(($emailId=Email::getIdByEmail($ccaddr->mailbox.'@'.$ccaddr->host))){
            break;
        }
    }
}
//TODO: Options to reject emails without a matching To address in db? May be it was Bcc? Current Policy: If you pipe, we accept policy

require_once(INCLUDE_DIR.'class.ticket.php'); //We now need this bad boy!

$var=array();
$deptId=0;
$name=trim($from->personal,'"');
if($from->comment && $from->comment[0])
    $name.=' ('.$from->comment[0].')';
$subj=utf8_encode($parser->getSubject());
if(!($body=Format::stripEmptyLines($parser->getBody())))
    $body=$subj?$subj:'(EMPTY)';

$var['mid']=$parser->getMessageId();
$var['email']=$from->mailbox.'@'.$from->host;
$var['name']=$name?utf8_encode($name):$var['email'];
$var['emailId']=$emailId?$emailId:$cfg->getDefaultEmailId();
$var['subject']=$subj?$subj:'[No Subject]';
$var['message']=utf8_encode(Format::stripEmptyLines($body));
$var['header']=$parser->getHeader();
$var['priorityId']=$cfg->useEmailPriority()?$parser->getPriority():0;

$ticket=null;
if(preg_match ("[[#][0-9]{1,10}]", $var['subject'], $regs)) {
    $extid=trim(preg_replace("/[^0-9]/", "", $regs[0]));
    if(!($ticket=Ticket::lookupByExtId($extid, $var['email'])) || strcasecmp($ticket->getEmail(), $var['email']))
       $ticket = null;
}        

$errors=array();
$msgid=0;
if($ticket) {
    //post message....postMessage does the cleanup.
    if(!($msgid=$ticket->postMessage($var['message'], 'Email',$var['mid'],$var['header'])))
        api_exit(EX_DATAERR, _('Unable to post message'));

} elseif(($ticket=Ticket::create($var, $errors, 'email'))) { // create new ticket.
    $msgid=$ticket->getLastMsgId();
} else { // failure....

    // report success on hard rejection
    if(isset($errors['errno']) && $errors['errno'] == 403)
        api_exit(EX_SUCCESS);

    // check if it's a bounce!
    if($var['header'] && TicketFilter::isAutoBounce($var['header'])) {
        $ost->logWarning(_('Bounced email'), $var['message'], false);
        api_exit(EX_SUCCESS); 
    }
    
    api_exit(EX_DATAERR, _('Ticket create Failed').' '.implode("\n",$errors)."\n\n");
}

//Ticket created...save attachments if enabled.
if($ticket && $cfg->allowEmailAttachments() && ($attachments=$parser->getAttachments())) {
    foreach($attachments as $attachment) {
        if($attachment['filename'] && $ost->isFileTypeAllowed($attachment['filename']))
            $ticket->saveAttachment(array('name' => $attachment['filename'], 'data' => $attachment['body']), $msgid, 'M');
    }
}
api_exit(EX_SUCCESS);
?>
