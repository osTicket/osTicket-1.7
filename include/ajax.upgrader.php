<?php
/*********************************************************************
    ajax.upgrader.php

    AJAX interface for Upgrader

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/

if(!defined('INCLUDE_DIR')) die('403');
require_once INCLUDE_DIR.'class.upgrader.php';
require_once(INCLUDE_DIR.'languages/language_control/languages_processor.php');

class UpgraderAjaxAPI extends AjaxController {

    function upgrade() {
        global $thisstaff, $ost;

        if(!$thisstaff or !$thisstaff->isAdmin() or !$ost)
            Http::response(403, lang('access_denied'));

        $upgrader = new Upgrader($ost->getDBSignature(), TABLE_PREFIX, SQL_DIR);

        //Just report the next action on the first call.
        if(!$_SESSION['ost_upgrader'] || !$_SESSION['ost_upgrader'][$upgrader->getShash()]['progress']) {
            $_SESSION['ost_upgrader'][$upgrader->getShash()]['progress'] = $upgrader->getNextAction();
            Http::response(200, $upgrader->getNextAction());
            exit;
        }

        if($upgrader->isAborted()) {
            Http::response(416, lang("have_problem"));
            exit;
        }

        if($upgrader->getNumPendingTasks() && $upgrader->doTasks()) {
            //More pending tasks - doTasks returns the number of pending tasks
            Http::response(200, $upgrader->getNextAction());
            exit;
        } elseif($ost->isUpgradePending()) {
            if($upgrader->isUpgradable()) {
                $version = $upgrader->getNextVersion();
                if($upgrader->upgrade()) {
                    //We're simply reporting progress here - call back will report next action'
                    Http::response(200, lang("upgrade_to")." $version ... ".lang("post_upgrade"));
                    exit;
                }
            } else { 
                //Abort: Upgrade pending but NOT upgradable - invalid or wrong hash.
                $upgrader->abort(sprintf(lang('upgrade_failed').' [%s]',$ost->getDBSignature()));
            }
        } elseif(!$ost->isUpgradePending()) {
            $upgrader->setState('done');
            session_write_close();
            Http::response(201, lang("we_are_done"));
            exit;
        }

        if($upgrader->isAborted() || $upgrader->getErrors()) {
            Http::response(416, lang("have_problem"));
            exit;
        }

        Http::response(200, $upgrader->getNextAction());
    }
}
?>
