<?php
/*********************************************************************
    index.php

    Helpdesk landing page. Please customize it to fit your needs.

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2012 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require('client.inc.php');
$section = 'home';
require(CLIENTINC_DIR.'header.inc.php');
$ost->language->load('home');
?>

<div id="landing_page">
    <h1><?php echo $ost->language->get('TEXT_WELCOME_TITLE');?></h1>
    <p>
        <?php echo $ost->language->get('TEXT_WELCOME_MSG');?>
    </p>

    <div id="new_ticket">
        <h3><?php echo $ost->language->get('TEXT_OPEN_NEW_TICKET_TITLE');?></h3>
        <br>
        <div><?php echo $ost->language->get('TEXT_OPEN_NEW_TICKET_MSG');?></div>
        <p>
            <a href="open.php" class="green button"><?php echo $ost->language->get('LABEL_OPEN_NEW_TICKET');?></a>
        </p>
    </div>

    <div id="check_status">
        <h3><?php echo $ost->language->get('TEXT_CHECK_STATUS_TITLE');?></h3>
        <br>
        <div><?php echo $ost->language->get('TEXT_CHECK_STATUS_MSG');?></div>
        <p>
            <a href="view.php" class="blue button"><?php echo $ost->language->get('LABEL_CHECK_STATUS');?></a>
        </p>
    </div>
</div>
<div class="clear"></div>
<?php
if($cfg && $cfg->isKnowledgebaseEnabled()){
    //FIXME: provide ability to feature or select random FAQs ??
?>
<p><?php printf($ost->language->get('TEXT_BROWSE_FAQ'), '<a href="kb/index.php">', '</a>'); ?></p>
</div>
<?php
} ?>
<?php require(CLIENTINC_DIR.'footer.inc.php'); ?>
