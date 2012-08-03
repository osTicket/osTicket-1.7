<?php
if(!defined('OSTCLIENTINC') || !$thisclient || !$ticket || !$ticket->checkClientAccess($thisclient)) die('Access Denied!');

$info=($_POST && $errors)?Format::htmlchars($_POST):array();

$dept = $ticket->getDept();
//Making sure we don't leak out internal dept names
if(!$dept || !$dept->isPublic())
    $dept = $cfg->getDefaultDept();

?>
<div id="main_ticket_view">
	<h1>
		Ticket #<?php echo $ticket->getExtId(); ?>
		<a href="view.php?id=<?php echo $ticket->getExtId(); ?>" title="Reload"><span class="Icon refresh">&nbsp;</span></a>
	</h1>

	<div class="cols_info">
		<div class="col1">
		  <div class="info_box">
            <table cellspacing="0" cellpadding="0">
              <tbody>
                <tr>
                    <th>Ticket Status:</th>
                    <td><?php echo ucfirst($ticket->getStatus()); ?></td>
                </tr>
                <tr>
                    <th>Department:</th>
                    <td><?php echo Format::htmlchars($dept->getName()); ?></td>
                </tr>
                <tr>
                    <th>Create Date:</th>
                    <td><?php echo Format::db_datetime($ticket->getCreateDate()); ?></td>
                </tr>
              </tbody>
           </table>
		  </div>
		</div>
		<div class="col2">
		  <div class="info_box">
            <table cellspacing="0" cellpadding="0">
               <tr>
                   <th>Name:</th>
                   <td><?php echo ucfirst($ticket->getName()); ?></td>
               </tr>
               <tr>
                   <th width="100">Email:</th>
                   <td><?php echo Format::htmlchars($ticket->getEmail()); ?></td>
               </tr>
               <tr>
                   <th>Phone:</th>
                   <td><?php echo $ticket->getPhoneNumber(); ?></td>
               </tr>
            </table>
		  </div>
		</div>
	</div>

	<h2>Subject: <b><?php echo Format::htmlchars($ticket->getSubject()); ?></b></h2>

	<span class="Icon thread">Ticket Thread</span>
	<div id="ticketThread">
<?php    
if($ticket->getThreadCount() && ($thread=$ticket->getClientThread())) {
    $threadType=array('M' => 'message', 'R' => 'response');
    foreach($thread as $entry) {
        //Making sure internal notes are not displayed due to backend MISTAKES!
        if(!$threadType[$entry['thread_type']]) continue;
        $poster = $entry['poster'];
        if($entry['thread_type']=='R' && $cfg->hideStaffName())
            $poster = ' ';
        ?>
        <table class="<?php echo $threadType[$entry['thread_type']]; ?>" cellspacing="0" cellpadding="0">
            <tr><th><?php echo Format::db_datetime($entry['created']); ?> <span class="poster"><?php echo $poster; ?></span></th></tr>
            <tr><td><?php echo Format::display($entry['body']); ?></td></tr>
            <?php
            if($entry['attachments'] && ($links=$ticket->getAttachmentsLinks($entry['id'], $entry['thread_type']))) { ?>
                <tr><td class="info"><?php echo $links; ?></td></tr>
            <?php
            } ?>
        </table>
    <?php
    }
}
?>
	</div>

<?php if($errors['err']) { ?>
    <div id="msg_error"><?php echo $errors['err']; ?></div>
<?php }elseif($msg) { ?>
    <div id="msg_notice"><?php echo $msg; ?></div>
<?php }elseif($warn) { ?>
    <div id="msg_warning"><?php echo $warn; ?></div>
<?php } ?>

	<form id="reply" action="tickets.php?id=<?php echo $ticket->getExtId(); ?>#reply" name="reply" method="post" enctype="multipart/form-data">
    <?php csrf_token(); ?>
    	<p class="form_title">Post a Reply</p>
    	<input type="hidden" name="id" value="<?php echo $ticket->getExtId(); ?>">
    	<input type="hidden" name="a" value="reply">

    	<table border="0" cellspacing="0" cellpadding="0" class="table_form">
	        <tr class="tr_message">
	            <th>Message:</th>
	            <td>
					<div class="textarea_desc">
	                <?php
	                if($ticket->isClosed()) {
	                    $msg='<b>Ticket will be reopened on message post</b>';
	                } else {
	                    $msg='To best assist you, please be specific and detailed';
	                }
	                ?>
	                	<span id="msg"><em><?php echo $msg; ?> </em></span>
						<span class="error">* <?php echo $errors['message']; ?></span>
					</div>
	                <textarea name="message" id="message" wrap="soft"><?php echo $info['message']; ?></textarea>
	            </td>
	        </tr>
        	<?php
        	if($cfg->allowOnlineAttachments()) { ?>
	        <tr>
	            <th>Attachments:</th>
	            <td id="reply_form_attachments" class="attachments">
	                <div class="uploads"></div>
	                <div class="file_input"><input class="multifile" type="file" name="attachments[]" value="" /></div>
	            </td>
	        </tr>
        <?php
        } ?>
    	</table>

    	<p class="form_buttons">
        	<input class="button submit" type="submit" value="Post Reply">
        	<input class="button reset" type="reset" value="Reset">
        	<input class="button cancel" type="button" value="Cancel" onClick="history.go(-1)">
    	</p>
	</form>
</div>