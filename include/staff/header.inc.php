<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <meta http-equiv="cache-control" content="no-cache" />
    <meta http-equiv="pragma" content="no-cache" />
    <title><?php echo ($ost && ($title=$ost->getPageTitle()))?$title:_('osTicket :: Staff Control Panel'); ?></title>
    <!--[if IE]>
    <style type="text/css">
        .tip_shadow { display:block !important; }
    </style>
    <![endif]-->
    <script type="text/javascript" src="../js/jquery-1.7.2.min.js"></script>
    <script type="text/javascript" src="../js/jquery-ui-1.8.18.custom.min.js"></script>
    <script type="text/javascript" src="../js/jquery.multifile.js"></script>
    <script type="text/javascript" src="./js/tips.js"></script>
    <script type="text/javascript" src="./js/nicEdit.js"></script>
    <script type="text/javascript" src="./js/bootstrap-typeahead.js"></script>
    <script type="text/javascript" src="./js/scp.js"></script>
    <link rel="stylesheet" href="./css/scp.css" media="screen">
    <link rel="stylesheet" href="./css/typeahead.css" media="screen">
    <link type="text/css" href="../css/ui-lightness/jquery-ui-1.8.18.custom.css" rel="stylesheet" />
    <link type="text/css" rel="stylesheet" href="../css/font-awesome.min.css">
    <link type="text/css" rel="stylesheet" href="./css/dropdown.css">
    <script type="text/javascript" src="./js/jquery.dropdown.js"></script>
	
	<script>
	jQuery(function($){
		$.datepicker.regional["varlang"]={closeText:"<?php echo _('Done');?>",
		prevText:"<?php echo _('Prev');?>",
		nextText:"<?php echo _('Next');?>",
		currentText:"<?php echo _('Today');?>",
		monthNames:["<?php echo _('January');?>","<?php echo _('February');?>","<?php echo _('March');?>","<?php echo _('April');?>","<?php echo _('May');?>","<?php echo _('June');?>","<?php echo _('July');?>","<?php echo _('August');?>","<?php echo _('September');?>","<?php echo _('October');?>","<?php echo _('November');?>","<?php echo _('December');?>"],
		monthNamesShort:["<?php echo _('Jan');?>","<?php echo _('Feb');?>","<?php echo _('Mar');?>","<?php echo _('Apr');?>","<?php echo _('May');?>","<?php echo _('Jun');?>","<?php echo _('Jul');?>","<?php echo _('Aug');?>","<?php echo _('Sep');?>","<?php echo _('Oct');?>","<?php echo _('Nov');?>","<?php echo _('Dec');?>"],
		dayNames:["<?php echo _('Sunday');?>","<?php echo _('Monday');?>","<?php echo _('Tuesday');?>","<?php echo _('Wednesday');?>","<?php echo _('Thursday');?>","<?php echo _('Friday');?>","<?php echo _('Saturday');?>"],
		dayNamesShort:["<?php echo _('Sun');?>","<?php echo _('Mon');?>","<?php echo _('Tue');?>","<?php echo _('Wed');?>","<?php echo _('Thu');?>","<?php echo _('Fri');?>","<?php echo _('Sat');?>"],
		dayNamesMin:["<?php echo _('Su');?>","<?php echo _('Mo');?>","<?php echo _('Tu');?>","<?php echo _('We');?>","<?php echo _('Th');?>","<?php echo _('Fr');?>","<?php echo _('Sa');?>"],
		weekHeader:"<?php echo _('Wk');?>",
		dateFormat:"mm/dd/yy",
		firstDay:0,
		isRTL:!1,
		showMonthAfterYear:!1,
		yearSuffix:""
		};
		$.datepicker.setDefaults($.datepicker.regional['varlang']);
	});
	</script>
    <?php
    if($ost && ($headers=$ost->getExtraHeaders())) {
        echo "\n\t".implode("\n\t", $headers)."\n";
    }
    ?>
</head>
<body>
<div id="container">
    <?php
    if($ost->getError())
        echo sprintf('<div id="error_bar">%s</div>', $ost->getError());
    elseif($ost->getWarning())
        echo sprintf('<div id="warning_bar">%s</div>', $ost->getWarning());
    elseif($ost->getNotice())
        echo sprintf('<div id="notice_bar">%s</div>', $ost->getNotice());
    ?>
    <div id="header">
        <a href="index.php" id="logo"><?php echo _('osTicket - Customer Support System');?></a>
        <p id="info"><?php echo _('Howdy,');?> <strong><?php echo $thisstaff->getUserName(); ?></strong>
           <?php
            if($thisstaff->isAdmin() && !defined('ADMINPAGE')) { ?>
            | <a href="admin.php"><?php echo _('Admin Panel');?></a>
            <?php }else{ ?>
            | <a href="index.php"><?php echo _('Staff Panel');?></a>
            <?php } ?>
            | <a href="profile.php"><?php echo _('My Preferences');?></a>
            | <a href="logout.php?auth=<?php echo $ost->getLinkToken(); ?>"><?php echo _('Log Out');?></a>
        </p>
    </div>
    <ul id="nav">
        <?php
        if(($tabs=$nav->getTabs()) && is_array($tabs)){
            foreach($tabs as $name =>$tab) {
                echo sprintf('<li class="%s"><a href="%s">%s</a>',$tab['active']?'active':'inactive',$tab['href'],$tab['desc']);
                if(!$tab['active'] && ($subnav=$nav->getSubMenu($name))){
                    echo "<ul>\n";
                    foreach($subnav as $item) {
                        echo sprintf('<li><a class="%s" href="%s" title="%s" >%s</a></li>',
                                $item['iconclass'],$item['href'],$item['title'],$item['desc']);
                    }
                    echo "\n</ul>\n";
                }
                echo "\n</li>\n";
            }
        } ?>
    </ul>
    <ul id="sub_nav">
        <?php
        if(($subnav=$nav->getSubMenu()) && is_array($subnav)){
            $activeMenu=$nav->getActiveMenu();
            if($activeMenu>0 && !isset($subnav[$activeMenu-1]))
                $activeMenu=0;
            foreach($subnav as $k=> $item) {
                if($item['droponly']) continue;
                $class=$item['iconclass'];
                if ($activeMenu && $k+1==$activeMenu
                        or (!$activeMenu
                            && (strpos(strtoupper($item['href']),strtoupper(basename($_SERVER['SCRIPT_NAME']))) !== false
                                or ($item['urls']
                                    && in_array(basename($_SERVER['SCRIPT_NAME']),$item['urls'])
                                    )
                                )))
                    $class="$class active";

                echo sprintf('<li><a class="%s" href="%s" title="%s" >%s</a></li>',$class,$item['href'],$item['title'],$item['desc']);
            }
        }
        ?>
    </ul>
    <div id="content">
        <?php if($errors['err']) { ?>
            <div id="msg_error"><?php echo $errors['err']; ?></div>
        <?php }elseif($msg) { ?>
            <div id="msg_notice"><?php echo $msg; ?></div>
        <?php }elseif($warn) { ?>
            <div id="msg_warning"><?php echo $warn; ?></div>
        <?php } ?>

