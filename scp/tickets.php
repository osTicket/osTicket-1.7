<?php
/*************************************************************************
    tickets.php

    Handles all tickets related actions.

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/

require('staff.inc.php');
require_once(INCLUDE_DIR.'class.ticket.php');
require_once(INCLUDE_DIR.'class.dept.php');
require_once(INCLUDE_DIR.'class.filter.php');
require_once(INCLUDE_DIR.'class.canned.php');

$page='';
$ticket=null; //clean start.
//LOCKDOWN...See if the id provided is actually valid and if the user has access.
if($_REQUEST['id']) {
    if(!($ticket=Ticket::lookup($_REQUEST['id'])))
         $errors['err']=__('Unknown or invalid ticket ID');
    elseif(!$ticket->checkStaffAccess($thisstaff)) {
        $errors['err']=__('Access denied. Contact admin if you believe this is in error');
        $ticket=null; //Clear ticket obj.
    }
}
//At this stage we know the access status. we can process the post.
if($_POST && !$errors):

    if($ticket && $ticket->getId()) {
        //More coffee please.
        $errors=array();
        $lock=$ticket->getLock(); //Ticket lock if any
        $statusKeys=array('open'=>'Open','Reopen'=>'Open','Close'=>'Closed');
        switch(strtolower($_POST['a'])):
        case 'reply':
            if(!$thisstaff->canPostReply())
                $errors['err'] = __('Action denied. Contact admin for access');
            else {

                if(!$_POST['response'])
                    $errors['response']=__('Response required');
                //Use locks to avoid double replies
                if($lock && $lock->getStaffId()!=$thisstaff->getId())
                    $errors['err']=__('Action Denied. Ticket is locked by someone else!');

                //Make sure the email is not banned
                if(!$errors['err'] && TicketFilter::isBanned($ticket->getEmail()))
                    $errors['err']=__('Email is in banlist. Must be removed to reply.');
            }

            $wasOpen =($ticket->isOpen());

            //If no error...do the do.
            $vars = $_POST;
            if(!$errors && $_FILES['attachments'])
                $vars['files'] = AttachmentFile::format($_FILES['attachments']);

            if(!$errors && ($response=$ticket->postReply($vars, $errors, isset($_POST['emailreply'])))) {
                $msg=__('Reply posted successfully');
                $ticket->reload();
                if($ticket->isClosed() && $wasOpen)
                    $ticket=null;

            } elseif(!$errors['err']) {
                $errors['err']=__('Unable to post the reply. Correct the errors below and try again!');
            }
            break;
        case 'transfer': /** Transfer ticket **/
            //Check permission
            if(!$thisstaff->canTransferTickets())
                $errors['err']=$errors['transfer'] = __('Action Denied. You are not allowed to transfer tickets.');
            else {

                //Check target dept.
                if(!$_POST['deptId'])
                    $errors['deptId'] = __('Select department');
                elseif($_POST['deptId']==$ticket->getDeptId())
                    $errors['deptId'] = __('Ticket already in the department');
                elseif(!($dept=Dept::lookup($_POST['deptId'])))
                    $errors['deptId'] = __('Unknown or invalid department');

                //Transfer message - required.
                if(!$_POST['transfer_comments'])
                    $errors['transfer_comments'] = __('Transfer comments required');
                elseif(strlen($_POST['transfer_comments'])<5)
                    $errors['transfer_comments'] = __('Transfer comments too short!');

                //If no errors - them attempt the transfer.
                if(!$errors && $ticket->transfer($_POST['deptId'], $_POST['transfer_comments'])) {
                    $msg = sprintf(__('Ticket transferred successfully to %s'),$ticket->getDeptName());
                    //Check to make sure the staff still has access to the ticket
                    if(!$ticket->checkStaffAccess($thisstaff))
                        $ticket=null;

                } elseif(!$errors['transfer']) {
                    $errors['err'] = __('Unable to complete the ticket transfer');
                    $errors['transfer']=__('Correct the error(s) below and try again!');
                }
            }
            break;
        case 'assign':

             if(!$thisstaff->canAssignTickets())
                 $errors['err']=$errors['assign'] = __('Action Denied. You are not allowed to assign/reassign tickets.');
             else {

                 $id = preg_replace("/[^0-9]/", "",$_POST['assignId']);
                 $claim = (is_numeric($_POST['assignId']) && $_POST['assignId']==$thisstaff->getId());

                 if(!$_POST['assignId'] || !$id)
                     $errors['assignId'] = __('Select assignee');
                 elseif($_POST['assignId'][0]!='s' && $_POST['assignId'][0]!='t' && !$claim)
                     $errors['assignId']=__('Invalid assignee ID - get technical support');
                 elseif($ticket->isAssigned()) {
                     if($_POST['assignId'][0]=='s' && $id==$ticket->getStaffId())
                         $errors['assignId']=__('Ticket already assigned to the staff.');
                     elseif($_POST['assignId'][0]=='t' && $id==$ticket->getTeamId())
                         $errors['assignId']=__('Ticket already assigned to the team.');
                 }

                 //Comments are not required on self-assignment (claim)
                 if($claim && !$_POST['assign_comments'])
                     $_POST['assign_comments'] = sprintf(__('Ticket claimed by %s'),$thisstaff->getName());
                 elseif(!$_POST['assign_comments'])
                     $errors['assign_comments'] = __('Assignment comments required');
                 elseif(strlen($_POST['assign_comments'])<5)
                         $errors['assign_comments'] = __('Comment too short');

                 if(!$errors && $ticket->assign($_POST['assignId'], $_POST['assign_comments'], !$claim)) {
                     if($claim) {
                         $msg = __('Ticket is NOW assigned to you!');
                     } else {
                         $msg=sprintf(__('Ticket assigned successfully to %s'), $ticket->getAssigned());
                         TicketLock::removeStaffLocks($thisstaff->getId(), $ticket->getId());
                         $ticket=null;
                     }
                 } elseif(!$errors['assign']) {
                     $errors['err'] = __('Unable to complete the ticket assignment');
                     $errors['assign'] = __('Correct the error(s) below and try again!');
                 }
             }
            break;
        case 'postnote': /* Post Internal Note */
            //Make sure the staff can set desired state
            if($_POST['state']) {
                if($_POST['state']=='closed' && !$thisstaff->canCloseTickets())
                    $errors['state'] = __("You don't have permission to close tickets");
                elseif(in_array($_POST['state'], array('overdue', 'notdue', 'unassigned'))
                        && (!($dept=$ticket->getDept()) || !$dept->isManager($thisstaff)))
                    $errors['state'] = __("You don't have permission to set the state");
            }

            $wasOpen = ($ticket->isOpen());

            $vars = $_POST;
            if($_FILES['attachments'])
                $vars['files'] = AttachmentFile::format($_FILES['attachments']);

            if(($note=$ticket->postNote($vars, $errors, $thisstaff))) {

                $msg=__('Internal note posted successfully');
                if($wasOpen && $ticket->isClosed())
                    $ticket = null; //Going back to main listing.
            } else {

                if(!$errors['err'])
                    $errors['err'] = __('Unable to post internal note - missing or invalid data.');

                $errors['postnote'] = __('Unable to post the note. Correct the error(s) below and try again!');
            }
            break;
        case 'edit':
        case 'update':
            if(!$ticket || !$thisstaff->canEditTickets())
                $errors['err']=__('Perm. Denied. You are not allowed to edit tickets');
            elseif($ticket->update($_POST,$errors)) {
                $msg=__('Ticket updated successfully');
                $_REQUEST['a'] = null; //Clear edit action - going back to view.
                //Check to make sure the staff STILL has access post-update (e.g dept change).
                if(!$ticket->checkStaffAccess($thisstaff))
                    $ticket=null;
            } elseif(!$errors['err']) {
                $errors['err']=__('Unable to update the ticket. Correct the errors below and try again!');
            }
            break;
        case 'process':
            switch(strtolower($_POST['do'])):
                case 'close':
                    if(!$thisstaff->canCloseTickets()) {
                        $errors['err'] = __('Perm. Denied. You are not allowed to close tickets.');
                    } elseif($ticket->isClosed()) {
                        $errors['err'] = __('Ticket is already closed!');
                    } elseif($ticket->close()) {
                        $msg=sprintf(__('Ticket #%d status set to CLOSED'),$ticket->getExtId());
                        //Log internal note
                        if($_POST['ticket_status_notes'])
                            $note = $_POST['ticket_status_notes'];
                        else
                            $note=__('Ticket closed (without comments)');

                        $ticket->logNote(__('Ticket Closed'), $note, $thisstaff);

                        //Going back to main listing.
                        TicketLock::removeStaffLocks($thisstaff->getId(), $ticket->getId());
                        $page=$ticket=null;

                    } else {
                        $errors['err']=__('Problems closing the ticket. Try again');
                    }
                    break;
                case 'reopen':
                    //if staff can close or create tickets ...then assume they can reopen.
                    if(!$thisstaff->canCloseTickets() && !$thisstaff->canCreateTickets()) {
                        $errors['err']=__('Perm. Denied. You are not allowed to reopen tickets.');
                    } elseif($ticket->isOpen()) {
                        $errors['err'] = __('Ticket is already open!');
                    } elseif($ticket->reopen()) {
                        $msg=__('Ticket REOPENED');

                        if($_POST['ticket_status_notes'])
                            $note = $_POST['ticket_status_notes'];
                        else
                            $note=__('Ticket reopened (without comments)');

                        $ticket->logNote(__('Ticket Reopened'), $note, $thisstaff);

                    } else {
                        $errors['err']=__('Problems reopening the ticket. Try again');
                    }
                    break;
                case 'release':
                    if(!$ticket->isAssigned() || !($assigned=$ticket->getAssigned())) {
                        $errors['err'] = __('Ticket is not assigned!');
                    } elseif($ticket->release()) {
                        $msg=sprintf(__('Ticket released (unassigned) from %1$s by %2$s)'),$assigned,$thisstaff->getName());
                        $ticket->logActivity(__('Ticket unassigned'),$msg);
                    } else {
                        $errors['err'] = __('Problems releasing the ticket. Try again');
                    }
                    break;
                case 'claim':
                    if(!$thisstaff->canAssignTickets()) {
                        $errors['err'] = __('Perm. Denied. You are not allowed to assign/claim tickets.');
                    } elseif(!$ticket->isOpen()) {
                        $errors['err'] = __('Only open tickets can be assigned');
                    } elseif($ticket->isAssigned()) {
                        $errors['err'] = sprintf(__('Ticket is already assigned to %s'),$ticket->getAssigned());
                    } elseif($ticket->assignToStaff($thisstaff->getId(), (sprintf(__('Ticket claimed by %s'),$thisstaff->getName())), false)) {
                        $msg = __('Ticket is now assigned to you!');
                    } else {
                        $errors['err'] = __('Problems assigning the ticket. Try again');
                    }
                    break;
                case 'overdue':
                    $dept = $ticket->getDept();
                    if(!$dept || !$dept->isManager($thisstaff)) {
                        $errors['err']=__('Perm. Denied. You are not allowed to flag tickets overdue');
                    } elseif($ticket->markOverdue()) {
                        $msg=sprintf(__('Ticket flagged as overdue by %s'),$thisstaff->getName());
                        $ticket->logActivity(__('Ticket Marked Overdue'),$msg);
                    } else {
                        $errors['err']=__('Problems marking the the ticket overdue. Try again');
                    }
                    break;
                case 'answered':
                    $dept = $ticket->getDept();
                    if(!$dept || !$dept->isManager($thisstaff)) {
                        $errors['err']=__('Perm. Denied. You are not allowed to flag tickets');
                    } elseif($ticket->markAnswered()) {
                        $msg=sprintf(__('Ticket flagged as answered by %s'),$thisstaff->getName());
                        $ticket->logActivity(__('Ticket Marked Answered'),$msg);
                    } else {
                        $errors['err']=__('Problems marking the the ticket answered. Try again');
                    }
                    break;
                case 'unanswered':
                    $dept = $ticket->getDept();
                    if(!$dept || !$dept->isManager($thisstaff)) {
                        $errors['err']=__('Perm. Denied. You are not allowed to flag tickets');
                    } elseif($ticket->markUnAnswered()) {
                        $msg=sprintf(__('Ticket flagged as unanswered by %s'),$thisstaff->getName());
                        $ticket->logActivity(__('Ticket Marked Unanswered'),$msg);
                    } else {
                        $errors['err']=__('Problems marking the the ticket unanswered. Try again');
                    }
                    break;
                case 'banemail':
                    if(!$thisstaff->canBanEmails()) {
                        $errors['err']=__('Perm. Denied. You are not allowed to ban emails');
                    } elseif(BanList::includes($ticket->getEmail())) {
                        $errors['err']=__('Email already in banlist');
                    } elseif(Banlist::add($ticket->getEmail(),$thisstaff->getName())) {
                        $msg=sprintf(__('Email %s added to banlist'),$ticket->getEmail());
                    } else {
                        $errors['err']=__('Unable to add the email to banlist');
                    }
                    break;
                case 'unbanemail':
                    if(!$thisstaff->canBanEmails()) {
                        $errors['err'] = __('Perm. Denied. You are not allowed to remove emails from banlist.');
                    } elseif(Banlist::remove($ticket->getEmail())) {
                        $msg = __('Email removed from banlist');
                    } elseif(!BanList::includes($ticket->getEmail())) {
                        $warn = __('Email is not in the banlist');
                    } else {
                        $errors['err']=__('Unable to remove the email from banlist. Try again.');
                    }
                    break;
                case 'delete': // Dude what are you trying to hide? bad customer support??
                    if(!$thisstaff->canDeleteTickets()) {
                        $errors['err']=__('Perm. Denied. You are not allowed to DELETE tickets!!');
                    } elseif($ticket->delete()) {
                        $msg=sprintf(__('Ticket #%d deleted successfully'),$ticket->getNumber());
                        //Log a debug note
                        $ost->logDebug(sprintf(__('Ticket #%d deleted'),$ticket->getNumber()),
                                sprintf(__('Ticket #%1$s deleted by %2$s'),
                                    $ticket->getNumber(), $thisstaff->getName())
                                );
                        $ticket=null; //clear the object.
                    } else {
                        $errors['err']=__('Problems deleting the ticket. Try again');
                    }
                    break;
                default:
                    $errors['err']=__('You must select action to perform');
            endswitch;
            break;
        default:
            $errors['err']=__('Unknown action');
        endswitch;
        if($ticket && is_object($ticket))
            $ticket->reload();//Reload ticket info following post processing
    }elseif($_POST['a']) {

        switch($_POST['a']) {
            case 'mass_process':
                if(!$thisstaff->canManageTickets())
                    $errors['err']=__('You do not have permission to mass manage tickets. Contact admin for such access');
                elseif(!$_POST['tids'] || !is_array($_POST['tids']))
                    $errors['err']=__('No tickets selected. You must select at least one ticket.');
                else {
                    $count=count($_POST['tids']);
                    $i = 0;
                    switch(strtolower($_POST['do'])) {
                        case 'reopen':
                            if($thisstaff->canCloseTickets() || $thisstaff->canCreateTickets()) {
                                $note=sprintf(__('Ticket reopened by %s'),$thisstaff->getName());
                                foreach($_POST['tids'] as $k=>$v) {
                                    if(($t=Ticket::lookup($v)) && $t->isClosed() && @$t->reopen()) {
                                        $i++;
                                        $t->logNote(__('Ticket Reopened'), $note, $thisstaff);
                                    }
                                }

                                if($i==$count)
                                    $msg = sprintf(__('Selected tickets %d reopened successfully'),$i);
                                elseif($i)
                                    $warn = sprintf(__('%1$d of %2$d selected tickets reopened'),$i, $count);
                                else
                                    $errors['err'] = __('Unable to reopen selected tickets');
                            } else {
                                $errors['err'] = __('You do not have permission to reopen tickets');
                            }
                            break;
                        case 'close':
                            if($thisstaff->canCloseTickets()) {
                                $note=sprintf(__('Ticket closed without response by %s'),$thisstaff->getName());
                                foreach($_POST['tids'] as $k=>$v) {
                                    if(($t=Ticket::lookup($v)) && $t->isOpen() && @$t->close()) {
                                        $i++;
                                        $t->logNote(__('Ticket Closed'), $note, $thisstaff);
                                    }
                                }

                                if($i==$count)
                                    $msg =sprintf(__('Selected tickets (%d) closed successfully'),$i);
                                elseif($i)
                                    $warn = sprintf(__('%1$d of %2$d selected tickets closed'),$i, $count);
                                else
                                    $errors['err'] = __('Unable to close selected tickets');
                            } else {
                                $errors['err'] = __('You do not have permission to close tickets');
                            }
                            break;
                        case 'mark_overdue':
                            $note=sprintf(__('Ticket flagged as overdue by %s'),$thisstaff->getName());
                            foreach($_POST['tids'] as $k=>$v) {
                                if(($t=Ticket::lookup($v)) && !$t->isOverdue() && $t->markOverdue()) {
                                    $i++;
                                    $t->logNote(__('Ticket Marked Overdue'), $note, $thisstaff);
                                }
                            }

                            if($i==$count)
                                $msg = sprintf(__('Selected tickets (%d) marked overdue'), $i);
                            elseif($i)
                                $warn = sprintf(__('%1$d of %2$d selected tickets marked overdue'), $i, $count);
                            else
                                $errors['err'] = __('Unable to flag selected tickets as overdue');
                            break;
                        case 'delete':
                            if($thisstaff->canDeleteTickets()) {
                                foreach($_POST['tids'] as $k=>$v) {
                                    if(($t=Ticket::lookup($v)) && @$t->delete()) $i++;
                                }

                                //Log a warning
                                if($i) {
                                    $log = sprintf(__('%1$s (%2$s) just deleted %3$d ticket(s)'),
                                            $thisstaff->getName(), $thisstaff->getUserName(), $i);
                                    $ost->logWarning(__('Tickets deleted'), $log, false);

                                }

                                if($i==$count)
                                    $msg = sprintf(__('Selected tickets (%d) deleted successfully'),$i);
                                elseif($i)
                                    $warn = sprintf(__('%1$d of %2$d selected tickets deleted'),$i, $count);
                                else
                                    $errors['err'] = __('Unable to delete selected tickets');
                            } else {
                                $errors['err'] = __('You do not have permission to delete tickets');
                            }
                            break;
                        default:
                            $errors['err']=__('Unknown or unsupported action - get technical help');
                    }
                }
                break;
            case 'open':
                $ticket=null;
                if(!$thisstaff || !$thisstaff->canCreateTickets()) {
                     $errors['err']=__('You do not have permission to create tickets. Contact admin for such access');
                } else {
                    $vars = $_POST;
                    if($_FILES['attachments'])
                        $vars['files'] = AttachmentFile::format($_FILES['attachments']);

                    if(($ticket=Ticket::open($vars, $errors))) {
                        $msg=__('Ticket created successfully');
                        $_REQUEST['a']=null;
                        if(!$ticket->checkStaffAccess($thisstaff) || $ticket->isClosed())
                            $ticket=null;
                    } elseif(!$errors['err']) {
                        $errors['err']=__('Unable to create the ticket. Correct the error(s) and try again');
                    }
                }
                break;
        }
    }
    if(!$errors)
        $thisstaff ->resetStats(); //We'll need to reflect any changes just made!
endif;

/*... Quick stats ...*/
$stats= $thisstaff->getTicketsStats();

//Navigation
$nav->setTabActive('tickets');
if($cfg->showAnsweredTickets()) {
    $nav->addSubMenu(array('desc'=>__('Open').' ('.number_format($stats['open']+$stats['answered']).')',
                            'title'=>__('Open Tickets'),
                            'href'=>'tickets.php',
                            'iconclass'=>'Ticket'),
                        (!$_REQUEST['status'] || $_REQUEST['status']=='open'));
} else {

    if($stats) {
        $nav->addSubMenu(array('desc'=>__('Open').' ('.number_format($stats['open']).')',
                               'title'=>__('Open Tickets'),
                               'href'=>'tickets.php',
                               'iconclass'=>'Ticket'),
                            (!$_REQUEST['status'] || $_REQUEST['status']=='open'));
    }

    if($stats['answered']) {
        $nav->addSubMenu(array('desc'=>__('Answered').' ('.number_format($stats['answered']).')',
                               'title'=>__('Answered Tickets'),
                               'href'=>'tickets.php?status=answered',
                               'iconclass'=>'answeredTickets'),
                            ($_REQUEST['status']=='answered'));
    }
}

if($stats['assigned']) {
    if(!$ost->getWarning() && $stats['assigned']>10)
        $ost->setWarning(sprintf(__('%d tickets assigned to you! Do something about it!'),$stats['assigned']));

    $nav->addSubMenu(array('desc'=>__('My Tickets').' ('.number_format($stats['assigned']).')',
                           'title'=>__('Assigned Tickets'),
                           'href'=>'tickets.php?status=assigned',
                           'iconclass'=>'assignedTickets'),
                        ($_REQUEST['status']=='assigned'));
}

if($stats['overdue']) {
    $nav->addSubMenu(array('desc'=>__('Overdue').' ('.number_format($stats['overdue']).')',
                           'title'=>__('Stale Tickets'),
                           'href'=>'tickets.php?status=overdue',
                           'iconclass'=>'overdueTickets'),
                        ($_REQUEST['status']=='overdue'));

    if(!$sysnotice && $stats['overdue']>10)
        $sysnotice=sprintf(__('%d overdue tickets!'),$stats['overdue']);
}

if($thisstaff->showAssignedOnly() && $stats['closed']) {
    $nav->addSubMenu(array('desc'=>__('My Closed Tickets').' ('.number_format($stats['closed']).')',
                           'title'=>__('My Closed Tickets'),
                           'href'=>'tickets.php?status=closed',
                           'iconclass'=>'closedTickets'),
                        ($_REQUEST['status']=='closed'));
} else {

    $nav->addSubMenu(array('desc'=>__('Closed Tickets').' ('.number_format($stats['closed']).')',
                           'title'=>__('Closed Tickets'),
                           'href'=>'tickets.php?status=closed',
                           'iconclass'=>'closedTickets'),
                        ($_REQUEST['status']=='closed'));
}

if($thisstaff->canCreateTickets()) {
    $nav->addSubMenu(array('desc'=>__('New Ticket'),
                           'href'=>'tickets.php?a=open',
                           'iconclass'=>'newTicket'),
                        ($_REQUEST['a']=='open'));
}


$inc = 'tickets.inc.php';
if($ticket) {
    $ost->setPageTitle(sprintf(__('Ticket #%d'),$ticket->getNumber()));
    $nav->setActiveSubMenu(-1);
    $inc = 'ticket-view.inc.php';
    if($_REQUEST['a']=='edit' && $thisstaff->canEditTickets())
        $inc = 'ticket-edit.inc.php';
    elseif($_REQUEST['a'] == 'print' && !$ticket->pdfExport($_REQUEST['psize'], $_REQUEST['notes']))
        $errors['err'] = __('Internal error: Unable to export the ticket to PDF for print.');
} else {
	$inc = 'tickets.inc.php';
    if($_REQUEST['a']=='open' && $thisstaff->canCreateTickets())
        $inc = 'ticket-open.inc.php';
    elseif($_REQUEST['a'] == 'export') {
        require_once(INCLUDE_DIR.'class.export.php');
        $ts = strftime('%Y%m%d');
        if (!($token=$_REQUEST['h']))
            $errors['err'] = __('Query token required');
        elseif (!($query=$_SESSION['search_'.$token]))
            $errors['err'] = __('Query token not found');
        elseif (!Export::saveTickets($query, "tickets-$ts.csv", 'csv'))
            $errors['err'] = __('Internal error: Unable to dump query results');
    }

    //Clear active submenu on search with no status
    if($_REQUEST['a']=='search' && !$_REQUEST['status'])
        $nav->setActiveSubMenu(-1);

    //set refresh rate if the user has it configured
    if(!$_POST && !$_REQUEST['a'] && ($min=$thisstaff->getRefreshRate()))
        $ost->addExtraHeader('<meta http-equiv="refresh" content="'.($min*60).'" />');
}

require_once(STAFFINC_DIR.'header.inc.php');
require_once(STAFFINC_DIR.$inc);
require_once(STAFFINC_DIR.'footer.inc.php');
?>
