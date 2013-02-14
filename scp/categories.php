<?php
/*********************************************************************
    categories.php

    FAQ categories

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2012 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require('staff.inc.php');
include_once(INCLUDE_DIR.'class.category.php');

/* check permission */
if(!$thisstaff || !$thisstaff->canManageFAQ()) {
    header('Location: kb.php');
    exit;
}


$category=null;
if($_REQUEST['id'] && !($category=Category::lookup($_REQUEST['id'])))
    $errors['err']=_('Unknown or invalid category ID.');

if($_POST){
    switch(strtolower($_POST['do'])) {
        case 'update':
            if(!$category) {
                $errors['err']=_('Unknown or invalid category.');
            } elseif($category->update($_POST,$errors)) {
                $msg=_('Category updated successfully');
            } elseif(!$errors['err']) {
                $errors['err']=_('Error updating category. Try again!');
            }
            break;
        case 'create':
            if(($id=Category::create($_POST,$errors))) {
                $msg=_('Category added successfully');
                $_REQUEST['a']=null;
            } elseif(!$errors['err']) {
                $errors['err']=_('Unable to add category. Correct error(s) below and try again.');
            }
            break;
        case 'mass_process':
            if(!$_POST['ids'] || !is_array($_POST['ids']) || !count($_POST['ids'])) {
                $errors['err']=_('You must select at least one category');
            } else {
                $count=count($_POST['ids']);
                switch(strtolower($_POST['a'])) {
                    case 'make_public':
                        $sql='UPDATE '.FAQ_CATEGORY_TABLE.' SET ispublic=1 '
                            .' WHERE category_id IN ('.implode(',', db_input($_POST['ids'])).')';
                    
                        if(db_query($sql) && ($num=db_affected_rows())) {
                            if($num==$count)
                                $msg = _('Selected categories made PUBLIC');
                            else
                                $warn = "$num "._("of")." $count "._("selected categories made PUBLIC");
                        } else {
                            $errors['err'] = _('Unable to enable selected categories public.');
                        }
                        break;
                    case 'make_private':
                        $sql='UPDATE '.FAQ_CATEGORY_TABLE.' SET ispublic=0 '
                            .' WHERE category_id IN ('.implode(',', db_input($_POST['ids'])).')';

                        if(db_query($sql) && ($num=db_affected_rows())) {
                            if($num==$count)
                                $msg = _('Selected categories made PRIVATE');
                            else
                                $warn = "$num "._("of")." $count "._("selected categories made PRIVATE");
                        } else {
                            $errors['err'] = _('Unable to disable selected categories PRIVATE');
                        }
                        break;
                    case 'delete':
                        $i=0;
                        foreach($_POST['ids'] as $k=>$v) {
                            if(($c=Category::lookup($v)) && $c->delete())
                                $i++;
                        }

                        if($i==$count)
                            $msg = _('Selected categories deleted successfully');
                        elseif($i>0)
                            $warn = "$i "._("of")." $count "._("selected categories deleted");
                        elseif(!$errors['err'])
                            $errors['err'] = _('Unable to delete selected categories');
                        break;
                    default:
                        $errors['err']=_('Unknown action/command');
                }
            }
            break;
        default:
            $errors['err']=_('Unknown action');
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
