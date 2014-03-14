<?php
/*********************************************************************
    cron.php

    File to handle LOCAL cron job calls.

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
/*@chdir(realpath(dirname(__FILE__)).'/'); //Change dir.
require('api.inc.php');
require_once(INCLUDE_DIR.'languages/language_control/languages_processor.php');
*/
require('api.inc.php');
if (!osTicket::is_cli())
    die('cron.php only_support_local http -> api/tasks/cron');

@chdir(realpath(dirname(__FILE__)).'/'); //Change dir.
require_once(INCLUDE_DIR.'api.cron.php');
LocalCronApiController::call();
?>
