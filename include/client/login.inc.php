<?php
if(!defined('OSTCLIENTINC')) die('Kwaheri');

$email=Format::input($_POST['lemail']?$_POST['lemail']:$_GET['e']);
$ticketid=Format::input($_POST['lticket']?$_POST['lticket']:$_GET['t']);
?>
<h1>Check Ticket Status</h1>

<p class="intro">
	To view the status of a ticket, provide us with the login details below.
</p>

<form action="login.php" method="post" id="clientLogin" class="form_box">
    <?php csrf_token(); ?>

    <div class="form_title">Authentication Required</div>

    <div class="form_row">
        <label for="email">E-Mail Address:</label>
        <input id="email" type="text" name="lemail" size="30" value="<?php echo $email; ?>">
    </div>

    <div class="form_row">
        <label for="ticketno">Ticket ID:</label>
        <input id="ticketno" type="text" name="lticket" size="16" value="<?php echo $ticketid; ?>"></td>
    </div>

    <p class="form_buttons">
        <input class="button" type="submit" value="View Status">
    </p>

</form>

<p class="info">
	If this is your first time contacting us or you've lost the ticket ID, please <a href="open.php">open a new ticket</a>.    
</p>
