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
require('admin.inc.php');
require_once(INCLUDE_DIR.'languages/language_control/languages_processor.php');

$staff=null;
if($_REQUEST['id'] && !($staff=Staff::lookup($_REQUEST['id'])))
    $errors['err']=lang('unknow_staff_id');

if($_POST){
    switch(strtolower($_POST['do'])){
        case 'update':
            if(!$staff){
                $errors['err']=lang('unknow_staff');
            }elseif($staff->update($_POST,$errors)){
                $msg=lang('staff_update_success');
            }elseif(!$errors['err']){
                $errors['err']=lang('unable_update_staff');
            }
            break;
        case 'create':
            if(($id=Staff::create($_POST,$errors))){
                $msg=Format::htmlchars($_POST['name']).' '. lang('added_succesfully');
                $_REQUEST['a']=null;
            }elseif(!$errors['err']){
                $errors['err']=lang('unable_add_staff');
            }
            break;
        case 'mass_process':
            if(!$_POST['ids'] || !is_array($_POST['ids']) || !count($_POST['ids'])) {
                $errors['err'] = lang('one_staff_member');
            } elseif(in_array($thisstaff->getId(),$_POST['ids'])) {
                $errors['err'] = lang('disable_only_admin');
            } else {
                $count=count($_POST['ids']);
                switch(strtolower($_POST['a'])) {
                    case 'enable':
                        $sql='UPDATE '.STAFF_TABLE.' SET isactive=1 '
                            .' WHERE staff_id IN ('.implode(',', db_input($_POST['ids'])).')';

                        if(db_query($sql) && ($num=db_affected_rows())) {
                            if($num==$count)
                                $msg = lang('staff_activated');
                            else
                                $warn = "$num ".lang('of')." $count ".lang('staff_activated');
                        } else {
                            $errors['err'] = lang('unable_activ_staff');
                        }
                        break;
                    case 'disable':
                        $sql='UPDATE '.STAFF_TABLE.' SET isactive=0 '
                            .' WHERE staff_id IN ('.implode(',', db_input($_POST['ids'])).') AND staff_id!='.db_input($thisstaff->getId());

                        if(db_query($sql) && ($num=db_affected_rows())) {
                            if($num==$count)
                                $msg = lang('disable_staff');
                            else
                                $warn = "$num ".lang('of')." $count ".lang('disable_staff');
                        } else {
                            $errors['err'] = lang('unable_disable_staff');
                        }
                        break;
                    case 'delete':
                        foreach($_POST['ids'] as $k=>$v) {
                            if($v!=$thisstaff->getId() && ($s=Staff::lookup($v)) && $s->delete())
                                $i++;
                        }

                        if($i && $i==$count)
                            $msg = lang('staff_deleted');
                        elseif($i>0)
                            $warn = "$i ".lang('of')." $count ".lang('staff_deleted_only');
                        elseif(!$errors['err'])
                            $errors['err'] = lang('unable_delete_staff');
                        break;
                    default:
                        $errors['err'] = lang('unknown_action');
                }
                    
            }
            break;
        default:
            $errors['err']=lang('unknown_command');
            break;
    }
}

$page='staffmembers.inc.php';
if($staff || ($_REQUEST['a'] && !strcasecmp($_REQUEST['a'],'add')))
    $page='staff.inc.php';

$nav->setTabActive('staff');
require(STAFFINC_DIR.'header.inc.php');
require(STAFFINC_DIR.$page);
include(STAFFINC_DIR.'footer.inc.php');
?>
