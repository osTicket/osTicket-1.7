<?php
/*********************************************************************
    apikeys.php

    API keys.

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require('admin.inc.php');
include_once(INCLUDE_DIR.'class.api.php');
require_once(INCLUDE_DIR.'languages/language_control/languages_processor.php');

$api=null;
if($_REQUEST['id'] && !($api=API::lookup($_REQUEST['id'])))
    $errors['err']=lang('invalid_api');

if($_POST){
    switch(strtolower($_POST['do'])){
        case 'update':
            if(!$api){
                $errors['err']=lang('invalid_api_key');
            }elseif($api->update($_POST,$errors)){
                $msg=lang('api_key_added');
            }elseif(!$errors['err']){
                $errors['err']=lang('api_key_updated');
            }
            break;
        case 'add':
            if(($id=API::add($_POST,$errors))){
                $msg=lang('api_key_added');
                $_REQUEST['a']=null;
            }elseif(!$errors['err']){
                $errors['err']=lang('cant_add_api');
            }
            break;
        case 'mass_process':
            if(!$_POST['ids'] || !is_array($_POST['ids']) || !count($_POST['ids'])) {
                $errors['err'] = lang('select_one_api');
            } else {
                $count=count($_POST['ids']);
                switch(strtolower($_POST['a'])) {
                    case 'enable':
                        $sql='UPDATE '.API_KEY_TABLE.' SET isactive=1 '
                            .' WHERE id IN ('.implode(',', db_input($_POST['ids'])).')';
                        if(db_query($sql) && ($num=db_affected_rows())) {
                            if($num==$count)
                                $msg = lang('api_key_enabled');
                            else
                                $warn = "$num ".lang('of')." $count ".lang('api_key_enabled');
                        } else {
                            $errors['err'] = lang('cant_enable_api');
                        }
                        break;
                    case 'disable':
                        $sql='UPDATE '.API_KEY_TABLE.' SET isactive=0 '
                            .' WHERE id IN ('.implode(',', db_input($_POST['ids'])).')';
                        if(db_query($sql) && ($num=db_affected_rows())) {
                            if($num==$count)
                                $msg = lang('api_key_disabled');
                            else
                                $warn = "$num ".lang('of')." $count ".lang('api_key_disabled');
                        } else {
                            $errors['err']=lang('cant_disable_api');
                        }
                        break;
                    case 'delete':
                        $i=0;
                        foreach($_POST['ids'] as $k=>$v) {
                            if(($t=API::lookup($v)) && $t->delete())
                                $i++;
                        }
                        if($i && $i==$count)
                            $msg = lang('api_key_deleted').' '.lang('successfully');
                        elseif($i>0)
                            $warn = "$i ".lang('of')." $count ".lang('api_key_deleted');
                        elseif(!$errors['err'])
                            $errors['err'] = lang('cant_delete_api');
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

$page='apikeys.inc.php';
if($api || ($_REQUEST['a'] && !strcasecmp($_REQUEST['a'],'add')))
    $page='apikey.inc.php';

$nav->setTabActive('manage');
require(STAFFINC_DIR.'header.inc.php');
require(STAFFINC_DIR.$page);
include(STAFFINC_DIR.'footer.inc.php');
?>
