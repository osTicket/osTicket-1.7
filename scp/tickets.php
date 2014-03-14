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
require_once(INCLUDE_DIR.'languages/language_control/languages_processor.php');


$page='';
$ticket=null; //clean start.
//LOCKDOWN...See if the id provided is actually valid and if the user has access.
if($_REQUEST['id']) {
    if(!($ticket=Ticket::lookup($_REQUEST['id'])))
         $errors['err']=lang('invalid_ticket_id');
    elseif(!$ticket->checkStaffAccess($thisstaff)) {
        $errors['err']=lang('cont_admin_if_error');
        $ticket=null; //Clear ticket obj.
    }
}
//At this stage we know the access status. we can process the post.
if($_POST && !$errors):

    if($ticket && $ticket->getId()) {
        //More coffee please.
        $errors=array();
        $lock=$ticket->getLock(); //Ticket lock if any
        $statusKeys=array('open'=>lang('open'),'Reopen'=>lang('open'),'Close'=>lang('closed'));
        switch(strtolower($_POST['a'])):
        case 'reply':
            if(!$thisstaff->canPostReply())
                $errors['err'] = lang('cont_admin_to_acces');
            else {

                if(!$_POST['response'])
                    $errors['response']=lang('required_response');
                //Use locks to avoid double replies
                if($lock && $lock->getStaffId()!=$thisstaff->getId())
                    $errors['err']=lang('act_denied_t_locket');

                //Make sure the email is not banned
                if(!$errors['err'] && TicketFilter::isBanned($ticket->getEmail()))
                    $errors['err']=lang('email_removed_to_reply');
            }

            $wasOpen =($ticket->isOpen());

            //If no error...do the do.
            $vars = $_POST;
            if(!$errors && $_FILES['attachments'])
                $vars['files'] = AttachmentFile::format($_FILES['attachments']);

            if(!$errors && ($response=$ticket->postReply($vars, $errors, isset($_POST['emailreply'])))) {
                $msg=lang('reply_posted_success');
                $ticket->reload();
                if($ticket->isClosed() && $wasOpen)
                    $ticket=null;

            } elseif(!$errors['err']) {
                $errors['err']=lang('cant_post_reply').' '.lang('correct_errors');
            }
            break;
        case 'transfer': /** Transfer ticket **/
            //Check permission
            if(!$thisstaff->canTransferTickets())
                $errors['err']=$errors['transfer'] = lang('not_transfer_ticket');
            else {

                //Check target dept.
                if(!$_POST['deptId'])
                    $errors['deptId'] = lang('select_department');
                elseif($_POST['deptId']==$ticket->getDeptId())
                    $errors['deptId'] = lang('ticket_in_dept');
                elseif(!($dept=Dept::lookup($_POST['deptId'])))
                    $errors['deptId'] = lang('invalid_dep');

                //Transfer message - required.
                if(!$_POST['transfer_comments'])
                    $errors['transfer_comments'] = lang('transfer_comments_req');
                elseif(strlen($_POST['transfer_comments'])<5)
                    $errors['transfer_comments'] = lang('transfer_comments_shor');

                //If no errors - them attempt the transfer.
                if(!$errors && $ticket->transfer($_POST['deptId'], $_POST['transfer_comments'])) {
                    $msg = lang('ticket_transf_succ').' '.$ticket->getDeptName();
                    //Check to make sure the staff still has access to the ticket
                    if(!$ticket->checkStaffAccess($thisstaff))
                        $ticket=null;

                } elseif(!$errors['transfer']) {
                    $errors['err'] = lang('cant_complete_transfer');
                    $errors['transfer']=lang('correct_errors');
                }
            }
            break;
        case 'assign':

             if(!$thisstaff->canAssignTickets())
                 $errors['err']=$errors['assign'] = lang('cant_assign_ticket');
             else {

                 $id = preg_replace("/[^0-9]/", "",$_POST['assignId']);
                 $claim = (is_numeric($_POST['assignId']) && $_POST['assignId']==$thisstaff->getId());

                 if(!$_POST['assignId'] || !$id)
                     $errors['assignId'] = lang('select_assignee');
                 elseif($_POST['assignId'][0]!='s' && $_POST['assignId'][0]!='t' && !$claim)
                     $errors['assignId']=lang('invalid_assigned_id');
                 elseif($ticket->isAssigned()) {
                     if($_POST['assignId'][0]=='s' && $id==$ticket->getStaffId())
                         $errors['assignId']=lang('ticket_assigned_staf');
                     elseif($_POST['assignId'][0]=='t' && $id==$ticket->getTeamId())
                         $errors['assignId']=lang('ticket_assigned_team');
                 }

                 //Comments are not required on self-assignment (claim)
                 if($claim && !$_POST['assign_comments'])
                     $_POST['assign_comments'] = 'Ticket claimed by '.$thisstaff->getName();
                 elseif(!$_POST['assign_comments'])
                     $errors['assign_comments'] = lang('assig_comment_req');
                 elseif(strlen($_POST['assign_comments'])<5)
                         $errors['assign_comments'] = lang('comment_to_short');

                 if(!$errors && $ticket->assign($_POST['assignId'], $_POST['assign_comments'], !$claim)) {
                     if($claim) {
                         $msg = lang('ticket_assig_to_you');
                     } else {
                         $msg=lang('ticket_assigned_to').' '.$ticket->getAssigned();
                         TicketLock::removeStaffLocks($thisstaff->getId(), $ticket->getId());
                         $ticket=null;
                     }
                 } elseif(!$errors['assign']) {
                     $errors['err'] = lang('unnab_assign_ticket');
                     $errors['assign'] = lang('correct_errors');
                 }
             }
            break;
        case 'postnote': /* Post Internal Note */
            //Make sure the staff can set desired state
            if($_POST['state']) {
                if($_POST['state']=='closed' && !$thisstaff->canCloseTickets())
                    $errors['state'] = lang("dont_permit_cs_tick");
                elseif(in_array($_POST['state'], array('overdue', 'notdue', 'unassigned'))
                        && (!($dept=$ticket->getDept()) || !$dept->isManager($thisstaff)))
                    $errors['state'] = lang("cant_set_state");
            }

            $wasOpen = ($ticket->isOpen());

            $vars = $_POST;
            if($_FILES['attachments'])
                $vars['files'] = AttachmentFile::format($_FILES['attachments']);

            if(($note=$ticket->postNote($vars, $errors, $thisstaff))) {

                $msg=lang('note_posted');
                if($wasOpen && $ticket->isClosed())
                    $ticket = null; //Going back to main listing.

            } else {

                if(!$errors['err'])
                    $errors['err'] = lang('cant_post_inter_note');

                $errors['postnote'] = lang('cant_post_note').' '.lang('correct_errors');
            }
            break;
        case 'edit':
        case 'update':
            if(!$ticket || !$thisstaff->canEditTickets())
                $errors['err']=lang('perm_denied');
            elseif($ticket->update($_POST,$errors)) {
                $msg=lang('ticket_updated');
                $_REQUEST['a'] = null; //Clear edit action - going back to view.
                //Check to make sure the staff STILL has access post-update (e.g dept change).
                if(!$ticket->checkStaffAccess($thisstaff))
                    $ticket=null;
            } elseif(!$errors['err']) {
                $errors['err']=lang('cant_update_tick').' '.lang('correct_errors');
            }
            break;
        case 'process':
            switch(strtolower($_POST['do'])):
                case 'close':
                    if(!$thisstaff->canCloseTickets()) {
                        $errors['err'] = lang('not_allowed_c_tick');
                    } elseif($ticket->isClosed()) {
                        $errors['err'] = lang('ticket_is_closed');
                    } elseif($ticket->close()) {
                        $msg='Ticket #'.$ticket->getExtId().' '.lang('stat_set_to_closed');
                        //Log internal note
                        if($_POST['ticket_status_notes'])
                            $note = $_POST['ticket_status_notes'];
                        else
                            $note=lang('ticket_without_com');

                        $ticket->logNote(lang('ticket_closed_only'), $note, $thisstaff);

                        //Going back to main listing.
                        TicketLock::removeStaffLocks($thisstaff->getId(), $ticket->getId());
                        $page=$ticket=null;

                    } else {
                        $errors['err']=lang('problem_clos_tick');
                    }
                    break;
                case 'reopen':
                    //if staff can close or create tickets ...then assume they can reopen.
                    if(!$thisstaff->canCloseTickets() && !$thisstaff->canCreateTickets()) {
                        $errors['err']=lang('not_allowed_r_tick');
                    } elseif($ticket->isOpen()) {
                        $errors['err'] = lang('ticket_is_open');
                    } elseif($ticket->reopen()) {
                        $msg=lang('ticket_reopen');

                        if($_POST['ticket_status_notes'])
                            $note = $_POST['ticket_status_notes'];
                        else
                            $note=lang('ticket_ro_no_comt');

                        $ticket->logNote(lang('ticket_reopen'), $note, $thisstaff);

                    } else {
                        $errors['err']=lang('problem_reopen_tic');
                    }
                    break;
                case 'release':
                    if(!$ticket->isAssigned() || !($assigned=$ticket->getAssigned())) {
                        $errors['err'] = lang('ticket_not_assig');
                    } elseif($ticket->release()) {
                        $msg=lang('ticket_released').' '.$assigned;
                        $ticket->logActivity(lang('ticket_unassigned'),$msg.' '.lang('by').' '.$thisstaff->getName());
                    } else {
                        $errors['err'] = lang('cant_release_tick');
                    }
                    break;
                case 'claim':
                    if(!$thisstaff->canAssignTickets()) {
                        $errors['err'] = lang('cant_claim_ticket');
                    } elseif(!$ticket->isOpen()) {
                        $errors['err'] = lang('o_ticket_can_assg');
                    } elseif($ticket->isAssigned()) {
                        $errors['err'] = lang('ticket_assig_to').' '.$ticket->getAssigned();
                    } elseif($ticket->assignToStaff($thisstaff->getId(), (lang('ticket_claimed_by').' '.$thisstaff->getName()), false)) {
                        $msg = lang('ticket_not_to_you');
                    } else {
                        $errors['err'] = lang('problem_asign_tic');
                    }
                    break;
                case 'overdue':
                    $dept = $ticket->getDept();
                    if(!$dept || !$dept->isManager($thisstaff)) {
                        $errors['err']=lang('not_allowed_f_tick');
                    } elseif($ticket->markOverdue()) {
                        $msg=lang('ticket_ovedue');
                        $ticket->logActivity(lang('marked_overdue'),($msg.' '.lang('by').' '.$thisstaff->getName()));
                    } else {
                        $errors['err']=lang('prob_marked_overdue');
                    }
                    break;
                case 'answered':
                    $dept = $ticket->getDept();
                    if(!$dept || !$dept->isManager($thisstaff)) {
                        $errors['err']=lang('not_p_flag_ticket');
                    } elseif($ticket->markAnswered()) {
                        $msg=lang('ticket_answered');
                        $ticket->logActivity(lang('ticket_m_answered'),($msg.' '.lang('by').' '.$thisstaff->getName()));
                    } else {
                        $errors['err']=lang('prob_mark_t_answer');
                    }
                    break;
                case 'unanswered':
                    $dept = $ticket->getDept();
                    if(!$dept || !$dept->isManager($thisstaff)) {
                        $errors['err']=lang('not_p_flag_ticket');
                    } elseif($ticket->markUnAnswered()) {
                        $msg=lang('ticket_unanswered');
                        $ticket->logActivity(lang('ticket_m_unanswered'),($msg.' '.lang('by').' '.$thisstaff->getName()));
                    } else {
                        $errors['err']=lang('prob_mark_unanswer');
                    }
                    break;
                case 'banemail':
                    if(!$thisstaff->canBanEmails()) {
                        $errors['err']=lang('cant_ban_emails');
                    } elseif(BanList::includes($ticket->getEmail())) {
                        $errors['err']=lang('email_in_ban');
                    } elseif(Banlist::add($ticket->getEmail(),$thisstaff->getName())) {
                        $msg='Email ('.$ticket->getEmail().') '.lang('added_to_ban_list');
                    } else {
                        $errors['err']=lang('cant_add_email_ban');
                    }
                    break;
                case 'unbanemail':
                    if(!$thisstaff->canBanEmails()) {
                        $errors['err'] = lang('cant_remove_email_b');
                    } elseif(Banlist::remove($ticket->getEmail())) {
                        $msg = lang('email_removed_ban');
                    } elseif(!BanList::includes($ticket->getEmail())) {
                        $warn = lang('email_not_banlist');
                    } else {
                        $errors['err']=lang('unab_remove_email');
                    }
                    break;
                case 'delete': // Dude what are you trying to hide? bad customer support??
                    if(!$thisstaff->canDeleteTickets()) {
                        $errors['err']=lang('not_allowed_d_tick');
                    } elseif($ticket->delete()) {
                        $msg=lang('ticket').' #'.$ticket->getNumber().' '.lang('deleted_succesfully');
                        //Log a debug note
                        $ost->logDebug(lang('ticket').' #'.$ticket->getNumber().' '.lang('deleted'),
                                sprintf(lang('ticket').' #%s '.lang('deleted_by').' %s',
                                    $ticket->getNumber(), $thisstaff->getName())
                                );
                        $ticket=null; //clear the object.
                    } else {
                        $errors['err']=lang('prob_delete_tick');
                    }
                    break;
                default:
                    $errors['err']=lang('must_select_action');
            endswitch;
            break;
        default:
            $errors['err']=lang('unknown_action_only');
        endswitch;
        if($ticket && is_object($ticket))
            $ticket->reload();//Reload ticket info following post processing
    }elseif($_POST['a']) {

        switch($_POST['a']) {
            case 'mass_process':
                if(!$thisstaff->canManageTickets())
                    $errors['err']=lang('cant_mass_mang_tick');
                elseif(!$_POST['tids'] || !is_array($_POST['tids']))
                    $errors['err']=lang('select_one_ticket');
                else {
                    $count=count($_POST['tids']);
                    $i = 0;
                    switch(strtolower($_POST['do'])) {
                        case 'reopen':
                            if($thisstaff->canCloseTickets() || $thisstaff->canCreateTickets()) {
                                $note=lang('ticket_reopen_by').' '.$thisstaff->getName();
                                foreach($_POST['tids'] as $k=>$v) {
                                    if(($t=Ticket::lookup($v)) && $t->isClosed() && @$t->reopen()) {
                                        $i++;
                                        $t->logNote(lang('ticket_reopen'), $note, $thisstaff);
                                    }
                                }

                                if($i==$count)
                                    $msg = lang("selected_tickets")." ($i) ".lang('reopen_successfully');
                                elseif($i)
                                    $warn = "$i ".lang('of')." $count ".lang('select_tickets_ropen');
                                else
                                    $errors['err'] = lang('cant_reopen_ticket');
                            } else {
                                $errors['err'] = lang('dont_permit_reopen_t');
                            }
                            break;
                        case 'close':
                            if($thisstaff->canCloseTickets()) {
                                $note=lang('ticket_no_response').' '.$thisstaff->getName();
                                foreach($_POST['tids'] as $k=>$v) {
                                    if(($t=Ticket::lookup($v)) && $t->isOpen() && @$t->close()) {
                                        $i++;
                                        $t->logNote(lang('ticket_closed_only'), $note, $thisstaff);
                                    }
                                }
                                if($i==$count)
                                    $msg =lang("selected_tickets")." ($i) ".lang('closed_succesfully');
                                elseif($i)
                                    $warn = "$i ".lang('of')." $count ".lang('ticket_closed');
                                else
                                    $errors['err'] = lang('cant_close_tickets');
                            } else {
                                $errors['err'] = lang('dont_permit_close_t');
                            }
                            break;
                        case 'mark_overdue':
                            $note=lang('ticket_flagged_overd').' '.$thisstaff->getName();
                            foreach($_POST['tids'] as $k=>$v) {
                                if(($t=Ticket::lookup($v)) && !$t->isOverdue() && $t->markOverdue()) {
                                    $i++;
                                    $t->logNote(lang('marked_overdue'), $note, $thisstaff);
                                }
                            }

                            if($i==$count)
                                $msg = lang("selected_tickets")." ($i) ".lang('market_overdue');
                            elseif($i)
                                $warn = "$i ".lang('of')." $count ".lang('sticket_mark_overdue');
                            else
                                $errors['err'] = lang('cant_flag_tickets');
                            break;
                        case 'delete':
                            if($thisstaff->canDeleteTickets()) {
                                foreach($_POST['tids'] as $k=>$v) {
                                    if(($t=Ticket::lookup($v)) && @$t->delete()) $i++;
                                }

                                //Log a warning
                                if($i) {
                                    $log = sprintf('%s (%s) '.lang('just_deleted').' %d '.lang('ticket').'(s)',
                                            $thisstaff->getName(), $thisstaff->getUserName(), $i);
                                    $ost->logWarning(lang('tickets_deleted'), $log, false);

                                }

                                if($i==$count)
                                    $msg = lang("selected_tickets")." ($i) ".lang('deleted_succesfully');
                                elseif($i)
                                    $warn = "$i ".lang('of')." $count ".lang('tickets_deleted');
                                else
                                    $errors['err'] = lang('cant_delete_ticket');
                            } else {
                                $errors['err'] = lang('dont_permit_d_tick');
                            }
                            break;
                        default:
                            $errors['err']=lang('unknow_action');
                    }
                }
                break;
            case 'open':
                $ticket=null;
                if(!$thisstaff || !$thisstaff->canCreateTickets()) {
                     $errors['err']=lang('dont_permit_c_tick');
                } else {
                    $vars = $_POST;
                    if($_FILES['attachments'])
                        $vars['files'] = AttachmentFile::format($_FILES['attachments']);

                    if(($ticket=Ticket::open($vars, $errors))) {
                        $msg=lang('ticket_created');
                        $_REQUEST['a']=null;
                        if(!$ticket->checkStaffAccess($thisstaff) || $ticket->isClosed())
                            $ticket=null;
                    } elseif(!$errors['err']) {
                        $errors['err']=lang('cant_create_ticket').lang('correct_errors');
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
    $nav->addSubMenu(array('desc'=>lang('opened').' ('.number_format($stats['open']+$stats['answered']).')',
                            'title'=>lang('opened_tickets'),
                            'href'=>'tickets.php',
                            'iconclass'=>'Ticket'),
                        (!$_REQUEST['status'] || $_REQUEST['status']=='open'));
} else {

    if($stats) {
        $nav->addSubMenu(array('desc'=>lang('opened').' ('.number_format($stats['open']).')',
                               'title'=>lang('opened_tickets'),
                               'href'=>'tickets.php',
                               'iconclass'=>'Ticket'),
                            (!$_REQUEST['status'] || $_REQUEST['status']=='open'));
    }

    if($stats['answered']) {
        $nav->addSubMenu(array('desc'=>lang('answered').' ('.number_format($stats['answered']).')',
                               'title'=>lang('answered'),
                               'href'=>'tickets.php?status=answered',
                               'iconclass'=>'answeredTickets'),
                            ($_REQUEST['status']=='answered'));
    }
}

if($stats['assigned']) {
    if(!$ost->getWarning() && $stats['assigned']>10)
        $ost->setWarning($stats['assigned'].' '.lang('ticket_assign_you'));

    $nav->addSubMenu(array('desc'=>lang('my_tickets').' ('.number_format($stats['assigned']).')',
                           'title'=>lang('assigned_tickets'),
                           'href'=>'tickets.php?status=assigned',
                           'iconclass'=>'assignedTickets'),
                        ($_REQUEST['status']=='assigned'));
}

if($stats['overdue']) {
    $nav->addSubMenu(array('desc'=>lang('overdue').' ('.number_format($stats['overdue']).')',
                           'title'=>lang('stale_tickets'),
                           'href'=>'tickets.php?status=overdue',
                           'iconclass'=>'overdueTickets'),
                        ($_REQUEST['status']=='overdue'));

    if(!$sysnotice && $stats['overdue']>10)
        $sysnotice=$stats['overdue'] .' overdue tickets!';
}

if($thisstaff->showAssignedOnly() && $stats['closed']) {
    $nav->addSubMenu(array('desc'=>lang('my_closed_tickets').' ('.number_format($stats['closed']).')',
                           'title'=>lang('my_closed_tickets'),
                           'href'=>'tickets.php?status=closed',
                           'iconclass'=>'closedTickets'),
                        ($_REQUEST['status']=='closed'));
} else {

    $nav->addSubMenu(array('desc'=>lang('closed_tickets').' ('.number_format($stats['closed']).')',
                           'title'=>lang('closed_tickets'),
                           'href'=>'tickets.php?status=closed',
                           'iconclass'=>'closedTickets'),
                        ($_REQUEST['status']=='closed'));
}

if($thisstaff->canCreateTickets()) {
    $nav->addSubMenu(array('desc'=>lang('new_ticket'),
                           'href'=>'tickets.php?a=open',
                           'iconclass'=>'newTicket'),
                        ($_REQUEST['a']=='open'));
}

$inc = 'tickets.inc.php';
if($ticket) {
    $ost->setPageTitle('Ticket #'.$ticket->getNumber());
    $nav->setActiveSubMenu(-1);
    $inc = 'ticket-view.inc.php';
    if($_REQUEST['a']=='edit' && $thisstaff->canEditTickets())
        $inc = 'ticket-edit.inc.php';
    elseif($_REQUEST['a'] == 'print' && !$ticket->pdfExport($_REQUEST['psize'], $_REQUEST['notes']))
        $errors['err'] = lang('cant_export_to_pdf');
} else {
    $inc = 'tickets.inc.php';
    if($_REQUEST['a']=='open' && $thisstaff->canCreateTickets())
        $inc = 'ticket-open.inc.php';
    elseif($_REQUEST['a'] == 'export') {
        require_once(INCLUDE_DIR.'class.export.php');
        $ts = strftime('%Y%m%d');
        if (!($token=$_REQUEST['h']))
            $errors['err'] = lang('query_token_req');
        elseif (!($query=$_SESSION['search_'.$token]))
            $errors['err'] = lang('query_token_not_found');
        elseif (!Export::saveTickets($query, "tickets-$ts.csv", 'csv'))
            $errors['err'] = lang('cant_dump_query');
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
