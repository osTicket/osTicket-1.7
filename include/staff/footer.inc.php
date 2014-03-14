    </div>
    <div id="footer">
        <?php echo lang('copyright'); ?>  &copy; 2006-<?php echo date('Y'); ?>&nbsp;osTicket.com. &nbsp;<?php echo lang('right_reserved'); ?>
    </div>
<?php
if(is_object($thisstaff) && $thisstaff->isStaff()) { ?>
    <div>
        <!-- Do not remove <img src="autocron.php" alt="" width="1" height="1" border="0" /> or your auto cron will cease to function -->
        <img src="autocron.php" alt="" width="1" height="1" border="0" />
        <!-- Do not remove <img src="autocron.php" alt="" width="1" height="1" border="0" /> or your auto cron will cease to function -->
    </div>
<?php
} ?>
</div>
<div id="overlay"></div>
<div id="loading">
    <h4><?php echo lang('please_wait'); ?></h4>
    <p><?php echo lang('take_a_second'); ?></p>
</div>
</body>
</html>
