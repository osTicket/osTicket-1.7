<?php
/*********************************************************************
    emails.php

    Emails

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require('admin.inc.php');
include_once(INCLUDE_DIR.'class.email.php');
require_once(INCLUDE_DIR.'languages/language_control/languages_processor.php');

$email=null;
if($_REQUEST['id'] && !($email=Email::lookup($_REQUEST['id'])))
    $errors['err']=lang('unknow_email_id');

if($_POST){
    switch(strtolower($_POST['do'])){
        case 'update':
            if(!$email){
                $errors['err']=lang('unknow_email');
            }elseif($email->update($_POST,$errors)){
                $msg=lang('email_upd_success');
            }elseif(!$errors['err']){
                $errors['err']=lang('errror_upd_email');
            }
            break;
        case 'create':
            if(($id=Email::create($_POST,$errors))){
                $msg=lang('email_added_success');
                $_REQUEST['a']=null;
            }elseif(!$errors['err']){
                $errors['err']=lang('unable_add_email');
            }
            break;
        case 'mass_process':
            if(!$_POST['ids'] || !is_array($_POST['ids']) || !count($_POST['ids'])) {
                $errors['err'] = lang('select_one_email');
            } else {
                $count=count($_POST['ids']);

                $sql='SELECT count(dept_id) FROM '.DEPT_TABLE.' dept '
                    .' WHERE email_id IN ('.implode(',', db_input($_POST['ids'])).') '
                    .' OR autoresp_email_id IN ('.implode(',', db_input($_POST['ids'])).')';

                list($depts)=db_fetch_row(db_query($sql));
                if($depts>0) {
                    $errors['err'] = lang('email_use_by_dep');
                } elseif(!strcasecmp($_POST['a'], 'delete')) {
                    $i=0;
                    foreach($_POST['ids'] as $k=>$v) {
                        if($v!=$cfg->getDefaultEmailId() && ($e=Email::lookup($v)) && $e->delete())
                            $i++;
                    }

                    if($i && $i==$count)
                        $msg = lang('emails_deleted');
                    elseif($i>0)
                        $warn = "$i ".lang('of')." $count ".lang('selected_emails');
                    elseif(!$errors['err'])
                        $errors['err'] = lang('unable_delete_email');
                    
                } else {
                    $errors['err'] = lang('unknown_action');
                }
            }
            break;
        default:
            $errors['err'] = lang('unknown_command');
            break;
    }
}

$page='emails.inc.php';
if($email || ($_REQUEST['a'] && !strcasecmp($_REQUEST['a'],'add')))
    $page='email.inc.php';

$nav->setTabActive('emails');
require(STAFFINC_DIR.'header.inc.php');
require(STAFFINC_DIR.$page);
include(STAFFINC_DIR.'footer.inc.php');
?>
