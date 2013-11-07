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
$errors=array();
$settingOptions=array(
                'system' => lang('system_settings'),
                'tickets' => lang('ticket_settings'),
                'emails' => lang('email_settings'),
                'kb' => lang('knowl_settings'),
                'autoresp' => lang('Auto_res_Settings'),
                'alerts' => lang('nottice_settings'));
//Handle a POST.
if($_POST && !$errors) {
    if($cfg && $cfg->updateSettings($_POST,$errors)) {
        $msg=Format::htmlchars($settingOptions[$_POST['t']]).' '.lang('update_successful');
    } elseif(!$errors['err']) {
        $errors['err']=lang('error_update_set');
    }
}

$target=($_REQUEST['t'] && $settingOptions[$_REQUEST['t']])?$_REQUEST['t']:'system';
$config=($errors && $_POST)?Format::input($_POST):Format::htmlchars($cfg->getConfigInfo());

$nav->setTabActive('settings', ('settings.php?t='.$target));
require_once(STAFFINC_DIR.'header.inc.php');
include_once(STAFFINC_DIR."settings-$target.inc.php");
include_once(STAFFINC_DIR.'footer.inc.php');
?>
