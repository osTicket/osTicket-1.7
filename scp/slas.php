<?php
/*********************************************************************
    slas.php

    SLA - Service Level Agreements

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require('admin.inc.php');
include_once(INCLUDE_DIR.'class.sla.php');
require_once(INCLUDE_DIR.'languages/language_control/languages_processor.php');

$sla=null;
if($_REQUEST['id'] && !($sla=SLA::lookup($_REQUEST['id'])))
    $errors['err']=lang('invalid_api');

if($_POST){
    switch(strtolower($_POST['do'])){
        case 'update':
            if(!$sla){
                $errors['err']=lang('invalid_sla_plan');
            }elseif($sla->update($_POST,$errors)){
                $msg=lang('sla_plan_updated');
            }elseif(!$errors['err']){
                $errors['err']=lang('error_update_sla');
            }
            break;
        case 'add':
            if(($id=SLA::create($_POST,$errors))){
                $msg=lang('sla_added_success');
                $_REQUEST['a']=null;
            }elseif(!$errors['err']){
                $errors['err']=lang('cant_add_sla_plan');
            }
            break;
        case 'mass_process':
            if(!$_POST['ids'] || !is_array($_POST['ids']) || !count($_POST['ids'])) {
                $errors['err'] = lang('select_one_plan');
            } else {
                $count=count($_POST['ids']);
                switch(strtolower($_POST['a'])) {
                    case 'enable':
                        $sql='UPDATE '.SLA_TABLE.' SET isactive=1 '
                            .' WHERE id IN ('.implode(',', db_input($_POST['ids'])).')';
                    
                        if(db_query($sql) && ($num=db_affected_rows())) {
                            if($num==$count)
                                $msg = lang('enabled_sla_plans');
                            else
                                $warn = "$num ".lang('of')." $count ".lang('enabled_sla_plans');;
                        } else {
                            $errors['err'] = lang('cant_enable_sla');
                        }
                        break;
                    case 'disable':
                        $sql='UPDATE '.SLA_TABLE.' SET isactive=0 '
                            .' WHERE id IN ('.implode(',', db_input($_POST['ids'])).')';
                        if(db_query($sql) && ($num=db_affected_rows())) {
                            if($num==$count)
                                $msg = lang('sla_plan_disabled');
                            else
                                $warn = "$num ".lang('of')." $count ".lang('sla_plan_disabled');
                        } else {
                            $errors['err'] = lang('cant_disable_sla');
                        }
                        break;
                    case 'delete':
                        $i=0;
                        foreach($_POST['ids'] as $k=>$v) {
                            if(($p=SLA::lookup($v)) && $p->delete())
                                $i++;
                        }

                        if($i && $i==$count)
                            $msg = lang('sla_plans_deleted').' '.lang('successfully');
                        elseif($i>0)
                            $warn = "$i ".lang('of')." $count ".lang('sla_plans_deleted');
                        elseif(!$errors['err'])
                            $errors['err'] = lang('cant_delete_sla');
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

$page='slaplans.inc.php';
if($sla || ($_REQUEST['a'] && !strcasecmp($_REQUEST['a'],'add')))
    $page='slaplan.inc.php';

$nav->setTabActive('manage');
require(STAFFINC_DIR.'header.inc.php');
require(STAFFINC_DIR.$page);
include(STAFFINC_DIR.'footer.inc.php');
?>
