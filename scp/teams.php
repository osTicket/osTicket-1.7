<?php
/*********************************************************************
    teams.php

    Evertything about teams

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2012 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require('admin.inc.php');
$team=null;
if($_REQUEST['id'] && !($team=Team::lookup($_REQUEST['id'])))
    $errors['err']=_('Unknown or invalid team ID.');

if($_POST){
    switch(strtolower($_POST['do'])){
        case 'update':
            if(!$team){
                $errors['err']=_('Unknown or invalid team.');
            }elseif($team->update($_POST,$errors)){
                $msg=_('Team updated successfully');
            }elseif(!$errors['err']){
                $errors['err']=_('Unable to update team. Correct any error(s) below and try again!');
            }
            break;
        case 'create':
            if(($id=Team::create($_POST,$errors))){
                $msg=Format::htmlchars($_POST['team']).' '._('added successfully');
                $_REQUEST['a']=null;
            }elseif(!$errors['err']){
                $errors['err']=_('Unable to add team. Correct any error(s) below and try again.');
            }
            break;
        case 'mass_process':
            if(!$_POST['ids'] || !is_array($_POST['ids']) || !count($_POST['ids'])) {
                $errors['err']=_('You must select at least one team.');
            }else{
                $count=count($_POST['ids']);
                if($_POST['enable']){
                    $sql='UPDATE '.TEAM_TABLE.' SET isenabled=1 WHERE team_id IN ('.
                        implode(',', db_input($_POST['ids'])).')';
                    if(db_query($sql) && ($num=db_affected_rows())){
                        if($num==$count)
                            $msg=_('Selected teams activated');
                        else
                            $warn="$num "._("of")." $count "._("selected teams activated");
                    }else{
                        $errors['err']=_('Unable to activate selected teams');
                    }
                }elseif($_POST['disable']){
                    $sql='UPDATE '.TEAM_TABLE.' SET isenabled=0 WHERE team_id IN ('.
                        implode(',', db_input($_POST['ids'])).')';
                    if(db_query($sql) && ($num=db_affected_rows())) {
                        if($num==$count)
                            $msg=_('Selected teams disabled');
                        else
                            $warn="$num "._("of")." $count "._("selected teams disabled");
                    }else{
                        $errors['err']=_('Unable to disable selected teams');
                    }
                }elseif($_POST['delete']){
                    foreach($_POST['ids'] as $k=>$v) {
                        if(($t=Team::lookup($v)) && $t->delete())
                            $i++;
                    }

                    if($i && $i==$count)
                        $msg=_('Selected teams deleted successfully');
                    elseif($i>0)
                        $warn="$i "._("of")." $count "._("selected teams deleted");
                    elseif(!$errors['err'])
                        $errors['err']=_('Unable to delete selected teams');
                }else{
                    $errors['err']=_('Unknown action. Get technical help!');
                }
            }
            break;
        default:
            $errors['err']=_('Unknown action');
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
