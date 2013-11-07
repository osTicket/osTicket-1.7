<?php
/*********************************************************************
    templates.php

    Email Templates

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require('admin.inc.php');
include_once(INCLUDE_DIR.'class.template.php');
require_once(INCLUDE_DIR.'languages/language_control/languages_processor.php');
$template=null;
if($_REQUEST['tpl_id'] &&
        !($template=EmailTemplateGroup::lookup($_REQUEST['tpl_id'])))
    $errors['err']=lang('Unknown or invalid template group ID.');
elseif($_REQUEST['id'] &&
        !($template=EmailTemplate::lookup($_REQUEST['id'])))
    $errors['err']=lang('Unknown or invalid template ID.');

if($_POST){
    switch(strtolower($_POST['do'])){
        case 'updatetpl':
            if(!$template){
                $errors['err']=lang('Unknown or invalid template');
            }elseif($template->update($_POST,$errors)){
                $template->reload();
                $msg=lang('Message template updated successfully');
            }elseif(!$errors['err']){
                $errors['err']=lang('error_update_temp');
            }
            break;
        case 'implement':
            if(!$template){
                $errors['err']=lang('Unknown or invalid template');
            }elseif($new = EmailTemplate::add($_POST,$errors)){
                $template = $new;
                $msg=lang('Message template updated successfully');
            }elseif(!$errors['err']){
                $errors['err']=lang('error_update_temp');
            }
            break;
        case 'update':
            if(!$template){
                $errors['err']=lang('Unknown or invalid template');
            }elseif($template->update($_POST,$errors)){
                $msg=lang('Template updated successfully');
            }elseif(!$errors['err']){
                $errors['err']=lang('Error updating template. Try again!');
            }
            break;
        case 'add':
            if(($new=EmailTemplateGroup::add($_POST,$errors))){
                $template=$new;
                $msg=lang('Template added successfully');
                $_REQUEST['a']=null;
            }elseif(!$errors['err']){
                $errors['err']=lang('Unable to add template. Correct error(s) below and try again.');
            }
            break;
        case 'mass_process':
            if(!$_POST['ids'] || !is_array($_POST['ids']) || !count($_POST['ids'])) {
                $errors['err']=lang('You must select at least one template to process.');
            } else {
                $count=count($_POST['ids']);
                switch(strtolower($_POST['a'])) {
                    case 'enable':
                        $sql='UPDATE '.EMAIL_TEMPLATE_GRP_TABLE.' SET isactive=1 '
                            .' WHERE tpl_id IN ('.implode(',', db_input($_POST['ids'])).')';
                        if(db_query($sql) && ($num=db_affected_rows())){
                            if($num==$count)
                                $msg = lang('Selected templates enabled');
                            else
                                $warn = "$num ".lang('of')." $count ".lang("selected templates enabled");
                        } else {
                            $errors['err'] = lang('Unable to enable selected templates');
                        }
                        break;
                    case 'disable':
                        $i=0;
                        foreach($_POST['ids'] as $k=>$v) {
                            if(($t=EmailTemplateGroup::lookup($v)) && !$t->isInUse() && $t->disable())
                                $i++;
                        }
                        if($i && $i==$count)
                            $msg = lang('Selected templates disabled');
                        elseif($i)
                            $warn = "$i ".lang('of')." $count ".lang("selected templates disabled (in-use templates can't be disabled)");
                        else
                            $errors['err'] = lang("Unable to disable selected templates (in-use or default template can't be disabled)");
                        break;
                    case 'delete':
                        $i=0;
                        foreach($_POST['ids'] as $k=>$v) {
                            if(($t=EmailTemplateGroup::lookup($v)) && !$t->isInUse() && $t->delete())
                                $i++;
                        }

                        if($i && $i==$count)
                            $msg = lang('Selected templates deleted successfully');
                        elseif($i>0)
                            $warn = "$i ".lang('of')." $count ".lang("selected templates deleted");
                        elseif(!$errors['err'])
                            $errors['err'] = lang('Unable to delete selected templates');
                        break;
                    default:
                        $errors['err']=lang('Unknown template action');
                }
            }
            break;
        default:
            $errors['err']=lang('Unknown action');
            break;
    }
}

$page='templates.inc.php';
if($template && !strcasecmp($_REQUEST['a'],'manage')){
    $page='tpl.inc.php';
}elseif($template && !strcasecmp($_REQUEST['a'],'implement')){
    $page='tpl.inc.php';
}elseif($template || !strcasecmp($_REQUEST['a'],'add')){
    $page='template.inc.php';
}

$nav->setTabActive('emails');
require(STAFFINC_DIR.'header.inc.php');
require(STAFFINC_DIR.$page);
include(STAFFINC_DIR.'footer.inc.php');
?>
