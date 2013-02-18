<?php
$title=($cfg && is_object($cfg) && $cfg->getTitle())?$cfg->getTitle():'osTicket :: Support Ticket System';
header("Content-Type: text/html; charset=UTF-8\r\n");
?>
<!DOCTYPE html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title><?php echo Format::htmlchars($title); ?></title>
    <meta name="description" content="customer support platform">
    <meta name="keywords" content="osTicket, Customer support system, support ticket system">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="<?php echo ROOT_PATH; ?>css/osticket.css" media="screen">
    <link rel="stylesheet" href="<?php echo ASSETS_PATH; ?>css/theme.css" media="screen">
    <link rel="stylesheet" href="<?php echo ASSETS_PATH; ?>css/print.css" media="print">
    <script src="<?php echo ROOT_PATH; ?>js/jquery-1.7.2.min.js"></script>
    <script src="<?php echo ROOT_PATH; ?>js/jquery.multifile.js"></script>
    <script src="<?php echo ROOT_PATH; ?>js/osticket.js"></script>
</head>
<body>
    <div id="container">
        <div id="header">
            <a id="logo" href="<?php echo ROOT_PATH; ?>index.php" title="Support Center"><img src="<?php echo ASSETS_PATH; ?>images/logo.png" border=0 alt="Support Center"></a>
            <p>
             <?php
             if($thisclient && is_object($thisclient) && $thisclient->isValid()) {
                 echo $thisclient->getName().'&nbsp;-&nbsp;';
                 ?>
                <?php
                if($cfg->showRelatedTickets()) {?>
                <a href="<?php echo ROOT_PATH; ?>tickets.php">My Tickets <b>(<?php echo $thisclient->getNumTickets(); ?>)</b></a> -
                <?php
                } ?>
                <a href="<?php echo ROOT_PATH; ?>logout.php?auth=<?php echo $ost->getLinkToken(); ?>">Log Out</a>
             <?php
             }elseif($nav){ ?>
                 Guest User - <a href="<?php echo ROOT_PATH; ?>login.php">Log In</a>
              <?php
             } ?>
            </p>
        </div>
        <?php
        if($nav){ ?>
        <ul id="nav">
            <?php
            if($nav && ($navs=$nav->getNavLinks()) && is_array($navs)){
                foreach($navs as $name =>$nav) {
                    echo sprintf('<li><a class="%s %s" href="%s">%s</a></li>%s',$nav['active']?'active':'',$name,(ROOT_PATH.$nav['href']),$nav['desc'],"\n");
                }
            } ?>
        </ul>
        <?php
        }else{ ?>
         <hr>
        <?php
        } ?>
        <div id="content">

         <?php if($errors['err']) { ?>
            <div id="msg_error"><?php echo $errors['err']; ?></div>
         <?php }elseif($msg) { ?>
            <div id="msg_notice"><?php echo $msg; ?></div>
         <?php }elseif($warn) { ?>
            <div id="msg_warning"><?php echo $warn; ?></div>
         <?php } ?>
