<?php
/*********************************************************************
    class.mailer.php

    osTicket mailer

    It's mainly PEAR MAIL wrapper for now (more improvements planned).

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/

include_once(INCLUDE_DIR.'class.email.php');

class Mailer {

    var $email;

    var $ht = array();
    var $attachments = array();
    var $options = array();

    var $smtp = array();
    var $eol="\n";

    function Mailer($email=null, $options=array()) {
        global $cfg;

        if(is_object($email) && $email->isSMTPEnabled() && ($info=$email->getSMTPInfo())) { //is SMTP enabled for the current email?
            $this->smtp = $info;
        } elseif($cfg && ($e=$cfg->getDefaultSMTPEmail()) && $e->isSMTPEnabled()) { //What about global SMTP setting?
            $this->smtp = $e->getSMTPInfo();
            if(!$e->allowSpoofing() || !$email)
                $email = $e;
        } elseif(!$email && $cfg && ($e=$cfg->getDefaultEmail())) {
            if($e->isSMTPEnabled() && ($info=$email->getSMTPInfo()))
                $this->smtp = $info;
            $email = $e;
        }

        $this->email = $email;
        $this->attachments = array();
        $this->options = $options;
    }

    function getEOL() {
        return $this->eol;
    }

    function getEmail() {
        return $this->email;
    }

    function getSMTPInfo() {
        return $this->smtp;
    }
    /* FROM Address */
    function setFromAddress($from) {
        $this->ht['from'] = $from;
    }

    function getFromAddress() {

        if(!$this->ht['from'] && ($email=$this->getEmail()))
            $this->ht['from'] =sprintf('"%s" <%s>', ($email->getName()?$email->getName():$email->getEmail()), $email->getEmail());

        return $this->ht['from'];
    }

    /* attachments */
    function getAttachments() {
        return $this->attachments;
    }

    function addAttachment($attachment) {
        $this->attachments[] = $attachment;
    }

    function addAttachments($attachments) {
        $this->attachments = array_merge($this->attachments, $attachments);
    }

    function send($to, $subject, $message, $options=null) {
        global $ost;

        //Get the goodies
        require_once (PEAR_DIR.'Mail.php'); // PEAR Mail package
        require_once (PEAR_DIR.'Mail/mime.php'); // PEAR Mail_Mime packge

        //do some cleanup
        $to = preg_replace("/(\r\n|\r|\n)/s",'', trim($to));
        $subject = preg_replace("/(\r\n|\r|\n)/s",'', trim($subject));
        //We're decoding html entities here becasuse we only support plain text for now - html support comming.
        $body = Format::htmldecode(preg_replace("/(\r\n|\r)/s", "\n", trim($message)));

        /* Message ID - generated for each outgoing email */
        $messageId = sprintf('<%s%d-%s>', Misc::randCode(6), time(),
                ($this->getEmail()?$this->getEmail()->getEmail():'@osTicketMailer'));

        $headers = array (
                'From' => $this->getFromAddress(),
                'To' => $to,
                'Subject' => $subject,
                'Date'=> date('D, d M Y H:i:s O'),
                'Message-ID' => $messageId,
                'X-Mailer' =>'osTicket Mailer'
               );

        // Add in the options passed to the constructor
        if (!$options) $options = array();
        $options += $this->options;

        if (isset($options['nobounce']) && $options['nobounce'])
            $headers['Return-Path'] = '<>';
        elseif (is_a($this->getEmail(), 'Email'))
            $headers['Return-Path'] = $this->getEmail()->getEmail();

        //Bulk.
        if (isset($options['bulk']) && $options['bulk'])
            $headers+= array('Precedence' => 'bulk');

        //Auto-reply - mark as autoreply and supress all auto-replies
        if (isset($options['autoreply']) && $options['autoreply']) {
            $headers+= array(
                    'Precedence' => 'auto_reply',
                    'X-Autoreply' => 'yes',
                    'X-Auto-Response-Suppress' => 'DR, RN, OOF, AutoReply',
                    'Auto-Submitted' => 'auto-replied');
        }

        //Notice (sort of automated - but we don't want auto-replies back
        if (isset($options['notice']) && $options['notice'])
            $headers+= array(
                    'X-Auto-Response-Suppress' => 'OOF, AutoReply',
                    'Auto-Submitted' => 'auto-generated');

        if ($options) {
            if (isset($options['inreplyto']) && $options['inreplyto'])
                $headers += array('In-Reply-To' => $options['inreplyto']);
            if (isset($options['references']) && $options['references']) {
                if (is_array($options['references']))
                    $headers += array('References' =>
                        implode(' ', $options['references']));
                else
                    $headers += array('References' => $options['references']);
            }
        }

        // Use Mail_mime default initially
        $eol = null;

        // MAIL_EOL setting can be defined in `ost-config.php`
        if (defined('MAIL_EOL') && is_string(MAIL_EOL)) {
            $eol = MAIL_EOL;
        }
        // The Suhosin patch will muck up the line endings in some
        // cases
        //
        // References:
        // https://github.com/osTicket/osTicket-1.8/issues/202
        // http://pear.php.net/bugs/bug.php?id=12032
        // http://us2.php.net/manual/en/function.mail.php#97680
        elseif ((extension_loaded('suhosin') || defined("SUHOSIN_PATCH"))
            && !$this->getSMTPInfo()
        ) {
            $eol = "\n";
        }
        $mime = new Mail_mime($eol);

        $mime->setTXTBody($body);
        //XXX: Attachments
        if(($attachments=$this->getAttachments())) {
            foreach($attachments as $attachment) {
                if($attachment['file_id'] && ($file=AttachmentFile::lookup($attachment['file_id'])))
                    $mime->addAttachment($file->getData(),$file->getType(), $file->getName(),false);
                elseif($attachment['file'] &&  file_exists($attachment['file']) && is_readable($attachment['file']))
                    $mime->addAttachment($attachment['file'],$attachment['type'],$attachment['name']);
            }
        }

        //Desired encodings...
        $encodings=array(
                'head_encoding' => 'quoted-printable',
                'text_encoding' => 'base64',
                'html_encoding' => 'base64',
                'html_charset'  => 'utf-8',
                'text_charset'  => 'utf-8',
                'head_charset'  => 'utf-8'
                );
        //encode the body
        $body = $mime->get($encodings);
        //encode the headers.
        $headers = $mime->headers($headers, true);

        // Cache smtp connections made during this request
        static $smtp_connections = array();
        if(($smtp=$this->getSMTPInfo())) { //Send via SMTP
            $key = sprintf("%s:%s:%s", $smtp['host'], $smtp['port'],
                $smtp['username']);
            if (!isset($smtp_connections[$key])) {
                $mail = mail::factory('smtp', array(
                    'host' => $smtp['host'],
                    'port' => $smtp['port'],
                    'auth' => $smtp['auth'],
                    'username' => $smtp['username'],
                    'password' => $smtp['password'],
                    'timeout'  => 20,
                    'debug' => false,
                    'persist' => true,
                ));
                if ($mail->connect())
                    $smtp_connections[$key] = $mail;
            }
            else {
                // Use persistent connection
                $mail = $smtp_connections[$key];
            }

            $result = $mail->send($to, $headers, $body);
            if(!PEAR::isError($result))
                return $messageId;

            // Force reconnect on next ->send()
            unset($smtp_connections[$key]);

            $alert=sprintf("Unable to email via SMTP:%s:%d [%s]\n\n%s\n",
                    $smtp['host'], $smtp['port'], $smtp['username'], $result->getMessage());
            $this->logError($alert);
        }

        //No SMTP or it failed....use php's native mail function.
        $mail = mail::factory('mail');
        return PEAR::isError($mail->send($to, $headers, $body))?false:$messageId;

    }

    function logError($error) {
        global $ost;
        //NOTE: Admin alert override - don't email when having email trouble!
        $ost->logError('Mailer Error', $error, false);
    }

    /******* Static functions ************/

    //Emails using native php mail function - if DB connection doesn't exist.
    //Don't use this function if you can help it.
    function sendmail($to, $subject, $message, $from) {
        $mailer = new Mailer(null, array('notice'=>true, 'nobounce'=>true));
        $mailer->setFromAddress($from);
        return $mailer->send($to, $subject, $message);
    }
}
?>
