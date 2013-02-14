<?php
/*********************************************************************
    canned.php

    Canned Responses aka Premade Responses.

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2012 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require('staff.inc.php');
include_once(INCLUDE_DIR.'class.canned.php');
/* check permission */
if(!$thisstaff || !$thisstaff->canManageCannedResponses()) {
    header('Location: kb.php');
    exit;
}

//TODO: Support attachments!

$canned=null;
if($_REQUEST['id'] && !($canned=Canned::lookup($_REQUEST['id'])))
    $errors['err']=_('Unknown or invalid canned response ID.');

if($_POST && $thisstaff->canManageCannedResponses()) {
    switch(strtolower($_POST['do'])) {
        case 'update':
            if(!$canned) {
                $errors['err']=_('Unknown or invalid canned response.');
            } elseif($canned->update($_POST, $errors)) {
                $msg=_('Canned response updated successfully');
                //Delete removed attachments.
                //XXX: files[] shouldn't be changed under any circumstances.
                $keepers = $_POST['files']?$_POST['files']:array();
                $attachments = $canned->getAttachments(); //current list of attachments.
                foreach($attachments as $k=>$file) {
                    if($file['id'] && !in_array($file['id'], $keepers)) {
                        $canned->deleteAttachment($file['id']);
                    }
                }
                //Upload NEW attachments IF ANY - TODO: validate attachment types??
                if($_FILES['attachments'] && ($files=Format::files($_FILES['attachments'])))
                    $canned->uploadAttachments($files);

                $canned->reload();

            } elseif(!$errors['err']) {
                $errors['err']=_('Error updating canned response. Try again!');
            }
            break;
        case 'create':
            if(($id=Canned::create($_POST, $errors))) {
                $msg=_('Canned response added successfully');
                $_REQUEST['a']=null;
                //Upload attachments
                if($_FILES['attachments'] && ($c=Canned::lookup($id)) && ($files=Format::files($_FILES['attachments'])))
                    $c->uploadAttachments($files);

            } elseif(!$errors['err']) {
                $errors['err']=_('Unable to add canned response. Correct error(s) below and try again.');
            }
            break;
        case 'mass_process':
            if(!$_POST['ids'] || !is_array($_POST['ids']) || !count($_POST['ids'])) {
                $errors['err']=_('You must select at least one canned response');
            } else {
                $count=count($_POST['ids']);
                switch(strtolower($_POST['a'])) {
                    case 'enable':
                        $sql='UPDATE '.CANNED_TABLE.' SET isenabled=1 '
                            .' WHERE canned_id IN ('.implode(',', db_input($_POST['ids'])).')';
                        if(db_query($sql) && ($num=db_affected_rows())) {
                            if($num==$count)
                                $msg = _('Selected canned responses enabled');
                            else
                                $warn = "$num "._("of")." $count "._("selected canned responses enabled");
                        } else {
                            $errors['err'] = _('Unable to enable selected canned responses.');
                        }
                        break;
                    case 'disable':
                        $sql='UPDATE '.CANNED_TABLE.' SET isenabled=0 '
                            .' WHERE canned_id IN ('.implode(',', db_input($_POST['ids'])).')';
                        if(db_query($sql) && ($num=db_affected_rows())) {
                            if($num==$count)
                                $msg = _('Selected canned responses disabled');
                            else
                                $warn = "$num "._("of")." $count "._("selected canned responses disabled");
                        } else {
                            $errors['err'] = _('Unable to disable selected canned responses');
                        }
                        break;
                    case 'delete':

                        $i=0;
                        foreach($_POST['ids'] as $k=>$v) {
                            if(($c=Canned::lookup($v)) && $c->delete())
                                $i++;
                        }

                        if($i==$count)
                            $msg = _('Selected canned responses deleted successfully');
                        elseif($i>0)
                            $warn="$i "._("of")." $count "._("selected canned responses deleted");
                        elseif(!$errors['err'])
                            $errors['err'] = _('Unable to delete selected canned responses');
                        break;
                    default:
                        $errors['err']=_('Unknown command');
                }
            }
            break;
        default:
            $errors['err']=_('Unknown action');
            break;
    }
}

$page='cannedresponses.inc.php';
if($canned || ($_REQUEST['a'] && !strcasecmp($_REQUEST['a'],'add')))
    $page='cannedresponse.inc.php';

$nav->setTabActive('kbase');
require(STAFFINC_DIR.'header.inc.php');
require(STAFFINC_DIR.$page);
include(STAFFINC_DIR.'footer.inc.php');
?>
