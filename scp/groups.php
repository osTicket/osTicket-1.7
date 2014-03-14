<?php
/*********************************************************************
    groups.php

    User Groups.

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require('admin.inc.php');
require_once(INCLUDE_DIR.'languages/language_control/languages_processor.php');

$group=null;
if($_REQUEST['id'] && !($group=Group::lookup($_REQUEST['id'])))
    $errors['err']=lang('unknow_group').' '.lang('id').'.';

if($_POST){
    switch(strtolower($_POST['do'])){
        case 'update':
            if(!$group){
                $errors['err']=lang('unknow_group').'.';
            }elseif($group->update($_POST,$errors)){
                $msg=lang('group_update_success');
            }elseif(!$errors['err']){
                $errors['err']=lang('unable_update_group').'.'.lang('correct_errors').'!';
            }
            break;
        case 'create':
            if(($id=Group::create($_POST,$errors))){
                $msg=Format::htmlchars($_POST['name']).' '.lang('added_succesfully');
                $_REQUEST['a']=null;
            }elseif(!$errors['err']){
                $errors['err']=lang('unable_add_group').' '.lang('correct_errors').'!';
            }
            break;
        case 'mass_process':
            if(!$_POST['ids'] || !is_array($_POST['ids']) || !count($_POST['ids'])) {
                $errors['err'] = lang('select_at_least_group').'.';
            } elseif(in_array($thisstaff->getGroupId(), $_POST['ids'])) {
                $errors['err'] = lang('cant_edit_group')."!";
            } else {
                $count=count($_POST['ids']);
                switch(strtolower($_POST['a'])) {
                    case 'enable':
                        $sql='UPDATE '.GROUP_TABLE.' SET group_enabled=1, updated=NOW() '
                            .' WHERE group_id IN ('.implode(',', db_input($_POST['ids'])).')';

                        if(db_query($sql) && ($num=db_affected_rows())){
                            if($num==$count)
                                $msg = lang('groups_activated');
                            else
                                $warn = "$num ".lang('of')." $count ".lang('groups_activated');
                        } else {
                            $errors['err'] = lang('cant_activate_group');
                        }
                        break;
                    case 'disable':
                        $sql='UPDATE '.GROUP_TABLE.' SET group_enabled=0, updated=NOW() '
                            .' WHERE group_id IN ('.implode(',', db_input($_POST['ids'])).')';
                        if(db_query($sql) && ($num=db_affected_rows())) {
                            if($num==$count)
                                $msg = lang('group_disabled');
                            else
                                $warn = "$num ".lang('of')." $count ".lang('group_disabled');
                        } else {
                            $errors['err'] = lang('unable_disable_group');
                        }
                        break;
                    case 'delete':
                        foreach($_POST['ids'] as $k=>$v) {
                            if(($g=Group::lookup($v)) && $g->delete())
                                $i++;
                        }   

                        if($i && $i==$count)
                            $msg = lang('group_delete_success');
                        elseif($i>0)
                            $warn = "$i ".lang('of')." $count ".lang('group_deleted');
                        elseif(!$errors['err'])
                            $errors['err'] = lang('unable_delete_group');
                        break;
                    default:
                        $errors['err']  = lang('unknown_action_only').'!';
                }
            }
            break;
        default:
            $errors['err']=lang('unknown_action_only');
            break;
    }
}

$page='groups.inc.php';
if($group || ($_REQUEST['a'] && !strcasecmp($_REQUEST['a'],'add')))
    $page='group.inc.php';

$nav->setTabActive('staff');
require(STAFFINC_DIR.'header.inc.php');
require(STAFFINC_DIR.$page);
include(STAFFINC_DIR.'footer.inc.php');
?>
