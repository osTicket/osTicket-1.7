<?php
//Note that ticket obj is initiated in tickets.php.
if(!defined('OSTSCPINC') || !$thisstaff || !is_object($ticket) || !$ticket->getId()) die(_('Invalid path'));

//Make sure the staff is allowed to access the page.
if(!@$thisstaff->isStaff() || !$ticket->checkStaffAccess($thisstaff)) die(_('Access Denied'));

//Re-use the post info on error...savekeyboards.org (Why keyboard? -> some people care about objects than users!!)
$info=($_POST && $errors)?Format::input($_POST):array();

//Auto-lock the ticket if locking is enabled.. If already locked by the user then it simply renews.
if($cfg->getLockTime() && !$ticket->acquireLock($thisstaff->getId(),$cfg->getLockTime()))
    $warn.=_('Unable to obtain a lock on the ticket');

//Get the goodies.
$dept  = $ticket->getDept();  //Dept
$staff = $ticket->getStaff(); //Assigned or closed by..
$team  = $ticket->getTeam();  //Assigned team.
$lock  = $ticket->getLock();  //Ticket lock obj
$id    = $ticket->getId();    //Ticket ID.

//Useful warnings and errors the user might want to know!
if($ticket->isAssigned() && (
            ($staff && $staff->getId()!=$thisstaff->getId())
         || ($team && !$team->hasMember($thisstaff))
        ))
    $warn.='&nbsp;&nbsp;<span class="Icon assignedTicket">'._('Ticket is assigned to ').implode('/', $ticket->getAssignees()).'</span>';
if(!$errors['err'] && ($lock && $lock->getStaffId()!=$thisstaff->getId()))
    $errors['err']=_('This ticket is currently locked by ').$lock->getStaffName();
if(!$errors['err'] && ($emailBanned=TicketFilter::isBanned($ticket->getEmail())))
    $errors['err']=_('Email is in banlist! Must be removed before any reply/response');

$unbannable=($emailBanned) ? BanList::includes($ticket->getEmail()) : false;

if($ticket->isOverdue())
    $warn.='&nbsp;&nbsp;<span class="Icon overdueTicket">'._('Marked overdue!').'</span>';

?>
<table width="910" cellpadding="2" cellspacing="0" border="0">
    <tr>
        <td width="50%">
             <h2><?= _('Ticket')?> #<?php echo $ticket->getExtId(); ?>
                <a href="tickets.php?id=<?php echo $ticket->getId(); ?>" title="<?=_('Reload')?>" class="reload"><?= _('Reload')?></a></h2>
        </td>
        <td width="50%" class="right_align">
            <a href="tickets.php?id=<?php echo $ticket->getId(); ?>&a=print" title="<?=_('Print Ticket')?>" class="print" id="ticket-print"><?= _('Print Ticket')?></a>
            <?php
            if($thisstaff->canEditTickets()) { ?>
             <a href="tickets.php?id=<?php echo $ticket->getId(); ?>&a=edit" title="<?=_('Edit Ticket')?>" class="edit"><?= _('Edit Ticket')?></a>
            <?php
            } ?>
        </td>
    </tr>
</table>
<table class="ticket_info" cellspacing="0" cellpadding="0" width="940" border="0">
    <tr>
        <td width="50">
            <table border="0" cellspacing="" cellpadding="4" width="100%">
                <tr>
                    <th width="100"><?= _('Status')?>:</th>
                    <td><?php echo _(ucfirst($ticket->getStatus())); ?></td>
                </tr>
                <tr>
                    <th><?= _('Priority')?>:</th>
                    <td><?php echo $ticket->getPriority(); ?></td>
                </tr>
                <tr>
                    <th><?= _('Department')?>:</th>
                    <td><?php echo Format::htmlchars($ticket->getDeptName()); ?></td>
                </tr>
                <tr>
                    <th><?= _('Create Date')?>:</th>
                    <td><?php echo Format::db_datetime($ticket->getCreateDate()); ?></td>
                </tr>
            </table>
        </td>
        <td width="50%">
            <table border="0" cellspacing="" cellpadding="4" width="100%">
                <tr>
                    <th width="100">Name:</th>
                    <td><?php echo Format::htmlchars($ticket->getName()); ?></td>
                </tr>
                <tr>
                    <th><?= _('Email')?>:</th>
                    <td>
                    <?php
                        echo $ticket->getEmail();
                        if(($related=$ticket->getRelatedTicketsCount())) {
                            echo sprintf('&nbsp;&nbsp;<a href="tickets.php?a=search&query=%s" title="Related Tickets">(<b>%d</b>)</a>',
                                    urlencode($ticket->getEmail()),$related);
                    
                        }
                    ?>
                    </td>
                </tr>
                <tr>
                    <th><?= _('Phone')?>:</th>
                    <td><?php echo $ticket->getPhoneNumber(); ?></td>
                </tr>
                <tr>
                    <th><?= _('Source')?>:</th>
                    <td><?php echo Format::htmlchars($ticket->getSource()); ?></td>
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
                    <th width="100"><?= _('Assigned To')?>:</th>
                    <td>
                        <?php
                        if($ticket->isAssigned())
                            echo Format::htmlchars(implode('/', $ticket->getAssignees()));
                        else
                            echo '<span class="faded">&mdash; '._('Unassigned').' &mdash;</span>';
                        ?>
                    </td>
                </tr>
                <?php
                } else { ?>
                <tr>
                    <th width="100"><?= _('Closed By')?>:</th>
                    <td>
                        <?php
                        if(($staff = $ticket->getStaff()))
                            echo Format::htmlchars($staff->getName());
                        else
                            echo '<span class="faded">&mdash; '._('Unknown').' &mdash;</span>';
                        ?>
                    </td>
                </tr>
                <?php
                } ?>
                <tr>
                    <th nowrap><?= _('Last Response')?>:</th>
                    <td><?php echo Format::db_datetime($ticket->getLastRespDate()); ?></td>
                </tr>
                <?php
                if($ticket->isOpen()){ ?>
                <tr>
                    <th><?= _('Due Date')?>:</th>
                    <td><?php echo Format::db_datetime($ticket->getDueDate()); ?></td>
                </tr>
                <?php
                }else { ?>
                <tr>
                    <th><?= _('Close Date')?>:</th>
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
                    <th width="100"><?= _('Subject')?>:</th>
                    <td><?php echo Format::htmlchars(Format::truncate($ticket->getSubject(),200)); ?></td>
                </tr>
                <tr>
                    <th><?= _('Help Topic')?>:</th>
                    <td><?php echo Format::htmlchars($ticket->getHelpTopic()); ?></td>
                </tr>
                <tr>
                    <th nowrap><?= _('Last Message')?>:</th>
                    <td><?php echo Format::db_datetime($ticket->getLastMsgDate()); ?></td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<div class="clear" style="padding-bottom:10px;"></div>
<?php
$tcount = $ticket->getThreadCount();
if($cfg->showNotesInline())
    $tcount+= $ticket->getNumNotes();
?>
<ul id="threads">
    <li><a class="active" id="toggle_ticket_thread" href="#"><?= _('Ticket Thread')?> (<?php echo $tcount; ?>)</a></li>
    <?php
    if(!$cfg->showNotesInline()) {?>
    <li><a id="toggle_notes" href="#"><?= _('Internal Notes')?> (<?php echo $ticket->getNumNotes(); ?>)</a></li>
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
                    echo sprintf('%s <em>posted by <b>%s</b></em>',
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
            if($note['attachments'] && ($links=$ticket->getAttachmentsLinks($note['id'],'N'))) {?>
            <tr>
                <td class="info" colspan="2"><?php echo $links; ?></td>
            </tr>
            <?php
            }?>
        </table>
    <?php
        }
    } else {
        echo "<p>"._('No internal notes found.')."</p>";
    }?>
</div>
<?php
} ?>
<div id="ticket_thread">
    <?php
    $threadTypes=array('M'=>'message','R'=>'response', 'N'=>'note');
    /* -------- Messages & Responses & Notes (if inline)-------------*/
    if(($thread=$ticket->getThread($cfg->showNotesInline()))) {
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
            if($entry['attachments'] && ($links=$ticket->getAttachmentsLinks($entry['id'], $entry['thread_type']))) {?>
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
        echo '<p>'._('Error fetching ticket thread - get technical help.').'</p>';
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
        <li><a id="reply_tab" href="#reply"><?= _('Post Reply')?></a></li>
        <li><a id="note_tab" href="#note"><?= _('Post Internal Note')?></a></li>
        <?php
        if($thisstaff->canTransferTickets()) { ?>
        <li><a id="transfer_tab" href="#transfer"><?= _('Dept. Transfer')?></a></li>
        <?php
        }
        
        if($thisstaff->canAssignTickets()) { ?>
        <li><a id="assign_tab" href="#assign"><?php echo $ticket->isAssigned()?_('Reassign Ticket'):_('Assign Ticket'); ?></a></li>
        <?php
        } ?>
    </ul>

    <form id="reply" action="tickets.php?id=<?php echo $ticket->getId(); ?>#reply" name="reply" method="post" enctype="multipart/form-data">
        <?php csrf_token(); ?>
        <input type="hidden" name="id" value="<?php echo $ticket->getId(); ?>">
        <input type="hidden" name="msgId" value="<?php echo $msgId; ?>">
        <input type="hidden" name="a" value="reply">
        <span class="error"></span>
        <table border="0" cellspacing="0" cellpadding="3">
            <tr>
                <td width="160">&nbsp;</td>
                <td class="error"><?php echo $errors['response']; ?></td>
            </tr>
            <?php
            if(($cannedResponses=Canned::responsesByDeptId($ticket->getDeptId()))) {?>
            <tr>
                <td width="160">
                    <label>&nbsp;</label>
                </td>
                <td width="765">
                    <select id="cannedResp" name="cannedResp">
                        <option value="0" selected="selected"><?= _('Select a canned response')?></option>
                        <?php
                        foreach($cannedResponses as $id =>$title) {
                            echo sprintf('<option value="%d">%s</option>',$id,$title);
                        }
                        ?>
                    </select>
                    &nbsp;&nbsp;&nbsp;
                    <label><input type='checkbox' value='1' name="append" id="append" checked="checked"> <?= _('Append')?></label>
                </td>
            </tr>
            <?php
            }?>
            <tr>
                <td width="160">
                    <label><strong><?= _('Response')?>:</strong></label>
                </td>
                <td width="765">
                    <textarea name="response" id="response" cols="50" rows="9" wrap="soft"><?php echo $info['response']; ?></textarea>
                </td>
            </tr>
            <?php
            if($cfg->allowAttachments()) { ?>
            <tr>
                <td width="160">
                    <label for="attachment"><?= _('Attachments')?>:</label>
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
                    <label for="signature" class="left"><?= _('Signature')?>:</label>
                </td>
                <td width="765">
                    <?php
                    $info['signature']=$info['signature']?$info['signature']:$thisstaff->getDefaultSignatureType();
                    ?>
                    <label><input type="radio" name="signature" value="none" checked="checked"> <?=_('None') ?></label>
                    <?php
                    if($thisstaff->getSignature()) {?>
                    <label><input type="radio" name="signature" value="mine" 
                        <?php echo ($info['signature']=='mine')?'checked="checked"':''; ?>> <?= _('My signature')?></label>
                    <?php
                    } ?>
                    <?php
                    if($dept && $dept->canAppendSignature()) { ?>
                    <label><input type="radio" name="signature" value="dept" 
                        <?php echo ($info['signature']=='dept')?'checked="checked"':''; ?>> 
                        <?= _('Dept. Signature')?> (<?php echo Format::htmlchars($dept->getName()); ?>)</label>
                    <?php
                    } ?>
                </td>
            </tr>
            <?php
            if($ticket->isClosed() || $thisstaff->canCloseTickets()) { ?>
            <tr>
                <td width="160">
                    <label><strong><?= _('Ticket Status')?>:</strong></label>
                </td>
                <td width="765">
                    <?php
                    $statusChecked=isset($info['reply_ticket_status'])?'checked="checked"':'';
                    if($ticket->isClosed()) { ?>
                        <label><input type="checkbox" name="reply_ticket_status" id="reply_ticket_status" value="Open"
                            <?php echo $statusChecked; ?>> <?= _('Reopen on Reply')?></label>
                   <?php
                    } elseif($thisstaff->canCloseTickets()) { ?>
                         <label><input type="checkbox" name="reply_ticket_status" id="reply_ticket_status" value="Closed"
                              <?php echo $statusChecked; ?>> <?= _('Close on Reply')?></label>
                   <?php
                    } ?>
                </td>
            </tr>
            <?php
            } ?>
            </div>
        </table>
        <p  style="padding-left:165px;">
            <input class="btn_sm" type="submit" value="<?=_('Post Reply')?>">
            <input class="btn_sm" type="reset" value="<?=_('Reset')?>">
        </p>
    </form>
    <form id="note" action="tickets.php?id=<?php echo $ticket->getId(); ?>#note" name="note" method="post" enctype="multipart/form-data">
        <?php csrf_token(); ?>
        <input type="hidden" name="id" value="<?php echo $ticket->getId(); ?>">
        <input type="hidden" name="a" value="postnote">
        <table border="0" cellspacing="0" cellpadding="3">
            <tr>
                <td width="160">&nbsp;</td>
                <td class="error"><?php echo $errors['note']; ?></td>
            </tr>
            <tr>
                <td width="160">
                    <label><strong><?= _('Title')?>:</strong></label>
                </td>
                <td width="765">
                    <input type="text" name="title" id="title" size="45" value="<?php echo $info['title']; ?>" >
                    &nbsp;<span class="error">*&nbsp;<?php echo $errors['title']; ?></span>
                </td>
            </tr>
            <tr>
                <td width="160">
                    <label><strong><?= _('Note')?>:</strong></label>
                </td>
                <td width="765">
                    <div><span class="faded"><?= _('Internal note details')?></span>&nbsp;
                        <span class="error">*&nbsp;<?php echo $errors['internal_note']; ?></span></div>
                    <textarea name="internal_note" id="internal_note" cols="50" rows="9" wrap="soft" 
                        style="width:600px"><?php echo $info['internal_note']; ?></textarea>
                </td>
            </tr>
                       
            <?php
            if($cfg->allowAttachments()) { ?>
            <tr>
                <td width="160">
                    <label for="attachment"><?= _('Attachments')?>:</label>
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
            <tr>
                <td width="160">
                    <label><?= _('Ticket Status')?>:</label>
                </td>
                <td width="765">
                    <?php
                    $statusChecked=isset($info['note_ticket_state'])?'checked="checked"':'';
                    if($ticket->isClosed()){ ?>
                        <label><input type="checkbox" name="note_ticket_state" id="note_ticket_state" value="open"
                            <?php echo $statusChecked; ?>> <?= _('Reopen Ticket')?></label>
                   <?php
                    } elseif(0 && $thisstaff->canCloseTickets()) { ?>
                         <label><input type="checkbox" name="note_ticket_state" id="note_ticket_state" value="Closed"
                              <?php echo $statusChecked; ?>> <?= _('Close Ticket')?></label>
                   <?php
                    } elseif($ticket->isAnswered()) { ?>
                        <label>
                            <input type="checkbox" name="note_ticket_state" id="note_ticket_state" value="Unanswered" 
                                <?php echo $statusChecked; ?>>
                            <?= _('Mark Unanswered')?>
                        </label>
                  <?php
                    } else { ?>
                        <label>
                            <input type="checkbox" name="note_ticket_state" id="note_ticket_state" value="Answered"
                                <?php echo $statusChecked; ?>>
                            <?= _('Mark Answered')?>
                        </label>
                  <?php
                    } ?>
                </td>
            </tr>
            </div>
        </table>

       <p  style="padding-left:165px;">
           <input class="btn_sm" type="submit" value="<?=_('Post Note')?>">
           <input class="btn_sm" type="reset" value="<?=_('Reset')?>">
       </p>
   </form>
    <?php
    if($thisstaff->canTransferTickets()) { ?>
    <form id="transfer" action="tickets.php?id=<?php echo $ticket->getId(); ?>#transfer" name="transfer" method="post" enctype="multipart/form-data">
        <?php csrf_token(); ?>
        <input type="hidden" name="ticket_id" value="<?php echo $ticket->getId(); ?>">
        <input type="hidden" name="a" value="transfer">
        <table border="0" cellspacing="0" cellpadding="3">
            <tr>
                <td width="160">&nbsp;</td>
                <td class="error"><?php echo $errors['transfer']; ?></td>
            </tr>
            <tr>
                <td width="160">
                    <label for="deptId"><strong><?= _('Department')?>:</strong></label>
                </td>
                <td width="765">
                    <select id="deptId" name="deptId">
                        <option value="0" selected="selected">&mdash; <?= _('Select Target Department')?> &mdash;</option>
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
                    <label><strong><?= _('Comments')?>:</strong></label>
                </td>
                <td width="765">
                    <span class="faded"><?= _('Enter reasons for the transfer.')?></span>
                    <span class="error">*&nbsp;<?php echo $errors['transfer_message']; ?></span><br>
                    <textarea name="transfer_message" id="transfer_message"
                        cols="80" rows="7" wrap="soft"><?php echo $info['transfer_message']; ?></textarea>
                </td>
            </tr>
        </table>
        <p style="padding-left:165px;">
           <input class="btn_sm" type="submit" value="<?=_('Transfer')?>">
           <input class="btn_sm" type="reset" value="<?=_('Reset')?>">
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
            <tr>
                <td width="160">&nbsp;</td>
                <td>
                <?php 
                    if($ticket->isAssigned())
                        echo sprintf('<em>'._('Ticket is currently assigned to').' <b>%s</b></em>',$ticket->getAssignee());
                ?>
                </td>
            </tr>
            <tr>
                <td width="160">
                    <label for="assignId"><strong><?= _('Assignee')?>:</strong></label>
                </td>
                <td width="765">
                    <select id="assignId" name="assignId">
                        <option value="0" selected="selected">&mdash; <?= _('Select Staff Member OR a Team')?> &mdash;</option>
                        <?php
                        $sid=$tid=0;
                        if(($users=Staff::getAvailableStaffMembers())) {
                            echo '<OPTGROUP label="'._('Staff Members').' ('.count($users).')">';
                            $staffId=$ticket->isAssigned()?$ticket->getStaffId():0;
                            foreach($users as $id => $name) {
                                if($staffId && $staffId==$id)
                                    $name.=' ('._('Assigned').')';

                                $k="s$id";
                                echo sprintf('<option value="%s" %s>%s</option>',
                                        $k,(($info['assignId']==$k)?'selected="selected"':''),$name);
                            }
                            echo '</OPTGROUP>';
                        }

                        if(($teams=Team::getActiveTeams())) {
                            echo '<OPTGROUP label="'._('Teams').' ('.count($teams).')">';
                            $teamId=(!$sid && $ticket->isAssigned())?$ticket->getTeamId():0;
                            foreach($teams as $id => $name) {
                                if($teamId && $teamId==$id)
                                    $name.=' (Assigned)';

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
                    <label><strong><?= _('Comments')?>:</strong><span class='error'>&nbsp;</span></label>
                </td>
                <td width="765">
                    <span class="faded"><?= _('Enter reasons for the assignment or instructions.')?></span>
                    <span class="error">*&nbsp;<?php echo $errors['assign_message']; ?></span><br>
                    <textarea name="assign_message" id="assign_message" cols="80" rows="7" wrap="soft"><?php echo $info['assign_message']; ?></textarea>
                </td>
            </tr>
        </table>
        <p  style="padding-left:165px;">
            <input class="btn_sm" type="submit" value="<?php echo $ticket->isAssigned()?_('Reassign'):_('Assign'); ?>">
            <input class="btn_sm" type="reset" value="<?=_('Reset')?>">
        </p>
    </form>
    <?php
    } ?>
</div>
<div style="display:none;" id="print-options">
    <h3><?= _('Ticket Print Options') ?></h3>
    <a class="close" href="">&times;</a>
    <hr/>
    <form action="tickets.php?id=<?php echo $ticket->getId(); ?>" method="post" id="print-form" name="print-form">
        <?php csrf_token(); ?>
        <input type="hidden" name="a" value="print">
        <input type="hidden" name="id" value="<?php echo $ticket->getId(); ?>">
        <fieldset class="notes">
            <label for="notes"><?= _('Print Notes')?>:</label>
            <input type="checkbox" id="notes" name="notes" value="1"> <?= _('Print <b>Internal</b> Notes/Comments')?>
        </fieldset>
        <fieldset>
            <label for="psize"><?= _('Paper Size')?>:</label>
            <select id="psize" name="psize">
                <option value="">&mdash; <?= _('Select Print Paper Size')?> &mdash;</option>
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
                <input type="reset" value="<?= _('Reset')?>">
                <input type="button" value="<?= _('Cancel')?>" class="close">
            </span>
            <span class="buttons" style="float:right">
                <input type="submit" value="<?= _('Print')?>">
            </span>
         </p>
    </form>
</div>
<script type="text/javascript" src="js/ticket.js"></script>
