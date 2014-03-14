<?php
/*********************************************************************
    banlist.php

    List of banned email addresses

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require('admin.inc.php');
include_once(INCLUDE_DIR.'class.banlist.php');
require_once(INCLUDE_DIR.'languages/language_control/languages_processor.php');

/* Get the system ban list filter */
if(!($filter=Banlist::getFilter())) 
    $warn = lang('system_ban_empty');
elseif(!$filter->isActive())
    $warn = lang('system_ban_disab').' - <a href="filters.php">'.lang('enable_here').'</a>.'; 
 
$rule=null; //ban rule obj.
if($filter && $_REQUEST['id'] && !($rule=$filter->getRule($_REQUEST['id'])))
    $errors['err'] = lang('invalid_ban_id').' #';

if($_POST && !$errors && $filter){
    switch(strtolower($_POST['do'])){
        case 'update':
            if(!$rule){
                $errors['err']=lang('invalid_ban');
            }elseif(!$_POST['val'] || !Validator::is_email($_POST['val'])){
                $errors['err']=$errors['val']=lang('valid_email_requir');
            }elseif(!$errors){
                $vars=array('w'=>'email',
                            'h'=>'equal',
                            'v'=>$_POST['val'],
                            'filter_id'=>$filter->getId(),
                            'isactive'=>$_POST['isactive'],
                            'notes'=>$_POST['notes']);
                if($rule->update($vars,$errors)){
                    $msg=lang('email_upd_success');
                }elseif(!$errors['err']){
                    $errors['err']=lang('error_update_ban');
                }
            }
            break;
        case 'add':
            if(!$filter) {
                $errors['err']=lang('invalid_ban_list');
            }elseif(!$_POST['val'] || !Validator::is_email($_POST['val'])) {
                $errors['err']=$errors['val']=lang('valid_email_requir');
            }elseif(BanList::includes($_POST['val'])) {
                $errors['err']=$errors['val']=lang('email_in_ban');
            }elseif($filter->addRule('email','equal',$_POST['val'],array('isactive'=>$_POST['isactive'],'notes'=>$_POST['notes']))) {
                $msg=lang('email_add_to_ban');
                $_REQUEST['a']=null;
                //Add filter rule here.
            }elseif(!$errors['err']){
                $errors['err']=lang('error_ban_rule');
            }
            break;
        case 'mass_process':
            if(!$_POST['ids'] || !is_array($_POST['ids']) || !count($_POST['ids'])) {
                $errors['err'] = lang('select_one_email');
            } else {
                $count=count($_POST['ids']);
                switch(strtolower($_POST['a'])) {
                    case 'enable':
                        $sql='UPDATE '.FILTER_RULE_TABLE.' SET isactive=1 '
                            .' WHERE filter_id='.db_input($filter->getId())
                            .' AND id IN ('.implode(',', db_input($_POST['ids'])).')';
                        if(db_query($sql) && ($num=db_affected_rows())){
                            if($num==$count)
                                $msg = lang('ban_status_enable');
                            else
                                $warn = "$num ".lang('of')." $count ".lang('ban_status_enabled');
                        } else  {
                            $errors['err'] = lang('not_enable_emails');
                        }
                        break;
                    case 'disable':
                        $sql='UPDATE '.FILTER_RULE_TABLE.' SET isactive=0 '
                            .' WHERE filter_id='.db_input($filter->getId())
                            .' AND id IN ('.implode(',', db_input($_POST['ids'])).')';
                        if(db_query($sql) && ($num=db_affected_rows())) {
                            if($num==$count)
                                $msg = lang('ban_status_dis');
                            else
                                $warn = "$num ".lang('of')." $count ".lang('ban_status_dis');
                        } else {
                            $errors['err'] = lang('not_disable_email');
                        }
                        break;
                    case 'delete':
                        $i=0;
                        foreach($_POST['ids'] as $k=>$v) {
                            if(($r=FilterRule::lookup($v)) && $r->getFilterId()==$filter->getId() && $r->delete())
                                $i++;
                        }
                        if($i && $i==$count)
                            $msg = lang('e_ban_del_success');
                        elseif($i>0)
                            $warn = "$i ".lang('of')." $count ".lang('emails_ban_deleted');
                        elseif(!$errors['err'])
                            $errors['err'] = lang('unable_delete_email');
                    
                        break;
                    default:
                        $errors['err'] = lang('unknown_action');
                }
            }
            break;
        default:
            $errors['err']=lang('unknown_action_only');
            break;
    }
}

$page='banlist.inc.php';
if(!$filter || ($rule || ($_REQUEST['a'] && !strcasecmp($_REQUEST['a'],'add'))))
    $page='banrule.inc.php';

$nav->setTabActive('emails');
require(STAFFINC_DIR.'header.inc.php');
require(STAFFINC_DIR.$page);
include(STAFFINC_DIR.'footer.inc.php');
?>
