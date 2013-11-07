<?php
/*********************************************************************
    index.php

    Helpdesk landing page. Please customize it to fit your needs.

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require('client.inc.php');
$section = 'home';
require(CLIENTINC_DIR.'header.inc.php');
require_once(INCLUDE_DIR.'languages/language_control/languages_processor.php');


?>

<?php session_start(); if (isset($_SESSION['error'])): ?>

    <p id="sysmsg" class="error">
        <?php echo lang('this_link_is_not_valid'); ?>
    </p>
    <script type="text/javascript">setTimeout("$('#sysmsg').fadeOut('slow');",1500);</script>
<?php unset($_SESSION['error']); endif; ?>

<div id="landing_page">
    <h1><?php echo lang('welcome_support'); ?></h1>
    <p>
        <?php echo lang('welcome_text'); ?>
    </p>

    <div id="new_ticket">
        <h3><?php echo lang('open_new_ticket'); ?></h3>
        <br>
        <div style="min-height:65px"><?php echo lang('provide_details'); ?></div>
        <p>
            <a href="open.php" class="green button"><?php echo lang('open_new_ticket'); ?></a>
        </p>
    </div>

    <div id="check_status">
        <h3><?php echo lang('check_ticket_stat'); ?></h3>
        <br>
        <div style="min-height:65px"><?php echo lang('provide_archives'); ?></div>
        <p>
            <a href="view.php" class="blue button"><?php echo lang('check_ticket_stat'); ?></a>
        </p>
    </div>
</div>
<div class="clear"></div>
<?php
if($cfg && $cfg->isKnowledgebaseEnabled()){
    //FIXME: provide ability to feature or select random FAQs ??
?>
<p><?php echo lang('sure_to_browse'); ?> <a href="kb/index.php"><?php echo lang('freq_asked_quest'); ?></a>, <?php echo lang('before_open_ticket'); ?></p>
</div>
<?php
} ?>
<?php require(CLIENTINC_DIR.'footer.inc.php'); ?>
