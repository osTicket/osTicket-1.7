<?php
/*********************************************************************
    filters.php

    Email Filters

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require('admin.inc.php');
include_once(INCLUDE_DIR.'class.filter.php');
require_once(INCLUDE_DIR.'class.canned.php');
require_once(INCLUDE_DIR.'languages/language_control/languages_processor.php');
$filter=null;
if($_REQUEST['id'] && !($filter=Filter::lookup($_REQUEST['id'])))
    $errors['err']=lang('invalid_filter');

/* NOTE: Banlist has its own interface*/
if($filter && $filter->isSystemBanlist())
    header('Location: banlist.php');

if($_POST){
    switch(strtolower($_POST['do'])){
        case 'update':
            if(!$filter){
                $errors['err']=lang('invalid_filter');
            }elseif($filter->update($_POST,$errors)){
                $msg=lang('filter_upd_success');
            }elseif(!$errors['err']){
                $errors['err']=lang('error_update_filt');
            }
            break;
        case 'add':
            if((Filter::create($_POST,$errors))){
                $msg=lang('filter_added');
                $_REQUEST['a']=null;
            }elseif(!$errors['err']){
                $errors['err']=lang('cant_add_filter');
            }
            break;
        case 'mass_process':
            if(!$_POST['ids'] || !is_array($_POST['ids']) || !count($_POST['ids'])) {
                $errors['err'] = lang('select_one_filter');
            } else {
                $count=count($_POST['ids']);
                switch(strtolower($_POST['a'])) {
                    case 'enable':
                        $sql='UPDATE '.FILTER_TABLE.' SET isactive=1 '
                            .' WHERE id IN ('.implode(',', db_input($_POST['ids'])).')';
                        if(db_query($sql) && ($num=db_affected_rows())) {
                            if($num==$count)
                                $msg = lang('select_filter_enab');
                            else
                                $warn = "$num ".lang('of')." $count ".lang('select_filter_enab');
                        } else {
                            $errors['err'] = lang('cant_enable_filter');
                        }
                        break;
                    case 'disable':
                        $sql='UPDATE '.FILTER_TABLE.' SET isactive=0 '
                            .' WHERE id IN ('.implode(',', db_input($_POST['ids'])).')';
                        if(db_query($sql) && ($num=db_affected_rows())) {
                            if($num==$count)
                                $msg = lang('filters_disabled');
                            else
                                $warn = "$num ".lang('of')." $count ".lang('filters_disabled');
                        } else {
                            $errors['err'] = lang('cant_disable_filter');
                        }
                        break;
                    case 'delete':
                        $i=0;
                        foreach($_POST['ids'] as $k=>$v) {
                            if(($f=Filter::lookup($v)) && !$f->isSystemBanlist() && $f->delete())
                                $i++;
                        }
                        
                        if($i && $i==$count)
                            $msg = lang('select_filter_delet');
                        elseif($i>0)
                            $warn = "$i ".lang('of')." $count ".lang('filters_deleted');
                        elseif(!$errors['err'])
                            $errors['err'] = lang('cant_delete_filter');
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

$page='filters.inc.php';
if($filter || ($_REQUEST['a'] && !strcasecmp($_REQUEST['a'],'add')))
    $page='filter.inc.php';

$nav->setTabActive('manage');
require(STAFFINC_DIR.'header.inc.php');
require(STAFFINC_DIR.$page);
include(STAFFINC_DIR.'footer.inc.php');
?>
