<?php
/*********************************************************************
    departments.php

    Departments

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2012 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require('admin.inc.php');
$dept=null;
if($_REQUEST['id'] && !($dept=Dept::lookup($_REQUEST['id'])))
    $errors['err']=_('Unknown or invalid department ID.');

if($_POST){
    switch(strtolower($_POST['do'])){
        case 'update':
            if(!$dept){
                $errors['err']=_('Unknown or invalid department.');
            }elseif($dept->update($_POST,$errors)){
                $msg=_('Department updated successfully');
            }elseif(!$errors['err']){
                $errors['err']=_('Error updating department. Try again!');
            }
            break;
        case 'create':
            if(($id=Dept::create($_POST,$errors))){
                $msg=Format::htmlchars($_POST['name']).' '._('added successfully');
                $_REQUEST['a']=null;
            }elseif(!$errors['err']){
                $errors['err']=_('Unable to add department. Correct error(s) below and try again.');
            }
            break;
        case 'mass_process':
            if(!$_POST['ids'] || !is_array($_POST['ids']) || !count($_POST['ids'])) {
                $errors['err']=_('You must select at least one department');
            }elseif(!$_POST['public'] && in_array($cfg->getDefaultDeptId(),$_POST['ids'])) {
                $errors['err']=_('You can not disable/delete a default department. Remove default Dept. and try again.');
            }else{
                $count=count($_POST['ids']);
                if($_POST['public']){
                    $sql='UPDATE '.DEPT_TABLE.' SET ispublic=1 WHERE dept_id IN ('
                        .implode(',', db_input($_POST['ids'])).')';
                    if(db_query($sql) && ($num=db_affected_rows())){
                        if($num==$count)
                            $msg=_('Selected departments made public');
                        else
                            $warn="$num "._("of")." $count "._("selected departments made public");
                    }else{
                        $errors['err']=_('Unable to make selected department public.');
                    }
                }elseif($_POST['private']){
                    $sql='UPDATE '.DEPT_TABLE.' SET ispublic=0  '.
                         'WHERE dept_id IN ('
                            .implode(',', db_input($_POST['ids']))
                        .') AND dept_id!='.db_input($cfg->getDefaultDeptId());
                    if(db_query($sql) && ($num=db_affected_rows())) {
                        if($num==$count)
                            $msg=_('Selected departments made private');
                        else
                            $warn="$num "._("of")." $count "._("selected departments made private");
                    }else{
                        $errors['err']=_('Unable to make selected department(s) private. Possibly already private!');
                    }

                }elseif($_POST['delete']){
                    //Deny all deletes if one of the selections has members in it.
                    $sql='SELECT count(staff_id) FROM '.STAFF_TABLE.' WHERE dept_id IN ('
                        .implode(',', db_input($_POST['ids'])).')';
                    list($members)=db_fetch_row(db_query($sql));
                    if($members)
                        $errors['err']=_('Dept. with users can not be deleted. Move staff first.');
                    else{
                        $i=0;
                        foreach($_POST['ids'] as $k=>$v) {
                            if($v!=$cfg->getDefaultDeptId() && ($d=Dept::lookup($v)) && $d->delete())
                                $i++;
                        }
                        if($i && $i==$count)
                            $msg=_('Selected departments deleted successfully');
                        elseif($i>0)
                            $warn="$i "._("of")." $count "._("selected departments deleted");
                        elseif(!$errors['err'])
                            $errors['err']=_('Unable to delete selected departments.');
                    }
                }else {
                    $errors['err']=_('Unknown action');
                }
            }
            break;
        default:
            $errors['err']=_('Unknown action');
            break;
    }
}

$page='departments.inc.php';
if($dept || ($_REQUEST['a'] && !strcasecmp($_REQUEST['a'],'add')))
    $page='department.inc.php';

$nav->setTabActive('staff');
require(STAFFINC_DIR.'header.inc.php');
require(STAFFINC_DIR.$page);
include(STAFFINC_DIR.'footer.inc.php');
?>
