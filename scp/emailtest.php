<?php
/*********************************************************************
	emailtest.php

	Email Diagnostic 

	Peter Rotich <peter@osticket.com>
	Copyright (c)  2006-2012 osTicket
	http://www.osticket.com

	Released under the GNU General Public License WITHOUT ANY WARRANTY.
	See LICENSE.TXT for details.

	vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require('admin.inc.php');
include_once(INCLUDE_DIR.'class.email.php');
include_once(INCLUDE_DIR.'class.csrf.php');
$info=array();
$info['out_subj']='osTicket test outgoing email';
$info['in_subj']='osTicket test incoming email';

// authorize email like "Name <email@email.com>"
function MyEmailGetErrors($email){
	if(preg_match('#([^<]*)<([^>]+)>#',$email,$matches)){
		$name =$matches[1];
		$email=$matches[2];
	}
	if($name and !preg_match('#^[a-z0-9\s\.]+$#i',$name)){
		$error = "Invalid name";
	}
	if (!Validator::is_email($email)){
		$error = "Invalid email address";		
	}
	return $error;
}


if($_POST){
	$errors=array();
	$email=null;
	$info=$_POST;
	
	if($_POST['form']=='out'){
		if(!$_POST['out_email_id'] || !($email=Email::lookup($_POST['out_email_id'])))
			$errors['out_email_id']='Select from email address';

		if($_POST['out_email']){
			$err_mail=MyEmailGetErrors($_POST['out_email']) and		$errors['out_email']=$err_mail;
		}
		else{
			$errors['out_email']='From email address required';
		}

		if(!$_POST['out_subj'])
			$errors['out_subj']='Subject required';

		if(!$_POST['out_message'])
			$errors['out_message']='Message required';

		if(!$errors && $email){
			if($email->send($_POST['out_email'],$_POST['out_subj'],$_POST['out_message']))
				$msg='Test email sent successfully to <b>'.Format::htmlchars($_POST['out_email']).'</b>';
			else
				$errors['err']='Error sending email - try again.';
		}
		elseif($errors['err']){
			$errors['err']='Error sending email - try again.';
		}
	}
	elseif($_POST['form']=='in'){
		if($_POST['in_email']){
			$err_mail=MyEmailGetErrors($_POST['in_email']) and	$errors['in_email']=$err_mail;
		}
		else{
			$errors['in_email']='From email address required';
		}

	   if(!$_POST['in_email_id'] || !($email=Email::lookup($_POST['in_email_id'])))
			$errors['in_email_id']='Select destination email address';

	   if(!$_POST['in_subj'])
			$errors['in_subj']='Subject required';

	   if(!$_POST['in_message'])
			$errors['in_message']='Message required';

	   if(!$errors && $email){
			//mail($email->getEmail() ,$_POST['in_subj'],$_POST['in_message'],"From: ". $_POST['in_email']) 
			if( $email->sendmail($email->getEmail() ,$_POST['in_subj'],$_POST['in_message'],trim($_POST['in_email'])) )
				$msg='Test email sent successfully to <b>'.Format::htmlchars($email->getEmail()). "</b> (".Format::htmlchars($email->getName()).")";
			else
				$errors['err']='Error sending email - try again.';
		}
		elseif($errors['err']){
			$errors['err']='Error sending email - try again.';
		}
		
	}
	else{
		$errors['err']='Unknown Error';
	}

}
$info=Format::htmlchars($info);
$nav->setTabActive('emails');
require(STAFFINC_DIR.'header.inc.php');
$options_outgoing='';
$options_incoming='';
$sql='SELECT email_id,email,name,smtp_active FROM '.EMAIL_TABLE.' email ORDER by name';
 if(($res=db_query($sql)) && db_num_rows($res)){
	 while(list($id,$email,$name,$smtp)=db_fetch_row($res)){
		 $selected_outgoing=($info['out_email_id'] && $id==$info['out_email_id'])?' selected="selected"':'';
		 $selected_incoming=($info['in_email_id'] && $id==$info['in_email_id'])?' selected="selected"':'';
		 if($name)
			 $email=Format::htmlchars("$name <$email>");
		 if($smtp)
			 $email.=' (SMTP)';
		$options_outgoing .="	<option value=\"$id\"$selected_outgoing>$email</option>\n";
		$options_incoming .="	<option value=\"$id\"$selected_incoming>$email</option>\n";
	 }
 }

?>

<!-- 
I will obviously merge this in the SCP stylesheet, but 
If you would accept my previous '"CSS enhancements"  pull, 
i would merge this directly to my submitted scp stylesheet 
-->

<style>
.form_div{
	margin-bottom: 10px;
	clear:both;
}
.form_tabs_nav{
	margin-bottom:10px;
}
#emailtest_form_tabs .form_tabs_nav{
	margin-top:10px;
	margin-bottom:20px;	
}
.form_tabs_nav UL {
	overflow: hidden;
	padding:0;
	margin:0;
	margin-bottom: -1px;
	margin-left: 20px;
	position: relative; /*fix ie z-index */
}
.form_tabs_nav_bottom{
	border-bottom:1px solid #ccc;	
}

.form_tabs_nav LI{
	float:left;
	padding:0;
	margin:0;
	padding:2px 10px;
	list-style:none;
	margin-right:10px;
	border: 1px solid #ccc;
	background: #f5f5f5;
	border-radius: 5px 5px 0 0 ;
	-moz-border-radius: 5px 5px 0 0 ;
	-webkit-border-radius: 5px 5px 0 0 ;
}
.form_tabs_nav LI.selected{
	background: #fff;
	border-bottom-color: #fff;
}
.form_help{
	margin-top: 30px;
	padding: 10px;
	background: rgba(255,252,17,0.36);
	border: 1px solid #eee;
}
</style>
<script>
$(document).ready(function(){

	/* 

	This JS code should obviously be moved to the main js file, but I would prefer if we could first add 
	the "tabs" widget to the included jquery UI (jquery-ui-1.8.18.custom.min.js) : 
	
	this code would then simply become something like : $("jsFormTabs").tabs();
	and could them be easely used in other forms, if needed

	With UI.tabs It would also return to the selected tab after posting
	
	*/

	$('.jsForm2').hide();
	$(".jsFormTabsNav A").click(function(event){
		var rel=$(this).attr('rel');
		$('.jsFormTabsNav A').each(function(){
			$(this).closest('LI').removeClass("selected");
		});
		$(this).closest('LI').addClass("selected");
		if(rel=='jsForm2'){
			$('.jsForm1').hide();
			$('.jsForm2').show();
		}
		else{
			$('.jsForm2').hide();
			$('.jsForm1').show();
		}
	});

});
</script>

<div id="emailtest_form_tabs" class="form_tabs jsFormTabs">

	<div class="form_tabs_nav">
		<ul class="jsFormTabsNav" >
			<li class="selected"><a href="#emailtest_form1" rel="jsForm1">Test Outgoing Email</a></li> 
			<li><a href="#emailtest_form2" rel="jsForm2">Test Incoming Email</a></li>
		</ul>
		<div class="form_tabs_nav_bottom"></div>
	</div>

	<div id="emailtest_form1" class="form_div jsForm1">
		<h2>Test Outgoing Email</h2>
		<form action="emailtest.php" method="post" id="emailtest">
			<?php csrf_token(); ?>
			<input type="hidden" name="form" value="out">
			<table class="form_table" border="0" cellspacing="0" cellpadding="2">
				<thead>
					<tr>
						<th colspan="2" class="th_message"><em>Emails delivery depends on your server settings (php.ini) and/or email SMTP configuration.</em></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td class="form_field required">From:</td>
						<td class="form_value">
							<select name="out_email_id">
								<option value="0">&mdash; Select FROM Email &mdash;</option>
								<?php echo $options_outgoing; ?>
							</select>
							&nbsp;<span class="error">*&nbsp;<?php echo $errors['out_email_id']; ?></span>
						</td>
					</tr>
					<tr>
						<td class="form_field required">To:</td>
						<td class="form_value">
							<input type="text" size="60" name="out_email" value="<?php echo $info['out_email']; ?>">
							&nbsp;<span class="error">*&nbsp;<?php echo $errors['out_email']; ?></span>
						</td>
					</tr>
					<tr>
						<td class="form_field required">Subject:</td>
						<td class="form_value">
							<input type="text" size="60" name="out_subj" value="<?php echo $info['out_subj']; ?>">
							&nbsp;<span class="error">*&nbsp;<?php echo $errors['out_subj']; ?></span>
						</td>
					</tr>
					<tr>
						<td colspan=2>
							<em><strong>Message</strong>: email message to send.</em>&nbsp;<span class="error">*&nbsp;<?php echo $errors['out_message']; ?></span><br>
							<textarea name="out_message" cols="21" rows="10" style="width: 90%;"><?php echo $info['out_message']; ?></textarea>
						</td>
					</tr>
				</tbody>
			</table>
			<p class='form_buttons'>
				<input type="submit" name="submit" value="Send Message">
				<input type="reset"	 name="reset"  value="Reset">
				<input type="button" name="cancel" value="Cancel" onclick='window.location.href="emails.php"'>
			</p>
		</form>
	</div>

	<a name="form2"></a>
	<div id="emailtest_form2" class="form_div jsForm2">
		<h2>Test Incoming Email</h2>
		<form action="emailtest.php" method="post" id="emailtest">
			<?php csrf_token(); ?>
			<input type="hidden" name="form" value="in">
			<table class="form_table" border="0" cellspacing="0" cellpadding="2">
				<thead>
					<tr>
						<th colspan="2" class="th_message"><em>Send emails to the Ticket system</em></th>
					</tr>
				</thead>
				<tbody>
					<tr class="tr_first">
						<td class="form_field required">From:</td>
						<td class="form_value">
							<input type="text" size="60" name="in_email" value="<?php echo $info['in_email']; ?>">
							&nbsp;<span class="error">*&nbsp;<?php echo $errors['in_email']; ?></span>
						</td>
					</tr>
					<tr>
						<td class="form_field required">To:</td>
						<td class="form_value">
							<select name="in_email_id">
								<option value="0">&mdash; Select TO Email &mdash;</option>
								<?php echo $options_incoming; ?>
							</select>
							&nbsp;<span class="error">*&nbsp;<?php echo $errors['in_email_id']; ?></span>
						</td>
					</tr>
					<tr>
						<td class="form_field required">Subject:</td>
						<td class="form_value">
							<input type="text" size="60" name="in_subj" value="<?php echo $info['in_subj']; ?>">
							&nbsp;<span class="error">*&nbsp;<?php echo $errors['in_subj']; ?></span>
						</td>
					</tr>
					<tr class='tr_last'>
						<td colspan=2  class="form_value">
							<em><strong>Message</strong>: email message to send.</em>&nbsp;<span class="error">*&nbsp;<?php echo $errors['in_message']; ?></span><br>
							<textarea name="in_message" cols="21" rows="10" style="width: 90%;"><?php echo $info['in_message']; ?></textarea>
						</td>
					</tr>
				</tbody>
			</table>
			<p class='form_buttons'>
				<input type="submit" name="submit" value="Send Message">
				<input type="reset"	 name="reset"  value="Reset">
				<input type="button" name="cancel" value="Cancel" onclick='window.location.href="emails.php"'>
			</p>
		</form>
	</div>
</div>

<div id="emailtest_help" class="form_help">
Emails can be formated as "<i>user@domain.com</i>" or "<i>Name &lt;user@email.com&gt;</i>"
</div>

<?php
include(STAFFINC_DIR.'footer.inc.php');
?>
