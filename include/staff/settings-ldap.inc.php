<?php
if(!defined('OSTADMININC') || !$thisstaff->isAdmin()) die('Access Denied');

//call ldapActive to create the table if scp login was skipped.
$tmpval=LDAP::ldapActive();

$qstr='t='.$_REQUEST['t'];
$query='SELECT ldap.* FROM '.TABLE_PREFIX.'ldap_config ldap;';
$res=db_query($query);
if($res)
{
	$num=db_num_rows($res);
}

?>
<div style="width:700;padding-top:5px; float:left;">
 <h2>LDAP Connections</h2>
 </div>
<div style="float:right;text-align:right;padding-top:5px;padding-right:5px;">
    <a href="ldaptest.php" class="Icon alert-settings">LDAP Diagnostic</a>&nbsp;<b><a href="settings.php?t=ldap&a=add" class="Icon preferences">New LDAP Connection</a></b></div>
<div class="clear"></div>
<form action="settings.php?<?php echo $qstr; ?>" method="POST" name="settings">
 <?php csrf_token(); ?>
 <input type="hidden" name="do" value="mass_process" >
 <input type="hidden" id="action" name="a" value="" >
 <table class="list" border="0" cellspacing="1" cellpadding="0" width="940">
    <thead>
        <tr>
            <th width="7">&nbsp;</th>        
            <th width="400">Domain</th>
            <th width="120">Priority</th>
            <th width="150">Suffix</th>
            <th width="250">Controller</th>
            <th width="110">Status</th>
        </tr>
    </thead>
    <tbody>
    <?php
        $total=0;
        if($res && db_num_rows($res)):
            while ($row = db_fetch_array($res)) {
                $email=$row['email'];
                ?>
            <tr>
                <td width=7px>
                  <input type="checkbox" class="ckb" name="ids[]" value="<?php echo $row['ldap_id']; ?>" 
                            <?php echo $sel?'checked="checked"':''; ?>  <?php echo $default?'disabled="disabled"':''; ?>>
                </td>
                <td><a href="settings.php?t=ldap&id=<?php echo $row['ldap_id']; ?>"><?php echo $row['ldap_domain']; ?></a>&nbsp;</td>
                <td><?php echo $row['priority']; ?></td>
                <td><a href="settings.php?t=ldap&id=<?php echo $row['ldap_id']; ?>"><?php echo $row['ldap_suffix']; ?></a></td>
                <td><?php echo $row['ldap_controller']; ?></td>
                <td><?php echo $row['ldap_active']?'Active':'Inactive'; ?></td>
            </tr>
            <?php
            } //end of while.
        endif; ?>
    <tfoot>
     <tr>
        <td colspan="6">
            <?php if($res && $num){ ?>
            Select:&nbsp;
            <a id="selectAll" href="#ckb">All</a>&nbsp;&nbsp;
            <a id="selectNone" href="#ckb">None</a>&nbsp;&nbsp;
            <a id="selectToggle" href="#ckb">Toggle</a>&nbsp;&nbsp;
            <?php }else{
                echo 'No ldap entries found';
            } ?>
        </td>
     </tr>
    </tfoot>
</table>
<p class="centered" id="actions">
    <input class="button" type="submit" name="delete" value="Delete Entries" >
</p>
</form>

<div style="display:none;" class="dialog" id="confirm-action">
    <h3>Please Confirm</h3>
    <a class="close" href="">&times;</a>
    <hr/>
    <p class="confirm-action" style="display:none;" id="delete-confirm">
        <font color="red"><strong>Are you sure you want to DELETE selected LDAP Entries?</strong></font>
        <br><br>Deleted entries CANNOT be recovered.
    </p>
    <div>Please confirm to continue.</div>
    <hr style="margin-top:1em"/>
    <p class="full-width">
        <span class="buttons" style="float:left">
            <input type="button" value="No, Cancel" class="close">
        </span>
        <span class="buttons" style="float:right">
            <input type="button" value="Yes, Do it!" class="confirm">
        </span>
     </p>
    <div class="clear"></div>
</div>
