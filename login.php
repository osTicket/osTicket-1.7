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

// Check the CSRF token, and ensure that future requests will have to use a
// different CSRF token. This will help ward off both parallel and serial
// brute force attacks, because new tokens will have to be requested for
// each attempt.
if($_POST) {
    // Check CSRF token
    if (!$ost->checkCSRFToken())
        Http::response(400, __('Valid CSRF Token Required'));

    // Rotate the CSRF token (original cannot be reused)
    $ost->getCSRF()->rotate();

    if(($user=Client::login(trim($_POST['lticket']), trim($_POST['lemail']), null, $errors))) {
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
