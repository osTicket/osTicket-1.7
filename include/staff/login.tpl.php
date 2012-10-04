<?php 
defined('OSTSCPINC') or die('Invalid path');

$info = ($_POST && $errors)?Format::htmlchars($_POST):array();
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title>osTicket:: SCP Login</title>
    <link rel="stylesheet" href="css/login.css" type="text/css" />
    <meta name="robots" content="noindex" />
    <meta http-equiv="cache-control" content="no-cache" />
    <meta http-equiv="pragma" content="no-cache" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <script type="text/javascript" src="../js/jquery-1.7.2.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            $("input:not(.dp):visible:enabled:first").focus();
         });
    </script>
</head>
<body id="loginBody">
<div id="loginBox">
    <h1 id="logo"><a href="index.php">osTicket Staff Control Panel</a></h1>
    <h3><?php echo Format::htmlchars($msg); ?></h3>
    <form action="login.php" method="post">
        <?php csrf_token(); ?>
        <input type="hidden" name="do" value="scplogin">
        <fieldset>
            <input type="text" name="username" id="name" value="<?php echo $info['username']; ?>" placeholder="username" autocorrect="off" autocapitalize="off">
            <input type="password" name="passwd" id="pass" placeholder="password" autocorrect="off" autocapitalize="off">
        </fieldset>
        <input class="submit" type="submit" name="submit" value="Log In">
    </form>
</div>
<div id="copyRights">Copyright &copy; <a href='http://www.osticket.com' target="_blank">osTicket.com</a></div>
</body>
</html>
