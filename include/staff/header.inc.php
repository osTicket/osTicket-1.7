<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <meta http-equiv="cache-control" content="no-cache" />
    <meta http-equiv="pragma" content="no-cache" />
    <title><?php echo ($ost && ($title=$ost->getPageTitle()))?$title:__('osTicket :: Staff Control Panel'); ?></title>
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
		$.datepicker.regional["varlang"]={closeText:"<?php echo __('Done');?>",
		prevText:"<?php echo __('Prev');?>",
		nextText:"<?php echo __('Next');?>",
		currentText:"<?php echo __('Today');?>",
		monthNames:["<?php echo __('January');?>","<?php echo __('February');?>","<?php echo __('March');?>","<?php echo __('April');?>","<?php echo __('May');?>","<?php echo __('June');?>","<?php echo __('July');?>","<?php echo __('August');?>","<?php echo __('September');?>","<?php echo __('October');?>","<?php echo __('November');?>","<?php echo __('December');?>"],
		monthNamesShort:["<?php echo __('Jan');?>","<?php echo __('Feb');?>","<?php echo __('Mar');?>","<?php echo __('Apr');?>","<?php echo __('May');?>","<?php echo __('Jun');?>","<?php echo __('Jul');?>","<?php echo __('Aug');?>","<?php echo __('Sep');?>","<?php echo __('Oct');?>","<?php echo __('Nov');?>","<?php echo __('Dec');?>"],
		dayNames:["<?php echo __('Sunday');?>","<?php echo __('Monday');?>","<?php echo __('Tuesday');?>","<?php echo __('Wednesday');?>","<?php echo __('Thursday');?>","<?php echo __('Friday');?>","<?php echo __('Saturday');?>"],
		dayNamesShort:["<?php echo __('Sun');?>","<?php echo __('Mon');?>","<?php echo __('Tue');?>","<?php echo __('Wed');?>","<?php echo __('Thu');?>","<?php echo __('Fri');?>","<?php echo __('Sat');?>"],
		dayNamesMin:["<?php echo __('Su');?>","<?php echo __('Mo');?>","<?php echo __('Tu');?>","<?php echo __('We');?>","<?php echo __('Th');?>","<?php echo __('Fr');?>","<?php echo __('Sa');?>"],
		weekHeader:"<?php echo __('Wk');?>",
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
        <a href="index.php" id="logo"><?php echo __('osTicket - Customer Support System');?></a>
        <p id="info"><?php echo __('Howdy,');?> <strong><?php echo $thisstaff->getUserName(); ?></strong>
           <?php
            if($thisstaff->isAdmin() && !defined('ADMINPAGE')) { ?>
            | <a href="admin.php"><?php echo __('Admin Panel');?></a>
            <?php }else{ ?>
            | <a href="index.php"><?php echo __('Staff Panel');?></a>
            <?php } ?>
            | <a href="profile.php"><?php echo __('My Preferences');?></a>
            | <a href="logout.php?auth=<?php echo $ost->getLinkToken(); ?>"><?php echo __('Log Out');?></a>
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
