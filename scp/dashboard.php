<?php
/*********************************************************************
    dashboard.php

    Staff's Dashboard - basic stats...etc.

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require('staff.inc.php');
$nav->setTabActive('dashboard');
require(STAFFINC_DIR.'header.inc.php');
?>

<script type="text/javascript" src="js/raphael-min.js"></script>
<script type="text/javascript" src="js/g.raphael.js"></script>
<script type="text/javascript" src="js/g.line-min.js"></script>
<script type="text/javascript" src="js/g.dot-min.js"></script>
<script type="text/javascript" src="js/bootstrap-tab.js"></script>
<script type="text/javascript" src="js/dashboard.inc.js"></script>

<link rel="stylesheet" type="text/css" href="css/bootstrap.css"/>
<link rel="stylesheet" type="text/css" href="css/dashboard.css"/>

<h2><?php echo lang('ticket_act_only'); ?></h2>
<p><?php echo lang('start_time_graph'); ?></p>
<form class="well form-inline" id="timeframe-form">
    <label>
        <?php echo lang('report_timeframe'); ?>:
        <input type="text" class="dp input-medium search-query"
            name="start" placeholder="<?php echo lang('last_month'); ?>"/>
    </label>
    <label>
        <?php echo lang('period'); ?>:
        <select name="period">
            <option value="now" selected="selected"><?php echo lang('up_to_today'); ?></option>
            <option value="+7 days"><?php echo lang('on_week'); ?></option>
            <option value="+14 days"><?php echo lang('two_weeks'); ?></option>
            <option value="+1 month"><?php echo lang('one_month'); ?></option>
            <option value="+3 months"><?php echo lang('one_quater'); ?></option>
        </select>
    </label>
    <button class="btn" type="submit"><?php echo lang('refresh'); ?></button>
</form>

<!-- Create a graph and fetch some data to create pretty dashboard -->
<div style="position:relative">
    <div id="line-chart-here" style="height:300px"></div>
    <div style="position:absolute;right:0;top:0" id="line-chart-legend"></div>
</div>

<hr/>
<h2><?php echo lang('statistics'); ?></h2>
<p><?php echo lang('statist_of_tickets'); ?></p>
<ul class="nav nav-tabs" id="tabular-navigation"></ul>

<div id="table-here"></div>

<?php
include(STAFFINC_DIR.'footer.inc.php');
?>
