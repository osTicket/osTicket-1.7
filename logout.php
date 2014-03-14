<?php
/*********************************************************************
    logout.php

    Destroy clients session.

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/

require('client.inc.php');
//Check token: Make sure the user actually clicked on the link to logout.
if(!$_GET['auth'] || !$ost->validateLinkToken($_GET['auth']))
   @header('Location: index.php');

session_unset();
//session_destroy();
$_SESSION['_client']=array();
$_SESSION['_client']['token'] = null;
header('Location: index.php');
require('index.php');
?>
