<?php
/*********************************************************************
    departments.php

    Departments

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require('admin.inc.php');
require_once(INCLUDE_DIR.'languages/language_control/languages_processor.php');

$dept=null;
if($_REQUEST['id'] && !($dept=Dept::lookup($_REQUEST['id'])))
    $errors['err']=lang('invalid_dep_id').'.';

if($_POST){
    switch(strtolower($_POST['do'])){
        case 'update':
            if(!$dept){
                $errors['err']=lang('invalid_dep').'.';
            }elseif($dept->update($_POST,$errors)){
                $msg=lang('dep_update_success');
            }elseif(!$errors['err']){
                $errors['err']=lang('error_upd_departm').'!';
            }
            break;
        case 'create':
            if(($id=Dept::create($_POST,$errors))){
                $msg=Format::htmlchars($_POST['name']).' '. lang('added_succesfully');
                $_REQUEST['a']=null;
            }elseif(!$errors['err']){
                $errors['err']=lang('unable_add_departm').' '. lang('correct_errors').' .';
            }
            break;
        case 'mass_process':
            if(!$_POST['ids'] || !is_array($_POST['ids']) || !count($_POST['ids'])) {
                $errors['err'] = lang('at_least_one').' '. lang('department');
            }elseif(in_array($cfg->getDefaultDeptId(),$_POST['ids'])) {
                $errors['err'] = lang('cant_disab_departm').'.';
            }else{
                $count=count($_POST['ids']);
                switch(strtolower($_POST['a'])) {
                    case 'make_public':
                        $sql='UPDATE '.DEPT_TABLE.' SET ispublic=1 '
                            .' WHERE dept_id IN ('.implode(',', db_input($_POST['ids'])).')';
                        if(db_query($sql) && ($num=db_affected_rows())){
                            if($num==$count)
                                $msg=lang('Spublic_departm');
                            else
                                $warn="$num of $count ".lang('Spublic_departm');
                        } else {
                            $errors['err']=lang('error_public_departm').'.';
                        }
                        break;
                    case 'make_private':
                        $sql='UPDATE '.DEPT_TABLE.' SET ispublic=0  '
                            .' WHERE dept_id IN ('.implode(',', db_input($_POST['ids'])).') '
                            .' AND dept_id!='.db_input($cfg->getDefaultDeptId());
                        if(db_query($sql) && ($num=db_affected_rows())) {
                            if($num==$count)
                                $msg = lang('private_departm');
                            else
                                $warn = "$num of $count ".lang('private_departm');
                        } else {
                            $errors['err'] = lang('error_priv_departm').'!';
                        }
                        break;
                    case 'delete':
                        //Deny all deletes if one of the selections has members in it.
                        $sql='SELECT count(staff_id) FROM '.STAFF_TABLE
                            .' WHERE dept_id IN ('.implode(',', db_input($_POST['ids'])).')';
                        list($members)=db_fetch_row(db_query($sql));
                        if($members)
                            $errors['err']=lang('cant_delete_departm').'.';
                        else {
                            $i=0;
                            foreach($_POST['ids'] as $k=>$v) {
                                if($v!=$cfg->getDefaultDeptId() && ($d=Dept::lookup($v)) && $d->delete())
                                    $i++;
                            }
                            if($i && $i==$count)
                                $msg = lang('delete_dep_succes');
                            elseif($i>0)
                                $warn = "$i of $count ".lang('departm_deleted');
                            elseif(!$errors['err'])
                                $errors['err'] = lang('error_delete_departm').'.';
                        }
                        break;
                    default:
                        $errors['err']=lang('unknown_action');
                }
            }
            break;
        default:
            $errors['err']=lang('unknown_command');
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
