<?php
if(!defined('OSTCLIENTINC')) die('Access Denied');

$email=Format::input($_POST['lemail']?$_POST['lemail']:$_GET['e']);
$ticketid=Format::input($_POST['lticket']?$_POST['lticket']:$_GET['t']);
?>
<h1><?php echo LDAP::ldapClientActive()?'Log&nbsp;In':'Check&nbsp;Ticket&nbsp;Status';?></h1>
<p>To view the status of a ticket or to create a new ticket, provide us with the login details below.</p>
<form action="login.php" method="post" id="clientLogin">
    <?php csrf_token(); ?>
    <strong><?php echo Format::htmlchars($errors['login']); ?></strong>
    <br>
    <div>
        <label for="email"><?php echo LDAP::ldapClientActive()?'Username':'E-Mail Address';?>:</label>
        <input id="email" type="text" name="lemail" size="30" value="<?php echo $email; ?>">
    </div>
    <div>
		<label for="ticketno"><?php echo LDAP::ldapClientActive()?'Password':'Ticket&nbsp;ID';?>:</label>
		<input id="ticketno" <?php echo LDAP::ldapClientActive()?'type="password" autocomplete="off"':'type="text"';?> name="lticket" size="16" value="<?php echo $ticketid; ?>"></td>
    </div>
    <p>
        <input class="btn" type="submit" value="<?php echo LDAP::ldapClientActive()?'Log&nbsp;In':'Check&nbsp;Ticket&nbsp;Status';?>">
    </p>
</form>
<br>
<p>
If this is your first time contacting us or you've lost the ticket ID, please log in and open a new ticket.    
</p>
