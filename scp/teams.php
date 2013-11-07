<?php
/*********************************************************************
    teams.php

    Evertything about teams

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require('admin.inc.php');
require_once(INCLUDE_DIR.'languages/language_control/languages_processor.php');
$team=null;
if($_REQUEST['id'] && !($team=Team::lookup($_REQUEST['id'])))
    $errors['err']=lang('invalid_team_id');

if($_POST){
    switch(strtolower($_POST['do'])){
        case 'update':
            if(!$team){
                $errors['err']=lang('invalid_team');
            }elseif($team->update($_POST,$errors)){
                $msg=lang('team_update_success');
            }elseif(!$errors['err']){
                $errors['err']=lang('unable_update_team');
            }
            break;
        case 'create':
            if(($id=Team::create($_POST,$errors))){
                $msg=Format::htmlchars($_POST['team']).' '.lang('added_succesfully');
                $_REQUEST['a']=null;
            }elseif(!$errors['err']){
                $errors['err']=lang('unable_add_team').' '.lang('correct_errors');
            }
            break;
        case 'mass_process':
            if(!$_POST['ids'] || !is_array($_POST['ids']) || !count($_POST['ids'])) {
                $errors['err']=lang('select_one_team');
            } else {
                $count=count($_POST['ids']);
                switch(strtolower($_POST['a'])) {
                    case 'enable':
                        $sql='UPDATE '.TEAM_TABLE.' SET isenabled=1 '
                            .' WHERE team_id IN ('.implode(',', db_input($_POST['ids'])).')';

                        if(db_query($sql) && ($num=db_affected_rows())) {
                            if($num==$count)
                                $msg = lang('team_activated');
                            else
                                $warn = "$num of $count ".lang('team_activated');
                        } else {
                            $errors['err'] = lang('unable_activate_team');
                        }
                        break;
                    case 'disable':
                        $sql='UPDATE '.TEAM_TABLE.' SET isenabled=0 '
                            .' WHERE team_id IN ('.implode(',', db_input($_POST['ids'])).')';

                        if(db_query($sql) && ($num=db_affected_rows())) {
                            if($num==$count)
                                $msg = lang('teams_disabled');
                            else
                                $warn = "$num of $count ".lang('teams_disabled');
                        } else {
                            $errors['err'] = lang('not_desable_teams');
                        }
                        break;
                    case 'delete':
                        foreach($_POST['ids'] as $k=>$v) {
                            if(($t=Team::lookup($v)) && $t->delete())
                                $i++;
                        }
                        if($i && $i==$count)
                            $msg = lang('team_deleted_success');
                        elseif($i>0)
                            $warn = "$i of $count ".lang('team_deleted');
                        elseif(!$errors['err'])
                            $errors['err'] = lang('unable_delete_team');
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

$page='teams.inc.php';
if($team || ($_REQUEST['a'] && !strcasecmp($_REQUEST['a'],'add')))
    $page='team.inc.php';

$nav->setTabActive('staff');
require(STAFFINC_DIR.'header.inc.php');
require(STAFFINC_DIR.$page);
include(STAFFINC_DIR.'footer.inc.php');
?>
