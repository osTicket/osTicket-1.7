<?php
/*********************************************************************
    staff.php

    Evertything about staff members.

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2012 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require('admin.inc.php');
$staff=null;
if($_REQUEST['id'] && !($staff=Staff::lookup($_REQUEST['id'])))
    $errors['err']=_('Unknown or invalid staff ID.');

if($_POST){
    switch(strtolower($_POST['do'])){
        case 'update':
            if(!$staff){
                $errors['err']=_('Unknown or invalid staff.');
            }elseif($staff->update($_POST,$errors)){
                $msg=_('Staff updated successfully');
            }elseif(!$errors['err']){
                $errors['err']=_('Unable to update staff. Correct any error(s) below and try again!');
            }
            break;
        case 'create':
            if(($id=Staff::create($_POST,$errors))){
                $msg=Format::htmlchars($_POST['name']).' '._('added successfully');
                $_REQUEST['a']=null;
            }elseif(!$errors['err']){
                $errors['err']=_('Unable to add staff. Correct any error(s) below and try again.');
            }
            break;
        case 'mass_process':
            if(!$_POST['ids'] || !is_array($_POST['ids']) || !count($_POST['ids'])) {
                $errors['err'] = _('You must select at least one staff member.');
            } elseif(in_array($thisstaff->getId(),$_POST['ids'])) {
                $errors['err'] = _('You can not disable/delete yourself - you could be the only admin!');
            } else {
                $count=count($_POST['ids']);
                switch(strtolower($_POST['a'])) {
                    case 'enable':
                        $sql='UPDATE '.STAFF_TABLE.' SET isactive=1 '
                            .' WHERE staff_id IN ('.implode(',', db_input($_POST['ids'])).')';

                        if(db_query($sql) && ($num=db_affected_rows())) {
                            if($num==$count)
                                $msg = _('Selected staff activated');
                            else
                                $warn = "$num "._("of")." $count "._("selected staff activated");
                        } else {
                            $errors['err'] = _('Unable to activate selected staff');
                        }
                        break;
                    case 'disable':
                        $sql='UPDATE '.STAFF_TABLE.' SET isactive=0 '
                            .' WHERE staff_id IN ('.implode(',', db_input($_POST['ids'])).') AND staff_id!='.db_input($thisstaff->getId());

                        if(db_query($sql) && ($num=db_affected_rows())) {
                            if($num==$count)
                                $msg = _('Selected staff disabled');
                            else
                                $warn = "$num "._("of")." $count "._("selected staff disabled");
                        } else {
                            $errors['err'] = _('Unable to disable selected staff');
                        }
                        break;
                    case 'delete':
                        foreach($_POST['ids'] as $k=>$v) {
                            if($v!=$thisstaff->getId() && ($s=Staff::lookup($v)) && $s->delete())
                                $i++;
                        }

                        if($i && $i==$count)
                            $msg = _('Selected staff deleted successfully');
                        elseif($i>0)
                            $warn = "$i "._("of")." $count "._("selected staff deleted");
                        elseif(!$errors['err'])
                            $errors['err'] = _('Unable to delete selected staff.');
                        break;
                    default:
                        $errors['err'] = _('Unknown action. Get technical help!');
                }
                    
            }
            break;
        default:
            $errors['err']=_('Unknown action/command');
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
