<div style="width:700;padding-top:5px; float:left;">
 <h2><?php echo lang('languages'); ?></h2>
 </div>
<div style="float:right;text-align:right;padding-top:5px;padding-right:5px;">
    <b><a href="language.php?a=add" class="Icon users"><?php echo lang('add_new_lang'); ?></a></b></div>
<div class="clear"></div>
<form action="language.php" method="POST" name="emails">
<?php csrf_token(); ?>
 <input type="hidden" name="do" value="mass_process" >
 <input type="hidden" id="action" name="a" value="" >
 <table class="list" border="0" cellspacing="1" cellpadding="0" width="940">
    <caption></caption>
    <thead>
        <tr>       
            <th width="120"><a><?php echo lang('key'); ?></a></th>
            <th width="400"><a><?php echo lang('desc_key'); ?></a></th>
        </tr>
    </thead>
    <tbody>
            <?php foreach (getAssignedLanguages() as $key => $value): ?>
                <tr id="<?php echo $row['email_id']; ?>">
                    <td width=7px><a href="language.php?a=edit&key=<?php echo $key; ?>" > <?php echo $key; ?> </a></td>
                    <td width=7px><a href="language.php?a=edit&key=<?php echo $key; ?>" > <?php echo $value; ?> </a></td>
                </tr>
            <?php endforeach; ?>
    <tfoot>
     <tr>

     </tr>
    </tfoot>
</table>

</form>


