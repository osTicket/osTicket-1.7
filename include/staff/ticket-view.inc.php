<?php
//Note that ticket obj is initiated in tickets.php.
if(!defined('OSTSCPINC') || !$thisstaff || !is_object($ticket) || !$ticket->getId()) die('Invalid path');

//Make sure the staff is allowed to access the page.
if(!@$thisstaff->isStaff() || !$ticket->checkStaffAccess($thisstaff)) die('Access Denied');

//Re-use the post info on error...savekeyboards.org (Why keyboard? -> some people care about objects than users!!)
$info=($_POST && $errors)?Format::input($_POST):array();

//Auto-lock the ticket if locking is enabled.. If already locked by the user then it simply renews.
if($cfg->getLockTime() && !$ticket->acquireLock($thisstaff->getId(),$cfg->getLockTime()))
    $warn.='Unable to obtain a lock on the ticket';

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
    $warn.='&nbsp;&nbsp;<span class="Icon assignedTicket">Ticket is assigned to '.implode('/', $ticket->getAssignees()).'</span>';
if(!$errors['err'] && ($lock && $lock->getStaffId()!=$thisstaff->getId()))
    $errors['err']='This ticket is currently locked by '.$lock->getStaffName();
if(!$errors['err'] && ($emailBanned=EmailFilter::isBanned($ticket->getEmail())))
    $errors['err']='Email is in banlist! Must be removed before any reply/response';

$unbannable=($emailBanned) ? BanList::includes($ticket->getEmail()) : false;

if($ticket->isOverdue())
    $warn.='&nbsp;&nbsp;<span class="Icon overdueTicket">Marked overdue!</span>';

?>
<table width="940" cellpadding="2" cellspacing="0" border="0">
    <tr>
        <td width="50%">
            <h2><a href="tickets.php?id=<?php echo $ticket->getId(); ?>" title="Ticket #<?php echo $ticket->getExtId(); ?>">Ticket #<?php echo $ticket->getExtId(); ?></a>
            <a href="tickets.php?id=<?php echo $ticket->getId(); ?>" title="Reload" class="reload">Reload</a></h2>
        </td>
        <td width="50%" class="right_align">
            <?php
            /*
                YOU WILL NEED TO EDIT SOME OF THESE VALUES!

                The option's value attribute needs to be the
                URL to redirect to.

                For options with a confirmation dialog, this URL
                is overridden by the one set in the dialog form,
                but it's probably a good idea to include it just
                in case.
            */
            ?>
            <select name="ticket-quick-actions" id="ticket-quick-actions">
                <option value="" selected="selected">&mdash; Select Action &mdash;</option>
                <option class="print" value="tickets.php?id=<?php echo $ticket->getId(); ?>" data-dialog="print-options">Print Ticket</option>
                <?php if($thisstaff->canEditTickets()): ?>
                    <option class="edit" value="tickets.php?id=<?php echo $ticket->getId(); ?>&a=edit" data-dialog="">Edit Ticket</option>
                <?php endif; ?>
                <?php if($thisstaff->canCloseTickets()): ?>
                    <option class="close" value="tickets.php?id=<?php echo $ticket->getId(); ?>&a=close" data-dialog="close-confirm">Close Ticket</option>
                <?php endif; ?>
                <?php if($thisstaff->canBanEmails()): ?>
                    <option class="ban" value="tickets.php?id=<?php echo $ticket->getId(); ?>&a=ban" data-dialog="">Ban Email &amp; Close</option>
                <?php endif; ?>
                <?php if($thisstaff->canDeleteTickets()): ?>
                    <option class="delete" value="tickets.php?id=<?php echo $ticket->getId(); ?>&a=delete" data-dialog="delete-confirm">Delete Ticket</option>
                <?php endif; ?>
            </select>
        </td>
    </tr>
</table>
<table class="ticket_info" cellspacing="0" cellpadding="0" width="940" border="0">
    <tr>
        <td width="50">
            <table border="0" cellspacing="" cellpadding="4" width="100%">
                <tr>
                    <th width="100">Status:</th>
                    <td><?php echo ucfirst($ticket->getStatus()); ?></td>
                </tr>
                <tr>
                    <th>Priority:</th>
                    <td><?php echo $ticket->getPriority(); ?></td>
                </tr>
                <tr>
                    <th>Department:</th>
                    <td><?php echo Format::htmlchars($ticket->getDeptName()); ?></td>
                </tr>
                <tr>
                    <th>Create Date:</th>
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
                    <th>Email:</th>
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
                    <th>Phone:</th>
                    <td><?php echo $ticket->getPhoneNumber(); ?></td>
                </tr>
                <tr>
                    <th>Source:</th>
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
                    <th width="100">Assigned To:</th>
                    <td>
                        <?php
                        if($ticket->isAssigned())
                            echo Format::htmlchars(implode('/', $ticket->getAssignees()));
                        else
                            echo '<span class="faded">&mdash; Unassigned &mdash;</span>';
                        ?>
                    </td>
                </tr>
                <?php
                } else { ?>
                <tr>
                    <th width="100">Closed By:</th>
                    <td>
                        <?php
                        if(($staff = $ticket->getStaff()))
                            echo Format::htmlchars($staff->getName());
                        else
                            echo '<span class="faded">&mdash; Unknown &mdash;</span>';
                        ?>
                    </td>
                </tr>
                <?php
                } ?>
                <tr>
                    <th nowrap>Last Response:</th>
                    <td><?php echo Format::db_datetime($ticket->getLastRespDate()); ?></td>
                </tr>
                <?php
                if($ticket->isOpen()){ ?>
                <tr>
                    <th>Due Date:</th>
                    <td><?php echo Format::db_datetime($ticket->getDueDate()); ?></td>
                </tr>
                <?php
                }else { ?>
                <tr>
                    <th>Close Date:</th>
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
                    <th width="100">Subject:</th>
                    <td><?php echo Format::htmlchars(Format::truncate($ticket->getSubject(),200)); ?></td>
                </tr>
                <tr>
                    <th>Help Topic:</th>
                    <td><?php echo Format::htmlchars($ticket->getHelpTopic()); ?></td>
                </tr>
                <tr>
                    <th nowrap>Last Message:</th>
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
    <li><a class="active" id="toggle_ticket_thread" href="#">Ticket Thread (<?php echo $tcount; ?>)</a></li>
    <?php
    if(!$cfg->showNotesInline()) {?>
    <li><a id="toggle_notes" href="#">Internal Notes (<?php echo $ticket->getNumNotes(); ?>)</a></li>
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
        echo "<p>No internal notes found.</p>";
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
        echo '<p>Error fetching ticket thread - get technical help.</p>';
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
        <li><a id="reply_tab" href="#reply">Post Reply</a></li>
        <li><a id="note_tab" href="#note">Post Internal Note</a></li>
        <?php
        if($thisstaff->canTransferTickets()) { ?>
        <li><a id="transfer_tab" href="#transfer">Dept. Transfer</a></li>
        <?php
        }

        if($thisstaff->canAssignTickets()) { ?>
        <li><a id="assign_tab" href="#assign"><?php echo $ticket->isAssigned()?'Reassign Ticket':'Assign Ticket'; ?></a></li>
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
                        <option value="0" selected="selected">Select a canned response</option>
                        <?php
                        foreach($cannedResponses as $id =>$title) {
                            echo sprintf('<option value="%d">%s</option>',$id,$title);
                        }
                        ?>
                    </select>
                    &nbsp;&nbsp;&nbsp;
                    <label><input type='checkbox' value='1' name="append" id="append" checked="checked"> Append</label>
                </td>
            </tr>
            <?php
            }?>
            <tr>
                <td width="160">
                    <label><strong>Response:</strong></label>
                </td>
                <td width="765">
                    <textarea name="response" id="response" cols="50" rows="9" wrap="soft"><?php echo $info['response']; ?></textarea>
                </td>
            </tr>
            <?php
            if($cfg->allowAttachments()) { ?>
            <tr>
                <td width="160">
                    <label for="attachment">Attachments:</label>
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
                    <label for="signature" class="left">Signature:</label>
                </td>
                <td width="765">
                    <?php
                    $info['signature']=$info['signature']?$info['signature']:$thisstaff->getDefaultSignatureType();
                    ?>
                    <label><input type="radio" name="signature" value="none" checked="checked"> None</label>
                    <?php
                    if($thisstaff->getSignature()) {?>
                    <label><input type="radio" name="signature" value="mine"
                        <?php echo ($info['signature']=='mine')?'checked="checked"':''; ?>> My signature</label>
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
                    <label><strong>Ticket Status:</strong></label>
                </td>
                <td width="765">
                    <?php
                    $statusChecked=isset($info['reply_ticket_status'])?'checked="checked"':'';
                    if($ticket->isClosed()) { ?>
                        <label><input type="checkbox" name="reply_ticket_status" id="reply_ticket_status" value="Open"
                            <?php echo $statusChecked; ?>> Reopen on Reply</label>
                   <?php
                    } elseif($thisstaff->canCloseTickets()) { ?>
                         <label><input type="checkbox" name="reply_ticket_status" id="reply_ticket_status" value="Closed"
                              <?php echo $statusChecked; ?>> Close on Reply</label>
                   <?php
                    } ?>
                </td>
            </tr>
            <?php
            } ?>
            </div>
        </table>
        <p  style="padding-left:165px;">
            <input class="btn_sm" type="submit" value="Post Reply">
            <input class="btn_sm" type="reset" value="Reset">
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
                    <label><strong>Title:</strong></label>
                </td>
                <td width="765">
                    <input type="text" name="title" id="title" size="45" value="<?php echo $info['title']; ?>" >
                    &nbsp;<span class="error">*&nbsp;<?php echo $errors['title']; ?></span>
                </td>
            </tr>
            <tr>
                <td width="160">
                    <label><strong>Note:</strong></label>
                </td>
                <td width="765">
                    <div><span class="faded">Internal note details</span>&nbsp;
                        <span class="error">*&nbsp;<?php echo $errors['internal_note']; ?></span></div>
                    <textarea name="internal_note" id="internal_note" cols="50" rows="9" wrap="soft"
                        style="width:600px"><?php echo $info['internal_note']; ?></textarea>
                </td>
            </tr>

            <?php
            if($cfg->allowAttachments()) { ?>
            <tr>
                <td width="160">
                    <label for="attachment">Attachments:</label>
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
                    <label>Ticket Status:</label>
                </td>
                <td width="765">
                    <?php
                    $statusChecked=isset($info['note_ticket_state'])?'checked="checked"':'';
                    if($ticket->isClosed()){ ?>
                        <label><input type="checkbox" name="note_ticket_state" id="note_ticket_state" value="open"
                            <?php echo $statusChecked; ?>> Reopen Ticket</label>
                   <?php
                    } elseif(0 && $thisstaff->canCloseTickets()) { ?>
                         <label><input type="checkbox" name="note_ticket_state" id="note_ticket_state" value="Closed"
                              <?php echo $statusChecked; ?>> Close Ticket</label>
                   <?php
                    } elseif($ticket->isAnswered()) { ?>
                        <label>
                            <input type="checkbox" name="note_ticket_state" id="note_ticket_state" value="Unanswered"
                                <?php echo $statusChecked; ?>>
                            Mark Unanswered
                        </label>
                  <?php
                    } else { ?>
                        <label>
                            <input type="checkbox" name="note_ticket_state" id="note_ticket_state" value="Answered"
                                <?php echo $statusChecked; ?>>
                            Mark Answered
                        </label>
                  <?php
                    } ?>
                </td>
            </tr>
            </div>
        </table>

       <p  style="padding-left:165px;">
           <input class="btn_sm" type="submit" value="Post Note">
           <input class="btn_sm" type="reset" value="Reset">
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
                    <label for="deptId"><strong>Department:</strong></label>
                </td>
                <td width="765">
                    <select id="deptId" name="deptId">
                        <option value="0" selected="selected">&mdash; Select Target Department &mdash;</option>
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
                    <label><strong>Comments:</strong></label>
                </td>
                <td width="765">
                    <span class="faded">Enter reasons for the transfer.</span>
                    <span class="error">*&nbsp;<?php echo $errors['transfer_message']; ?></span><br>
                    <textarea name="transfer_message" id="transfer_message"
                        cols="80" rows="7" wrap="soft"><?php echo $info['transfer_message']; ?></textarea>
                </td>
            </tr>
        </table>
        <p style="padding-left:165px;">
           <input class="btn_sm" type="submit" value="Transfer">
           <input class="btn_sm" type="reset" value="Reset">
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
                        echo sprintf('<em>Ticket is currently assigned to <b>%s</b></em>',$ticket->getAssignee());
                ?>
                </td>
            </tr>
            <tr>
                <td width="160">
                    <label for="assignId"><strong>Assignee:</strong></label>
                </td>
                <td width="765">
                    <select id="assignId" name="assignId">
                        <option value="0" selected="selected">&mdash; Select Staff Member OR a Team &mdash;</option>
                        <?php
                        $sid=$tid=0;
                        if(($users=Staff::getAvailableStaffMembers())) {
                            echo '<OPTGROUP label="Staff Members ('.count($users).')">';
                            $staffId=$ticket->isAssigned()?$ticket->getStaffId():0;
                            foreach($users as $id => $name) {
                                if($staffId && $staffId==$id)
                                    $name.=' (Assigned)';

                                $k="s$id";
                                echo sprintf('<option value="%s" %s>%s</option>',
                                        $k,(($info['assignId']==$k)?'selected="selected"':''),$name);
                            }
                            echo '</OPTGROUP>';
                        }

                        if(($teams=Team::getActiveTeams())) {
                            echo '<OPTGROUP label="Teams ('.count($teams).')">';
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
                    <label><strong>Comments:</strong><span class='error'>&nbsp;</span></label>
                </td>
                <td width="765">
                    <span class="faded">Enter reasons for the assignment or instructions.</span>
                    <span class="error">*&nbsp;<?php echo $errors['assign_message']; ?></span><br>
                    <textarea name="assign_message" id="assign_message" cols="80" rows="7" wrap="soft"><?php echo $info['assign_message']; ?></textarea>
                </td>
            </tr>
        </table>
        <p  style="padding-left:165px;">
            <input class="btn_sm" type="submit" value="<?php echo $ticket->isAssigned()?'Reassign':'Assign'; ?>">
            <input class="btn_sm" type="reset" value="Reset">
        </p>
    </form>
    <?php
    } ?>
</div>
<div style="display:none;" id="print-options" class="dialog">
    <h3>Ticket Print Options</h3>
    <a class="close" href="">&times;</a>
    <hr/>
    <form action="tickets.php?id=<?php echo $ticket->getId(); ?>" method="post" id="print-form" name="print-form">
        <?php csrf_token(); ?>
        <input type="hidden" name="a" value="print">
        <input type="hidden" name="id" value="<?php echo $ticket->getId(); ?>">
        <fieldset class="notes">
            <label for="notes">Print Notes:</label>
            <input type="checkbox" id="notes" name="notes" value="1"> Print <b>Internal</b> Notes/Comments
        </fieldset>
        <fieldset>
            <label for="psize">Paper Size:</label>
            <select id="psize" name="psize">
                <option value="">&mdash; Select Print Paper Size &mdash;</option>
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
                <input type="reset" value="Reset">
                <input type="button" value="Cancel" class="close">
            </span>
            <span class="buttons" style="float:right">
                <input type="submit" value="Print">
            </span>
         </p>
    </form>
</div>
<div style="display:none;" id="close-confirm" class="dialog">
    <h3>Close Ticket</h3>
    <a class="close" href="">&times;</a>
    <hr/>
    <form action="tickets.php?id=<?php echo $ticket->getId(); ?>&a=close" method="post">
        Are you sure you want to close this ticket?
        <p class="full-width">
            <span class="buttons" style="float:left">
                <input type="button" value="Cancel" class="close">
            </span>
            <span class="buttons" style="float:right">
                <input type="submit" value="Close Ticket">
            </span>
         </p>
    </form>
</div>
<div style="display:none;" id="delete-confirm" class="dialog">
    <h3>Delete Ticket</h3>
    <a class="close" href="">&times;</a>
    <hr/>
    <form action="tickets.php?id=<?php echo $ticket->getId(); ?>&a=delete" method="post">
        Are you sure you want to close this ticket?<br>
        <strong class="error">This change cannot be undone.</strong>
        <p class="full-width">
            <span class="buttons" style="float:left">
                <input type="button" value="Cancel" class="close">
            </span>
            <span class="buttons" style="float:right">
                <input type="submit" value="Delete Ticket">
            </span>
         </p>
    </form>
</div>
<script type="text/javascript" src="js/ticket.js"></script>
