<?php
/*********************************************************************
    faq.php

    FAQs.

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require('staff.inc.php');
require_once(INCLUDE_DIR.'class.faq.php');
require_once(INCLUDE_DIR.'languages/language_control/languages_processor.php');

$faq=$category=null;
if($_REQUEST['id'] && !($faq=FAQ::lookup($_REQUEST['id'])))
   $errors['err']=lang('invalid_faq');

if($_REQUEST['cid'] && !$faq && !($category=Category::lookup($_REQUEST['cid'])))
    $errors['err']=lang('invalid_faq_catg');

if($_POST):
    $errors=array();
    switch(strtolower($_POST['do'])) {
        case 'create':
        case 'add':
            if(($faq=FAQ::add($_POST,$errors)))
                $msg=lang('faq_added_success');
            elseif(!$errors['err'])
                $errors['err'] = lang('cant_add_faq');
        break;
        case 'update':
        case 'edit';
            if(!$faq)
                $errors['err'] = lang('invalid_faq');
            elseif($faq->update($_POST,$errors)) {
                $msg=lang('faq_apdated');
                $_REQUEST['a']=null; //Go back to view
                $faq->reload();
            } elseif(!$errors['err'])
                $errors['err'] = lang('cant_upd_faq_only');     
            break;
        case 'manage-faq':
            if(!$faq) {
                $errors['err']=lang('invalid_faq');
            } else {
                switch(strtolower($_POST['a'])) {
                    case 'edit':
                        $_GET['a']='edit';
                        break;
                    case 'publish';
                        if($faq->publish())
                            $msg=lang('faq_published');
                        else
                            $errors['err']=lang('cant_publish_faq');
                        break;
                    case 'unpublish';
                        if($faq->unpublish())
                            $msg=lang('faq_unpublished');
                        else
                            $errors['err']=lang('cant_anpublish_faq');
                        break;
                    case 'delete':
                        $category = $faq->getCategory();
                        if($faq->delete()) {
                            $msg=lang('faq_deleted');
                            $faq=null;
                        } else {
                            $errors['err']=lang('cant_delete_faq');
                        }
                        break;
                    default:
                        $errors['err']=lang('invalid_action');
                }
            }
            break;
        default:
            $errors['err']=lang('unknown_action_only');
    
    }
endif;


$inc='faq-categories.inc.php'; //FAQs landing page.
if($faq) {
    $inc='faq-view.inc.php';
    if($_REQUEST['a']=='edit' && $thisstaff->canManageFAQ())
        $inc='faq.inc.php';
}elseif($_REQUEST['a']=='add' && $thisstaff->canManageFAQ()) {
    $inc='faq.inc.php';
} elseif($category && $_REQUEST['a']!='search') {
    $inc='faq-category.inc.php';
}
$nav->setTabActive('kbase');
require_once(STAFFINC_DIR.'header.inc.php');
require_once(STAFFINC_DIR.$inc);
require_once(STAFFINC_DIR.'footer.inc.php');
?>
