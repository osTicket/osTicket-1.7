<?php
/*********************************************************************
    categories.php

    FAQ categories

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require('staff.inc.php');
include_once(INCLUDE_DIR.'class.category.php');
require_once(INCLUDE_DIR.'languages/language_control/languages_processor.php');

/* check permission */
if(!$thisstaff || !$thisstaff->canManageFAQ()) {
    header('Location: kb.php');
    exit;
}


$category=null;
if($_REQUEST['id'] && !($category=Category::lookup($_REQUEST['id'])))
    $errors['err']=lang('invalid_catg_id');

if($_POST){
    switch(strtolower($_POST['do'])) {
        case 'update':
            if(!$category) {
                $errors['err']=lang('invalid_category');
            } elseif($category->update($_POST,$errors)) {
                $msg=lang('category_updated');
            } elseif(!$errors['err']) {
                $errors['err']=lang('error_update_cat');
            }
            break;
        case 'create':
            if(($id=Category::create($_POST,$errors))) {
                $msg=lang('catg_added_success');
                $_REQUEST['a']=null;
            } elseif(!$errors['err']) {
                $errors['err']=lang('cant_add_category').' '.lang('correct_errors');
            }
            break;
        case 'mass_process':
            if(!$_POST['ids'] || !is_array($_POST['ids']) || !count($_POST['ids'])) {
                $errors['err']=lang('select_one_catg');
            } else {
                $count=count($_POST['ids']);
                switch(strtolower($_POST['a'])) {
                    case 'make_public':
                        $sql='UPDATE '.FAQ_CATEGORY_TABLE.' SET ispublic=1 '
                            .' WHERE category_id IN ('.implode(',', db_input($_POST['ids'])).')';
                    
                        if(db_query($sql) && ($num=db_affected_rows())) {
                            if($num==$count)
                                $msg = lang('catg_make_public');
                            else
                                $warn = "$num ".lang('of')." $count ".lang('catg_make_public');
                        } else {
                            $errors['err'] = lang('cant_enable_catg');
                        }
                        break;
                    case 'make_private':
                        $sql='UPDATE '.FAQ_CATEGORY_TABLE.' SET ispublic=0 '
                            .' WHERE category_id IN ('.implode(',', db_input($_POST['ids'])).')';

                        if(db_query($sql) && ($num=db_affected_rows())) {
                            if($num==$count)
                                $msg = lang('categories_private');
                            else
                                $warn = "$num ".lang('of')." $count ".lang('categories_private');
                        } else {
                            $errors['err'] = lang('cant_disable_pcatg');
                        }
                        break;
                    case 'delete':
                        $i=0;
                        foreach($_POST['ids'] as $k=>$v) {
                            if(($c=Category::lookup($v)) && $c->delete())
                                $i++;
                        }

                        if($i==$count)
                            $msg = lang('categories_deleted');
                        elseif($i>0)
                            $warn = "$i ".lang('of')." $count ".lang('scatg_deleted');
                        elseif(!$errors['err'])
                            $errors['err'] = lang('cant_delete_catg');
                        break;
                    default:
                        $errors['err']=lang('unknown_command');
                }
            }
            break;
        default:
            $errors['err']=lang('unknown_action_only');
            break;
    }
}

$page='categories.inc.php';
if($category || ($_REQUEST['a'] && !strcasecmp($_REQUEST['a'],'add')))
    $page='category.inc.php';

$nav->setTabActive('kbase');
require(STAFFINC_DIR.'header.inc.php');
require(STAFFINC_DIR.$page);
include(STAFFINC_DIR.'footer.inc.php');
?>
