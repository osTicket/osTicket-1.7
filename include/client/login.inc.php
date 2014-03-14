<?php
if(!defined('OSTCLIENTINC')) die(lang('access_denied'));

$email=Format::input($_POST['lemail']?$_POST['lemail']:$_GET['e']);
$ticketid=Format::input($_POST['lticket']?$_POST['lticket']:$_GET['t']);
?>
<h1><?php echo lang('check_ticket_stat'); ?></h1>
<p><?php echo lang('prov_login_details'); ?></p>
<?php session_start(); if (isset($_SESSION['error'])): ?>
    <p id="sysmsg" class="error">
        <?php echo lang('invalid_code_or_closed_ticket'); ?>
    </p>
    <script type="text/javascript">setTimeout("$('#sysmsg').fadeOut('slow');",1500);</script>
<?php unset($_SESSION['error']); endif; ?>

<form action="login.php" method="post" id="clientLogin">
    <?php csrf_token(); ?>
    <strong><?php echo Format::htmlchars($errors['login']); ?></strong>
    <br>
    <div>
        <label for="email"><?php echo lang('email_address'); ?>:</label>
        <input id="email" type="text" name="lemail" size="30" value="<?php echo $email; ?>">
    </div>
    <div>
        <label for="ticketno"><?php echo lang('tickets_id'); ?>:</label>
        <input id="ticketno" type="text" name="lticket" size="16" value="<?php echo $ticketid; ?>"></td>
    </div>
    <p>
        <input class="btn" type="submit" value="<?php echo lang('view_status'); ?>">
    </p>
</form>
<br>
<p>
<?php echo lang('first_contact'); ?> <a href="open.php"><?php echo lang('open_new_ticket'); ?></a>.    
</p>
