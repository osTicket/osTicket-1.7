<?php
//Note that ticket obj is initiated in tickets.php.
if(!defined('OSTSCPINC') || !$thisstaff || !is_object($ticket) || !$ticket->getId()) die('Invalid path');

//Make sure the staff is allowed to access the page.
if(!@$thisstaff->isStaff() || !$ticket->checkStaffAccess($thisstaff)) die('Access Denied');

//Re-use the post info on error...savekeyboards.org (Why keyboard? -> some people care about objects than users!!)
$info=($_POST && $errors)?Format::input($_POST):array();

//Auto-lock the ticket if locking is enabled.. If already locked by the user then it simply renews.
if($cfg->getLockTime() && !$ticket->acquireLock($thisstaff->getId(),$cfg->getLockTime()))
    $warn.=__('Unable to obtain a lock on the ticket');

//Get the goodies.
$dept  = $ticket->getDept();  //Dept
$staff = $ticket->getStaff(); //Assigned or closed by..
$team  = $ticket->getTeam();  //Assigned team.
$sla   = $ticket->getSLA();
$lock  = $ticket->getLock();  //Ticket lock obj
$id    = $ticket->getId();    //Ticket ID.

//Useful warnings and errors the user might want to know!
if($ticket->isAssigned() && (
            ($staff && $staff->getId()!=$thisstaff->getId())
         || ($team && !$team->hasMember($thisstaff))
        ))
    $warn.='&nbsp;&nbsp;<span class="Icon assignedTicket">'.sprintf(__('Ticket is assigned to %s'),implode('/', $ticket->getAssignees())).'</span>';
if(!$errors['err'] && ($lock && $lock->getStaffId()!=$thisstaff->getId()))
    $errors['err']=sprintf(__('This ticket is currently locked by %s'),$lock->getStaffName());
if(!$errors['err'] && ($emailBanned=TicketFilter::isBanned($ticket->getEmail())))
    $errors['err']=__('Email is in banlist! Must be removed before any reply/response');

$unbannable=($emailBanned) ? BanList::includes($ticket->getEmail()) : false;

if($ticket->isOverdue())
    $warn.='&nbsp;&nbsp;<span class="Icon overdueTicket">'.__('Marked overdue!').'</span>';

?>
<table width="940" cellpadding="2" cellspacing="0" border="0">
    <tr>
        <td width="50%" class="has_bottom_border">
             <h2><a href="tickets.php?id=<?php echo $ticket->getId(); ?>" title="Reload"><i class="icon-refresh"></i> <?php echo sprintf(__('Ticket #%d'), $ticket->getExtId()); ?></a></h2>
        </td>
        <td width="50%" class="right_align has_bottom_border">
            <?php
            if($thisstaff->canBanEmails() || ($dept && $dept->isManager($thisstaff))) { ?>
            <span class="action-button" data-dropdown="#action-dropdown-more">
                <span ><i class="icon-cog"></i> <?php echo __('More');?></span>
                <i class="icon-caret-down"></i>
            </span>
            <?php
            } ?>
            <?php if($thisstaff->canDeleteTickets()) { ?>
                <a id="ticket-delete" class="action-button" href="#delete"><i class="icon-trash"></i> <?php echo __('Delete');?></a>
            <?php } ?>
            <?php 
            if($thisstaff->canCloseTickets()) {
                if($ticket->isOpen()) {?>
                <a id="ticket-close" class="action-button" href="#close"><i class="icon-remove-circle"></i> <?php echo __('Close');?></a>
                <?php
                } else { ?>
                <a id="ticket-reopen" class="action-button" href="#reopen"><i class="icon-undo"></i> <?php echo __('Reopen');?></a>
                <?php
                } ?>
            <?php 
            } ?>
            <?php 
            if($thisstaff->canEditTickets()) { ?>
                <a class="action-button" href="tickets.php?id=<?php echo $ticket->getId(); ?>&a=edit"><i class="icon-edit"></i> <?php echo __('Edit');?></a>
            <?php 
            } ?>

            <?php
            if($ticket->isOpen() && !$ticket->isAssigned() && $thisstaff->canAssignTickets()) {?>
                <a id="ticket-claim" class="action-button" href="#claim"><i class="icon-user"></i> <?php echo __('Claim');?></a>
                
            <?php
            }?>

            <a id="ticket-print" class="action-button" href="tickets.php?id=<?php echo $ticket->getId(); ?>&a=print"><i class="icon-print"></i> <?php echo __('Print');?></a>

            <div id="action-dropdown-more" class="action-dropdown anchor-right">
              <ul>
                <?php 
                if($ticket->isOpen() && ($dept && $dept->isManager($thisstaff))) {
                        
                    if($ticket->isAssigned()) { ?>
                        <li><a id="ticket-release" href="#release"><i class="icon-user"></i> <?php echo __('Release (unassign) Ticket');?></a></li>
                    <?php
                    }
                    
                    if(!$ticket->isOverdue()) { ?>
                        <li><a id="ticket-overdue" href="#overdue"><i class="icon-bell"></i> <?php echo __('Mark as Overdue');?></a></li>
                    <?php
                    }
                    
                    if($ticket->isAnswered()) { ?>
                        <li><a id="ticket-unanswered" href="#unanswered"><i class="icon-circle-arrow-left"></i> <?php echo __('Mark as Unanswered');?></a></li>
                    <?php
                    } else { ?>
                        <li><a id="ticket-answered" href="#answered"><i class="icon-circle-arrow-right"></i> <?php echo __('Mark as Answered');?></a></li>
                    <?php
                    }
                }
              
                if($thisstaff->canBanEmails()) { 
                     if(!$emailBanned) {?>
                        <li><a id="ticket-banemail" href="#banemail"><i class="icon-ban-circle"></i> <?php echo __('Ban Email');?> (<?php echo $ticket->getEmail(); ?>)</a></li>
                <?php 
                     } elseif($unbannable) { ?>
                        <li><a id="ticket-banemail" href="#unbanemail"><i class="icon-undo"></i> <?php echo __('Unban Email');?> (<?php echo $ticket->getEmail(); ?>)</a></li>
                    <?php
                     }
                }?>
              </ul>
            </div>
        </td>
    </tr>
</table>
<table class="ticket_info" cellspacing="0" cellpadding="0" width="940" border="0">
    <tr>
        <td width="50">
            <table border="0" cellspacing="" cellpadding="4" width="100%">
                <tr>
                    <th width="100"><?php echo __('Status');?>:</th>
                    <td><?php 
					switch($ticket->getStatus()) {
						case 'open':
							$results_type=__('open');
						break;
						case 'closed':
							$results_type=__('closed');
						break;
					}
					echo $results_type; ?></td>
                </tr>
                <tr>
                    <th><?php echo __('Priority');?>:</th>
                    <td><?php echo $ticket->getPriority(); ?></td>
                </tr>
                <tr>
                    <th><?php echo __('Department');?>:</th>
                    <td><?php echo Format::htmlchars($ticket->getDeptName()); ?></td>
                </tr>
                <tr>
                    <th><?php echo __('Create Date');?>:</th>
                    <td><?php echo Format::db_datetime($ticket->getCreateDate()); ?></td>
                </tr>
            </table>
        </td>
        <td width="50%">
            <table border="0" cellspacing="" cellpadding="4" width="100%">
                <tr>
                    <th width="100"><?php echo __('Name');?>:</th>
                    <td><?php echo Format::htmlchars($ticket->getName()); ?></td>
                </tr>
                <tr>
                    <th><?php echo __('Email');?>:</th>
                    <td>
                    <?php
                        echo $ticket->getEmail();
                        if(($client=$ticket->getClient())) {
                            echo sprintf('&nbsp;&nbsp;<a href="tickets.php?a=search&query=%s" title="Related Tickets" data-dropdown="#action-dropdown-stats">(<b>%d</b>)</a>',
                                    urlencode($ticket->getEmail()), $client->getNumTickets());
                        ?>
                            <div id="action-dropdown-stats" class="action-dropdown anchor-right">
                                <ul>
                                    <?php
                                    if(($open=$client->getNumOpenTickets()))
                                        echo sprintf('<li><a href="tickets.php?a=search&status=open&query=%s"><i class="icon-folder-open-alt"></i> '.__('%d Open Tickets').'</a></li>',
                                                $ticket->getEmail(), $open);
                                    if(($closed=$client->getNumClosedTickets()))
                                        echo sprintf('<li><a href="tickets.php?a=search&status=closed&query=%s"><i class="icon-folder-close-alt"></i> '.__('%d Closed Tickets').'</a></li>',
                                                $ticket->getEmail(), $closed);
                                    ?>
                                    <li><a href="tickets.php?a=search&query=<?php echo $ticket->getEmail(); ?>"><i class="icon-double-angle-right"></i> <?php echo __('All Tickets');?></a></li>
                                </u>
                            </div>
                    <?php
                        }
                    ?>
                    </td>
                </tr>
                <tr>
                    <th><?php echo __('Phone');?>:</th>
                    <td><?php echo $ticket->getPhoneNumber(); ?></td>
                </tr>
                <tr>
                    <th><?php echo __('Source');?>:</th>
                    <td><?php 
                        echo Format::htmlchars($ticket->getSource());

                        if($ticket->getIP())
                            echo '&nbsp;&nbsp; <span class="faded">('.$ticket->getIP().')</span>';

                    
                        ?>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<br>
<table class="ticket_info" cellspacing="0" cellpadding="0" width="940" border="0">
    <tr>
        <td width="50%">
            <table cellspacing="0" cellpadding="4" width="100%" border="0">
                <?php
                if($ticket->isOpen()) { ?>
                <tr>
                    <th width="100"><?php echo __('Assigned To');?>:</th>
                    <td>
                        <?php
                        if($ticket->isAssigned())
                            echo Format::htmlchars(implode('/', $ticket->getAssignees()));
                        else
                            echo '<span class="faded">&mdash; '.__('Unassigned').' &mdash;</span>';
                        ?>
                    </td>
                </tr>
                <?php
                } else { ?>
                <tr>
                    <th width="100"><?php echo __('Closed By');?>:</th>
                    <td>
                        <?php
                        if(($staff = $ticket->getStaff()))
                            echo Format::htmlchars($staff->getName());
                        else
                            echo '<span class="faded">&mdash; '.__('Unknown').' &mdash;</span>';
                        ?>
                    </td>
                </tr>
                <?php
                } ?>
                <tr>
                    <th><?php echo __('SLA Plan');?>:</th>
                    <td><?php echo $sla?Format::htmlchars($sla->getName()):'<span class="faded">&mdash; '.__('none').' &mdash;</span>'; ?></td>
                </tr>
                <?php
                if($ticket->isOpen()){ ?>
                <tr>
                    <th><?php echo __('Due Date');?>:</th>
                    <td><?php echo Format::db_datetime($ticket->getEstDueDate()); ?></td>
                </tr>
                <?php
                }else { ?>
                <tr>
                    <th><?php echo __('Close Date');?>:</th>
                    <td><?php echo Format::db_datetime($ticket->getCloseDate()); ?></td>
                </tr>
                <?php
                }
                ?>
            </table>
        </td>
        <td width="50%">
            <table cellspacing="0" cellpadding="4" width="100%" border="0">
                <tr>
                    <th width="100"><?php echo __('Help Topic');?>:</th>
                    <td><?php echo Format::htmlchars($ticket->getHelpTopic()); ?></td>
                </tr>
                <tr>
                    <th nowrap><?php echo __('Last Message');?>:</th>
                    <td><?php echo Format::db_datetime($ticket->getLastMsgDate()); ?></td>
                </tr>
                <tr>
                    <th nowrap><?php echo __('Last Response');?>:</th>
                    <td><?php echo Format::db_datetime($ticket->getLastRespDate()); ?></td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<div class="clear"></div>
<h2 style="padding:10px 0 5px 0; font-size:11pt;"><?php echo Format::htmlchars($ticket->getSubject()); ?></h2>
<?php
$tcount = $ticket->getThreadCount();
if($cfg->showNotesInline())
    $tcount+= $ticket->getNumNotes();
?>
<ul id="threads">
    <li><a class="active" id="toggle_ticket_thread" href="#"><?php echo __('Ticket Thread');?> (<?php echo $tcount; ?>)</a></li>
    <?php
    if(!$cfg->showNotesInline()) {?>
    <li><a id="toggle_notes" href="#"><?php echo __('Internal Notes');?> (<?php echo $ticket->getNumNotes(); ?>)</a></li>
    <?php
    }?>
</ul>
<?php
if(!$cfg->showNotesInline()) { ?>
<div id="ticket_notes">
    <?php
    /* Internal Notes */
    if($ticket->getNumNotes() && ($notes=$ticket->getNotes())) {
        foreach($notes as $note) {

        ?>
        <table class="note" cellspacing="0" cellpadding="1" width="940" border="0">
            <tr>
                <th width="640">
                    <?php
                    echo sprintf('%s <em>'.__('posted by <b>%s</b>').'</em>',
                            Format::htmlchars($note['title']),
                            Format::htmlchars($note['poster']));
                    ?>
                </th>
                <th class="date" width="300"><?php echo Format::db_datetime($note['created']); ?></th>
            </tr>
            <tr>
                <td colspan="2">
                    <?php echo Format::display($note['body']); ?>
                </td>
            </tr>
            <?php
             if($note['attachments'] 
                    && ($tentry=$ticket->getThreadEntry($note['id'])) 
                    && ($links=$tentry->getAttachmentsLinks())) { ?>
            <tr>
                <td class="info" colspan="2"><?php echo $links; ?></td>
            </tr>
            <?php
            }?>
        </table>
    <?php
        }
    } else {
        echo "<p>".__('No internal notes found.')."</p>";
    }?>
</div>
<?php
} ?>
<div id="ticket_thread">
    <?php
    $threadTypes=array('M'=>'message','R'=>'response', 'N'=>'note');
    /* -------- Messages & Responses & Notes (if inline)-------------*/
    $types = array('M', 'R');
    if($cfg->showNotesInline())
        $types[] = 'N';
    if(($thread=$ticket->getThreadEntries($types))) {
       foreach($thread as $entry) {
           ?>
        <table class="<?php echo $threadTypes[$entry['thread_type']]; ?>" cellspacing="0" cellpadding="1" width="940" border="0">
            <tr>
                <th width="200"><?php echo Format::db_datetime($entry['created']);?></th>
                <th width="440"><span><?php echo Format::htmlchars($entry['title']); ?></span></th>
                <th width="300" class="tmeta"><?php echo Format::htmlchars($entry['poster']); ?></th>
            </tr>
            <tr><td colspan=3><?php echo Format::display($entry['body']); ?></td></tr>
            <?php
            if($entry['attachments'] 
                    && ($tentry=$ticket->getThreadEntry($entry['id']))
                    && ($links=$tentry->getAttachmentsLinks())) {?>
            <tr>
                <td class="info" colspan=3><?php echo $links; ?></td>
            </tr>
            <?php
            }?>
        </table>
        <?php
        if($entry['thread_type']=='M')
            $msgId=$entry['id'];
       }
    } else {
        echo '<p>'.__('Error fetching ticket thread - get technical help.').'</p>';
    }?>
</div>
<div class="clear" style="padding-bottom:10px;"></div>
<?php if($errors['err']) { ?>
    <div id="msg_error"><?php echo $errors['err']; ?></div>
<?php }elseif($msg) { ?>
    <div id="msg_notice"><?php echo $msg; ?></div>
<?php }elseif($warn) { ?>
    <div id="msg_warning"><?php echo $warn; ?></div>
<?php } ?>

<div id="response_options">
    <ul>
        <?php
        if($thisstaff->canPostReply()) { ?>
        <li><a id="reply_tab" href="#reply"><?php echo __('Post Reply');?></a></li>
        <?php
        } ?>
        <li><a id="note_tab" href="#note"><?php echo __('Post Internal Note');?></a></li>
        <?php
        if($thisstaff->canTransferTickets()) { ?>
        <li><a id="transfer_tab" href="#transfer"><?php echo __('Dept. Transfer');?></a></li>
        <?php
        }

        if($thisstaff->canAssignTickets()) { ?>
        <li><a id="assign_tab" href="#assign"><?php echo $ticket->isAssigned()?__('Reassign Ticket'):__('Assign Ticket'); ?></a></li>
        <?php
        } ?>
    </ul>
    <?php
    if($thisstaff->canPostReply()) { ?>
    <form id="reply" action="tickets.php?id=<?php echo $ticket->getId(); ?>#reply" name="reply" method="post" enctype="multipart/form-data">
        <?php csrf_token(); ?>
        <input type="hidden" name="id" value="<?php echo $ticket->getId(); ?>">
        <input type="hidden" name="msgId" value="<?php echo $msgId; ?>">
        <input type="hidden" name="a" value="reply">
        <span class="error"></span>
        <table border="0" cellspacing="0" cellpadding="3">
            <tr>
                <td width="160">
                    <label><strong><?php echo __('TO');?>:</strong></label>
                </td>
                <td width="765">
                    <?php
                    $to = $ticket->getEmail();
                    if(($name=$ticket->getName()) && !strpos($name,'@'))
                        $to =sprintf('%s <em>&lt;%s&gt;</em>', $name, $ticket->getEmail());
                    echo $to;
                    ?>
                    &nbsp;&nbsp;&nbsp;
                    <label><input type='checkbox' value='1' name="emailreply" id="remailreply"
                        <?php echo ((!$info['emailreply'] && !$errors) || isset($info['emailreply']))?'checked="checked"':''; ?>> <?php echo __('Email Reply');?></label>
                </td>
            </tr>
            <?php
            if($errors['response']) {?>
            <tr><td width="160">&nbsp;</td><td class="error"><?php echo $errors['response']; ?>&nbsp;</td></tr>
            <?php
            }?>
            <tr>
                <td width="160">
                    <label><strong><?php echo __('Response');?>:</strong></label>
                </td>
                <td width="765">
                    <?php
                    if(($cannedResponses=Canned::responsesByDeptId($ticket->getDeptId()))) {?>
                        <select id="cannedResp" name="cannedResp">
                            <option value="0" selected="selected"><?php echo __('Select a canned response');?></option>
                            <?php
                            foreach($cannedResponses as $id =>$title) {
                                echo sprintf('<option value="%d">%s</option>',$id,$title);
                            }
                            ?>
                        </select>
                        &nbsp;&nbsp;&nbsp;
                        <label><input type='checkbox' value='1' name="append" id="append" checked="checked"> <?php echo __('Append');?></label>
                        <br>
                    <?php
                    }?>
                    <textarea name="response" id="response" cols="50" rows="9" wrap="soft"><?php echo $info['response']; ?></textarea>
                </td>
            </tr>
            <?php
            if($cfg->allowAttachments()) { ?>
            <tr>
                <td width="160">
                    <label for="attachment"><?php echo __('Attachments');?>:</label>
                </td>
                <td width="765" id="reply_form_attachments" class="attachments">
                    <div class="canned_attachments">
                    </div>
                    <div class="uploads">
                    </div>
                    <div class="file_input">
                        <input type="file" class="multifile" name="attachments[]" size="30" value="" />
                    </div>
                </td>
            </tr>
            <?php
            }?>
            <tr>
                <td width="160">
                    <label for="signature" class="left"><?php echo __('Signature');?>:</label>
                </td>
                <td width="765">
                    <?php
                    $info['signature']=$info['signature']?$info['signature']:$thisstaff->getDefaultSignatureType();
                    ?>
                    <label><input type="radio" name="signature" value="none" checked="checked"> <?php echo __('None');?></label>
                    <?php
                    if($thisstaff->getSignature()) {?>
                    <label><input type="radio" name="signature" value="mine"
                        <?php echo ($info['signature']=='mine')?'checked="checked"':''; ?>> <?php echo __('My Signature');?></label>
                    <?php
                    } ?>
                    <?php
                    if($dept && $dept->canAppendSignature()) { ?>
                    <label><input type="radio" name="signature" value="dept"
                        <?php echo ($info['signature']=='dept')?'checked="checked"':''; ?>>
                        Dept. Signature (<?php echo Format::htmlchars($dept->getName()); ?>)</label>
                    <?php
                    } ?>
                </td>
            </tr>
            <?php
            if($ticket->isClosed() || $thisstaff->canCloseTickets()) { ?>
            <tr>
                <td width="160">
                    <label><strong><?php echo __('Ticket Status');?>:</strong></label>
                </td>
                <td width="765">
                    <?php
                    $statusChecked=isset($info['reply_ticket_status'])?'checked="checked"':'';
                    if($ticket->isClosed()) { ?>
                        <label><input type="checkbox" name="reply_ticket_status" id="reply_ticket_status" value="Open"
                            <?php echo $statusChecked; ?>> <?php echo __('Reopen on Reply');?></label>
                   <?php
                    } elseif($thisstaff->canCloseTickets()) { ?>
                         <label><input type="checkbox" name="reply_ticket_status" id="reply_ticket_status" value="Closed"
                              <?php echo $statusChecked; ?>> <?php echo __('Close on Reply');?></label>
                   <?php
                    } ?>
                </td>
            </tr>
            <?php
            } ?>
            </div>
        </table>
        <p  style="padding-left:165px;">
            <input class="btn_sm" type="submit" value="<?php echo __('Post Reply');?>">
            <input class="btn_sm" type="reset" value="<?php echo __('Reset');?>">
        </p>
    </form>
    <?php
    } ?>
    <form id="note" action="tickets.php?id=<?php echo $ticket->getId(); ?>#note" name="note" method="post" enctype="multipart/form-data">
        <?php csrf_token(); ?>
        <input type="hidden" name="id" value="<?php echo $ticket->getId(); ?>">
        <input type="hidden" name="locktime" value="<?php echo $cfg->getLockTime(); ?>">
        <input type="hidden" name="a" value="postnote">
        <table border="0" cellspacing="0" cellpadding="3">
            <?php 
            if($errors['postnote']) {?>
            <tr>
                <td width="160">&nbsp;</td>
                <td class="error"><?php echo $errors['postnote']; ?></td>
            </tr>
            <?php
            } ?>
            <tr>
                <td width="160">
                    <label><strong><?php echo __('Internal Note');?>:</strong></label>
                </td>
                <td width="765">
                    <div><span class="faded"><?php echo __('Note details');?></span>&nbsp;
                        <span class="error">*&nbsp;<?php echo $errors['note']; ?></span></div>
                    <textarea name="note" id="internal_note" cols="80" rows="9" wrap="soft"><?php echo $info['note']; ?></textarea><br>
                    <div>
                        <span class="faded"><?php echo __('Note title');?> - <?php echo __('summarry of the note (optional)');?></span>&nbsp;
                        <span class="error"&nbsp;<?php echo $errors['title']; ?></span>
                    </div>
                    <input type="text" name="title" id="title" size="60" value="<?php echo $info['title']; ?>" >
                </td>
            </tr>
            <?php
            if($cfg->allowAttachments()) { ?>
            <tr>
                <td width="160">
                    <label for="attachment"><?php echo __('Attachments');?>:</label>
                </td>
                <td width="765" class="attachments">
                    <div class="uploads">
                    </div>
                    <div class="file_input">
                        <input type="file" class="multifile" name="attachments[]" size="30" value="" />
                    </div>
                </td>
            </tr>
            <?php
            }
            ?>
            <tr><td colspan="2">&nbsp;</td></tr>
            <tr>
                <td width="160">
                    <label><?php echo __('Ticket Status');?>:</label>
                </td>
                <td width="765">
                    <div class="faded"></div>
                    <select name="state">
                        <option value="" selected="selected">&mdash; <?php echo __('unchanged');?> &mdash;</option>
                        <?php
                        $state = $info['state'];
                        if($ticket->isClosed()){ 
                            echo sprintf('<option value="open" %s>'.__('Reopen Ticket').'</option>',
                                    ($state=='reopen')?'selected="selelected"':'');
                        } else {
                            if($thisstaff->canCloseTickets())
                                echo sprintf('<option value="closed" %s>'.__('Close Ticket').'</option>',
                                    ($state=='closed')?'selected="selelected"':'');

                            /* Ticket open - states */
                            echo '<option value="" disabled="disabled">&mdash; '.__('Ticket States').' &mdash;</option>';
                       
                            //Answer - state
                            if($ticket->isAnswered())
                                echo sprintf('<option value="unanswered" %s>'.__('Mark as Unanswered').'</option>',
                                    ($state=='unanswered')?'selected="selelected"':'');
                            else 
                                echo sprintf('<option value="answered" %s>'.__('Mark as Answered').'</option>',
                                    ($state=='answered')?'selected="selelected"':'');

                            //overdue - state
                            // Only department manager can set/clear overdue flag directly.
                            // Staff with edit perm. can still set overdue date & change SLA.
                            if($dept && $dept->isManager($thisstaff)) {
                                if(!$ticket->isOverdue())
                                    echo sprintf('<option value="overdue" %s>'.__('Flag As Overdue').'</option>',
                                        ($state=='answered')?'selected="selelected"':'');
                                else
                                    echo sprintf('<option value="notdue" %s>'.__('Clear Overdue Flag').'</option>',
                                        ($state=='notdue')?'selected="selelected"':'');

                                if($ticket->isAssigned())
                                    echo sprintf('<option value="unassigned" %s>'.__('Release (Unassign) Ticket').'</option>',
                                        ($state=='unassigned')?'selected="selelected"':'');
                            }
                        }?>
                    </select>
                    &nbsp;<span class='error'>*&nbsp;<?php echo $errors['state']; ?></span>
                </td>
            </tr>
            </div>
        </table>

       <p  style="padding-left:165px;">
           <input class="btn_sm" type="submit" value="<?php echo __('Post Note');?>">
           <input class="btn_sm" type="reset" value="<?php echo __('Reset');?>">
       </p>
   </form>
    <?php
    if($thisstaff->canTransferTickets()) { ?>
    <form id="transfer" action="tickets.php?id=<?php echo $ticket->getId(); ?>#transfer" name="transfer" method="post" enctype="multipart/form-data">
        <?php csrf_token(); ?>
        <input type="hidden" name="ticket_id" value="<?php echo $ticket->getId(); ?>">
        <input type="hidden" name="a" value="transfer">
        <table border="0" cellspacing="0" cellpadding="3">
            <?php
            if($errors['transfer']) {
                ?>
            <tr>
                <td width="160">&nbsp;</td>
                <td class="error"><?php echo $errors['transfer']; ?></td>
            </tr>
            <?php
            } ?>
            <tr>
                <td width="160">
                    <label for="deptId"><strong><?php echo __('Department');?>:</strong></label>
                </td>
                <td width="765">
                    <?php
                        echo sprintf('<span class="faded">'.__('Ticket is currently in <b>%s</b> department.').'</span>', $ticket->getDeptName());
                    ?>
                    <br>
                    <select id="deptId" name="deptId">
                        <option value="0" selected="selected">&mdash; <?php echo __('Select Target Department');?> &mdash;</option>
                        <?php
                        if($depts=Dept::getDepartments()) {
                            foreach($depts as $id =>$name) {
                                if($id==$ticket->getDeptId()) continue;
                                echo sprintf('<option value="%d" %s>%s</option>',
                                        $id, ($info['deptId']==$id)?'selected="selected"':'',$name);
                            }
                        }
                        ?>
                    </select>&nbsp;<span class='error'>*&nbsp;<?php echo $errors['deptId']; ?></span>
                </td>
            </tr>
            <tr>
                <td width="160">
                    <label><strong><?php echo __('Comments');?>:</strong></label>
                </td>
                <td width="765">
                    <span class="faded"><?php echo __('Enter reasons for the transfer.');?></span>
                    <span class="error">*&nbsp;<?php echo $errors['transfer_comments']; ?></span><br>
                    <textarea name="transfer_comments" id="transfer_comments"
                        cols="80" rows="7" wrap="soft"><?php echo $info['transfer_comments']; ?></textarea>
                </td>
            </tr>
        </table>
        <p style="padding-left:165px;">
           <input class="btn_sm" type="submit" value="<?php echo __('Transfer');?>">
           <input class="btn_sm" type="reset" value="<?php echo __('Reset');?>">
        </p>
    </form>
    <?php
    } ?>
    <?php
    if($thisstaff->canAssignTickets()) { ?>
    <form id="assign" action="tickets.php?id=<?php echo $ticket->getId(); ?>#assign" name="assign" method="post" enctype="multipart/form-data">
        <?php csrf_token(); ?>
        <input type="hidden" name="id" value="<?php echo $ticket->getId(); ?>">
        <input type="hidden" name="a" value="assign">
        <table border="0" cellspacing="0" cellpadding="3">
                
            <?php
            if($errors['assign']) {
                ?>
            <tr>
                <td width="160">&nbsp;</td>
                <td class="error"><?php echo $errors['assign']; ?></td>
            </tr>
            <?php
            } ?>
            <tr>
                <td width="160">
                    <label for="assignId"><strong><?php echo __('Assignee');?>:</strong></label>
                </td>
                <td width="765">
                    <?php
                    if($ticket->isAssigned() && $ticket->isOpen()) {
                        echo sprintf('<span class="faded">'.__('Ticket is currently assigned to <b>%s</b>').'</span>',
                                $ticket->getAssignee());
                    } else {
                        echo '<span class="faded">'.__('Assigning a closed ticket will <b>reopen</b> it!').'</span>';
                    }
                    ?>
                    <br>
                    <select id="assignId" name="assignId">
                        <option value="0" selected="selected">&mdash; <?php echo __('Select Staff Member OR a Team');?> &mdash;</option>
                        <?php
                        if($ticket->isOpen() && !$ticket->isAssigned())
                            echo sprintf('<option value="%d">'.__('Claim Ticket (comments optional)').'</option>', $thisstaff->getId());

                        $sid=$tid=0;
                        if(($users=Staff::getAvailableStaffMembers())) {
                            echo '<OPTGROUP label="'.__('Staff Members').' ('.count($users).')">';
                            $staffId=$ticket->isAssigned()?$ticket->getStaffId():0;
                            foreach($users as $id => $name) {
                                if($staffId && $staffId==$id)
                                    continue;

                                $k="s$id";
                                echo sprintf('<option value="%s" %s>%s</option>',
                                        $k,(($info['assignId']==$k)?'selected="selected"':''),$name);
                            }
                            echo '</OPTGROUP>';
                        }

                        if(($teams=Team::getActiveTeams())) {
                            echo '<OPTGROUP label="'.__('Teams').' ('.count($teams).')">';
                            $teamId=(!$sid && $ticket->isAssigned())?$ticket->getTeamId():0;
                            foreach($teams as $id => $name) {
                                if($teamId && $teamId==$id)
                                    continue;

                                $k="t$id";
                                echo sprintf('<option value="%s" %s>%s</option>',
                                        $k,(($info['assignId']==$k)?'selected="selected"':''),$name);
                            }
                            echo '</OPTGROUP>';
                        }
                        ?>
                    </select>&nbsp;<span class='error'>*&nbsp;<?php echo $errors['assignId']; ?></span>
                </td>
            </tr>
            <tr>
                <td width="160">
                    <label><strong><?php echo __('Comments');?>:</strong><span class='error'>&nbsp;</span></label>
                </td>
                <td width="765">
                    <span class="faded"><?php echo __('Enter reasons for the assignment or instructions for assignee.');?></span>
                    <span class="error">*&nbsp;<?php echo $errors['assign_comments']; ?></span><br>
                    <textarea name="assign_comments" id="assign_comments" cols="80" rows="7" wrap="soft"><?php echo $info['assign_comments']; ?></textarea>
                </td>
            </tr>
        </table>
        <p  style="padding-left:165px;">
            <input class="btn_sm" type="submit" value="<?php echo $ticket->isAssigned()?__('Reassign'):__('Assign'); ?>">
            <input class="btn_sm" type="reset" value="<?php echo __('Reset');?>">
        </p>
    </form>
    <?php
    } ?>
</div>
<div style="display:none;" class="dialog" id="print-options">
    <h3><?php echo __('Ticket Print Options');?></h3>
    <a class="close" href="">&times;</a>
    <hr/>
    <form action="tickets.php?id=<?php echo $ticket->getId(); ?>" method="post" id="print-form" name="print-form">
        <?php csrf_token(); ?>
        <input type="hidden" name="a" value="print">
        <input type="hidden" name="id" value="<?php echo $ticket->getId(); ?>">
        <fieldset class="notes">
            <label for="notes"><?php echo __('Print Notes');?>:</label>
            <input type="checkbox" id="notes" name="notes" value="1"> <?php echo __('Print <b>Internal</b> Notes/Comments');?>
        </fieldset>
        <fieldset>
            <label for="psize"><?php echo __('Paper Size');?>:</label>
            <select id="psize" name="psize">
                <option value="">&mdash; <?php echo __('Select Print Paper Size');?> &mdash;</option>
                <?php
                  $options=array('Letter', 'Legal', 'A4', 'A3');
                  $psize =$_SESSION['PAPER_SIZE']?$_SESSION['PAPER_SIZE']:$thisstaff->getDefaultPaperSize();
                  foreach($options as $v) {
                      echo sprintf('<option value="%s" %s>%s</option>',
                                $v,($psize==$v)?'selected="selected"':'', $v);
                  }
                ?>
            </select>
        </fieldset>
        <hr style="margin-top:3em"/>
        <p class="full-width">
            <span class="buttons" style="float:left">
                <input type="reset" value="<?php echo __('Reset');?>">
                <input type="button" value="<?php echo __('Cancel');?>" class="close">
            </span>
            <span class="buttons" style="float:right">
                <input type="submit" value="<?php echo __('Print');?>">
            </span>
         </p>
    </form>
    <div class="clear"></div>
</div>
<div style="display:none;" class="dialog" id="ticket-status">
    <h3><?php echo sprintf(__('%1$s Ticket #%2$s'), ($ticket->isClosed()?__('Reopen'):__('Close')), $ticket->getNumber()); ?></h3>
    <a class="close" href="">&times;</a>
    <hr/>
    <?php echo sprintf(__('Are you sure you want to <b>%s</b> this ticket?'), $ticket->isClosed()?__('REOPEN'):__('CLOSE')); ?>
    <form action="tickets.php?id=<?php echo $ticket->getId(); ?>" method="post" id="status-form" name="status-form">
        <?php csrf_token(); ?>
        <input type="hidden" name="id" value="<?php echo $ticket->getId(); ?>">
        <input type="hidden" name="a" value="process">
        <input type="hidden" name="do" value="<?php echo $ticket->isClosed()?'reopen':'close'; ?>">
        <fieldset>
            <em><?php echo __('Reasons for status change (internal note). Optional but highly recommended.');?></em><br>
            <textarea name="ticket_status_notes" id="ticket_status_notes" cols="50" rows="5" wrap="soft"><?php echo $info['ticket_status_notes']; ?></textarea>
        </fieldset>
        <hr style="margin-top:1em"/>
        <p class="full-width">
            <span class="buttons" style="float:left">
                <input type="reset" value="<?php echo __('Reset');?>">
                <input type="button" value="<?php echo __('Cancel');?>" class="close">
            </span>
            <span class="buttons" style="float:right">
                <input type="submit" value="<?php echo $ticket->isClosed()?__('Reopen'):__('Close'); ?>">
            </span>
         </p>
    </form>
    <div class="clear"></div>
</div>
<div style="display:none;" class="dialog" id="confirm-action">
    <h3><?php echo __('Please Confirm');?></h3>
    <a class="close" href="">&times;</a>
    <hr/>
    <p class="confirm-action" style="display:none;" id="claim-confirm">
        <?php echo __('Are you sure want to <b>claim</b> (self assign) this ticket?');?>
    </p>
    <p class="confirm-action" style="display:none;" id="answered-confirm">
        <?php echo __('Are you sure want to flag the ticket as <b>answered</b>?');?>
    </p>    
    <p class="confirm-action" style="display:none;" id="unanswered-confirm">
        <?php echo __('Are you sure want to flag the ticket as <b>unanswered</b>?');?>
    </p>
    <p class="confirm-action" style="display:none;" id="overdue-confirm">
        <?php echo __('Are you sure want to flag the ticket as <font color="red"><b>overdue</b></font>?');?>
    </p>
    <p class="confirm-action" style="display:none;" id="banemail-confirm">
        <?php echo sprintf(__('Are you sure want to <b>ban</b> %s?'), $ticket->getEmail());?> <br><br>
        <?php echo __('New tickets from the email address will be auto-rejected.');?>
    </p>
    <p class="confirm-action" style="display:none;" id="unbanemail-confirm">
        <?php echo sprintf(__('Are you sure want to <b>remove</b> %s from ban list?'), $ticket->getEmail()); ?> 
    </p>
    <p class="confirm-action" style="display:none;" id="release-confirm">
        <?php echo sprintf(__('Are you sure want to <b>unassign</b> ticket from %s<b>?'), $ticket->getAssigned()); ?></b>
    </p>
    <p class="confirm-action" style="display:none;" id="delete-confirm">
        <font color="red"><strong><?php echo __('Are you sure you want to DELETE this ticket?');?></strong></font>
        <br><br><?php echo __('Deleted tickets CANNOT be recovered, including any associated attachments.');?>
    </p>
    <div><?php echo __('Please confirm to continue.');?></div>
    <form action="tickets.php?id=<?php echo $ticket->getId(); ?>" method="post" id="confirm-form" name="confirm-form">
        <?php csrf_token(); ?>
        <input type="hidden" name="id" value="<?php echo $ticket->getId(); ?>">
        <input type="hidden" name="a" value="process">
        <input type="hidden" name="do" id="action" value="">
        <hr style="margin-top:1em"/>
        <p class="full-width">
            <span class="buttons" style="float:left">
                <input type="button" value="<?php echo __('Cancel');?>" class="close">
            </span>
            <span class="buttons" style="float:right">
                <input type="submit" value="<?php echo __('OK');?>">
            </span>
         </p>
    </form>
    <div class="clear"></div>
</div>
<script type="text/javascript" src="js/ticket.js"></script>
