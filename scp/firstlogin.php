<?php
/*********************************************************************
    staff.php

    Evertything about staff members.

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require('staff.inc.php');

$staff = $thisstaff;

if($_POST){
    $vars = $_POST;
    $vars['id'] = $staff->getId();
    $vars['username'] = $staff->getUserName();
    $vars['dept_id'] = $staff->getDeptId();
    $vars['group_id'] = $staff->getGroupId();
    $vars['isadmin'] = '0';
    $vars['isactive'] = '1';

    if($staff->update($vars,$errors)){
        $msg='Staff updated successfully';
    }elseif(!$errors['err']){
        $errors['err']='Unable to update staff. Correct any error(s) below and try again!';
    }
}

$page='firstlogin.php';
//if($staff || ($_REQUEST['a'] && !strcasecmp($_REQUEST['a'],'add')))
//    $page='staff.inc.php';

$nav->setTabActive('staff');
require(STAFFINC_DIR.'header.inc.php');
require(STAFFINC_DIR.$page);
include(STAFFINC_DIR.'footer.inc.php');
?>
