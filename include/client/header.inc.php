<?php
$ost->language->load('client/header');
$title=($cfg && is_object($cfg) && $cfg->getTitle())?$cfg->getTitle():$ost->language->get('TEXT_DEFAULT_TITLE');
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
    <link rel="stylesheet" href="<?php echo ASSETS_PATH; ?>css/theme.css" media="screen">
    <link rel="stylesheet" href="<?php echo ASSETS_PATH; ?>css/print.css" media="print">
    <script src="<?php echo ROOT_PATH; ?>js/jquery-1.7.2.min.js"></script>
    <script src="<?php echo ROOT_PATH; ?>js/jquery.multifile.js"></script>
    <script src="<?php echo ROOT_PATH; ?>js/osticket.js"></script>
</head>
<body>
    <div id="container">
        <div id="header">
            <a id="logo" href="<?php echo ROOT_PATH; ?>index.php" title="<?php echo $ost->language->get('TEXT_SUPPORT_CENTER');?>"><img src="<?php echo ASSETS_PATH; ?>images/logo.png" border=0 alt="<?php echo $ost->language->get('TEXT_SUPPORT_CENTER');?>"></a>
            <p>
             <?php
             if($thisclient && is_object($thisclient) && $thisclient->isValid()) {
                 echo $thisclient->getName().'&nbsp;-&nbsp;';
                 ?>
                <?php
                if($cfg->showRelatedTickets()) {?>
                <a href="<?php echo ROOT_PATH; ?>tickets.php"><?php echo $ost->language->get('TEXT_MY_TICKET');?> <b>(<?php echo $thisclient->getNumTickets(); ?>)</b></a> -
                <?php
                } ?>
                <a href="<?php echo ROOT_PATH; ?>logout.php"><?php echo $ost->language->get('TEXT_LOG_OUT');?></a>
             <?php
             }elseif($nav){ ?>
                <?php echo $ost->language->get('TEXT_GUEST');?> - <a href="<?php echo ROOT_PATH; ?>login.php"><?php echo $ost->language->get('TEXT_LOG_IN');?></a>
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
