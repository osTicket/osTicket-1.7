<?php
/*********************************************************************
    helptopics.php

    Help Topics.

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require('admin.inc.php');
include_once(INCLUDE_DIR.'class.topic.php');
require_once(INCLUDE_DIR.'languages/language_control/languages_processor.php');

$topic=null;
if($_REQUEST['id'] && !($topic=Topic::lookup($_REQUEST['id'])))
    $errors['err']=lang('inv_help_topic_id');

if($_POST){
    switch(strtolower($_POST['do'])){
        case 'update':
            if(!$topic){
                $errors['err']=lang('inv_help_topic');
            }elseif($topic->update($_POST,$errors)){
                $msg=lang('help_topc_updated');
            }elseif(!$errors['err']){
                $errors['err']=lang('cant_update_topic');
            }
            break;
        case 'create':
            if(($id=Topic::create($_POST,$errors))){
                $msg=lang('help_topic_added');
                $_REQUEST['a']=null;
            }elseif(!$errors['err']){
                $errors['err']=lang('cant_add_topic');
            }
            break;
        case 'mass_process':
            if(!$_POST['ids'] || !is_array($_POST['ids']) || !count($_POST['ids'])) {
                $errors['err'] = lang('select_one_topic');
            } else {
                $count=count($_POST['ids']);

                switch(strtolower($_POST['a'])) {
                    case 'enable':
                        $sql='UPDATE '.TOPIC_TABLE.' SET isactive=1 '
                            .' WHERE topic_id IN ('.implode(',', db_input($_POST['ids'])).')';
                    
                        if(db_query($sql) && ($num=db_affected_rows())) {
                            if($num==$count)
                                $msg = lang('select_help_topic');
                            else
                                $warn = "$num ".lang('of')." $count ".lang('select_help_topic');
                        } else {
                            $errors['err'] = lang('cant_enable_help');
                        }
                        break;
                    case 'disable':
                        $sql='UPDATE '.TOPIC_TABLE.' SET isactive=0 '
                            .' WHERE topic_id IN ('.implode(',', db_input($_POST['ids'])).')';
                        if(db_query($sql) && ($num=db_affected_rows())) {
                            if($num==$count)
                                $msg = lang('help_topic_dis');
                            else
                                $warn = "$num ".lang('of')." $count ".lang('help_topic_dis');
                        } else {
                            $errors['err'] =lang('cant_disable_topic');
                        }
                        break;
                    case 'delete':
                        $i=0;
                        foreach($_POST['ids'] as $k=>$v) {
                            if(($t=Topic::lookup($v)) && $t->delete())
                                $i++;
                        }

                        if($i && $i==$count)
                            $msg = lang('topics_deleted');
                        elseif($i>0)
                            $warn = "$i ".lang('of')." $count ".lang('topics_deleted_only');
                        elseif(!$errors['err'])
                            $errors['err']  = lang('cant_del_topic');

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

$page='helptopics.inc.php';
if($topic || ($_REQUEST['a'] && !strcasecmp($_REQUEST['a'],'add')))
    $page='helptopic.inc.php';


$nav->setTabActive('manage');
require(STAFFINC_DIR.'header.inc.php');
require(STAFFINC_DIR.$page);
include(STAFFINC_DIR.'footer.inc.php');
?>
