<?php
/*********************************************************************
    profile.php

    Staff's profile handle

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/

require_once('staff.inc.php');
$msg='';
$staff=Staff::lookup($thisstaff->getId());
if($_POST && $_POST['id']!=$thisstaff->getId()) { //Check dummy ID used on the form.
 $errors['err']=lang('internal_error').'. '.lang('action_denied');
} elseif(!$errors && $_POST) { //Handle post

    if(!$staff)
        $errors['err']=lang('unknow_staff');
    elseif($staff->updateProfile($_POST,$errors)){
        $msg=lang('profile_updated');
        $thisstaff->reload();
        $staff->reload();
        $_SESSION['TZ_OFFSET']=$thisstaff->getTZoffset();
        $_SESSION['TZ_DST']=$thisstaff->observeDaylight();
    }elseif(!$errors['err'])
        $errors['err']=lang('profile_upd_error').'. '.lang('try_correcting');
}

//Forced password Change.
if($thisstaff->forcePasswdChange() && !$errors['err'])
    $errors['err']=sprintf('<b>'.lang('hi').' %s</b> - '.lang('change_pass_to_cont'),$thisstaff->getFirstName());
elseif($thisstaff->onVacation() && !$warn)
    $warn=sprintf('<b>'.lang('welcome_back').' %s</b>! '.lang('listed_on_vacation'),$thisstaff->getFirstName());

$inc='profile.inc.php';
$nav->setTabActive('dashboard');
require_once(STAFFINC_DIR.'header.inc.php');
require(STAFFINC_DIR.$inc);
require_once(STAFFINC_DIR.'footer.inc.php');
?>
