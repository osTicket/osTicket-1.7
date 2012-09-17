<?php
/*********************************************************************
    groups.php

    User Groups.

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2012 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require('admin.inc.php');
$group=null;
if($_REQUEST['id'] && !($group=Group::lookup($_REQUEST['id'])))
    $errors['err']=_('Unknown or invalid group ID.');

if($_POST){
    switch(strtolower($_POST['do'])){
        case 'update':
            if(!$group){
                $errors['err']=_('Unknown or invalid group.');
            }elseif($group->update($_POST,$errors)){
                $msg=_('Group updated successfully');
            }elseif(!$errors['err']){
                $errors['err']=_('Unable to update group. Correct any error(s) below and try again!');
            }
            break;
        case 'create':
            if(($id=Group::create($_POST,$errors))){
                $msg=Format::htmlchars($_POST['name']).' '._('added successfully');
                $_REQUEST['a']=null;
            }elseif(!$errors['err']){
                $errors['err']=_('Unable to add group. Correct error(s) below and try again.');
            }
            break;
        case 'mass_process':
            if(!$_POST['ids'] || !is_array($_POST['ids']) || !count($_POST['ids'])) {
                $errors['err']=_('You must select at least one group.');
            }else{
                $count=count($_POST['ids']);
                if($_POST['enable']){
                    $sql='UPDATE '.GROUP_TABLE.' SET group_enabled=1, updated=NOW() WHERE group_id IN ('.
                        implode(',', db_input($_POST['ids'])).')';
                    if(db_query($sql) && ($num=db_affected_rows())){
                        if($num==$count)
                            $msg=_('Selected groups activated');
                        else
                            $warn="$num "._("of")." $count "._("selected groups activated");
                    }else{
                        $errors['err']=_('Unable to activate selected groups');
                    }
                }elseif($_POST['disable']){
                    $sql='UPDATE '.GROUP_TABLE.' SET group_enabled=0, updated=NOW() WHERE group_id IN ('.
                        implode(',', db_input($_POST['ids'])).')';
                    if(db_query($sql) && ($num=db_affected_rows())) {
                        if($num==$count)
                            $msg=_('Selected groups disabled');
                        else
                            $warn="$num "._("of")." $count "._("selected groups disabled");
                    }else{
                        $errors['err']=_('Unable to disable selected groups');
                    }
                }elseif($_POST['delete']){
                    foreach($_POST['ids'] as $k=>$v) {
                        if(($g=Group::lookup($v)) && $g->delete())
                            $i++;
                    }

                    if($i && $i==$count)
                        $msg=_('Selected groups deleted successfully');
                    elseif($i>0)
                        $warn="$i "._("of")." $count "._("selected groups deleted");
                    elseif(!$errors['err'])
                        $errors['err']=_('Unable to delete selected groups');
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

$page='groups.inc.php';
if($group || ($_REQUEST['a'] && !strcasecmp($_REQUEST['a'],'add')))
    $page='group.inc.php';

$nav->setTabActive('staff');
require(STAFFINC_DIR.'header.inc.php');
require(STAFFINC_DIR.$page);
include(STAFFINC_DIR.'footer.inc.php');
?>
