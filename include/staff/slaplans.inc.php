<?php
if(!defined('OSTADMININC') || !$thisstaff->isAdmin()) die(lang('access_denied'));

$qstr='';
$sql='SELECT * FROM '.SLA_TABLE.' sla WHERE 1';
$sortOptions=array('name'=>'sla.name','status'=>'sla.isactive','period'=>'sla.grace_period','date'=>'sla.created','updated'=>'sla.updated');
$orderWays=array('DESC'=>'DESC','ASC'=>'ASC');
$sort=($_REQUEST['sort'] && $sortOptions[strtolower($_REQUEST['sort'])])?strtolower($_REQUEST['sort']):'name';
//Sorting options...
if($sort && $sortOptions[$sort]) {
    $order_column =$sortOptions[$sort];
}
$order_column=$order_column?$order_column:'sla.name';

if($_REQUEST['order'] && $orderWays[strtoupper($_REQUEST['order'])]) {
    $order=$orderWays[strtoupper($_REQUEST['order'])];
}
$order=$order?$order:'ASC';

if($order_column && strpos($order_column,',')){
    $order_column=str_replace(','," $order,",$order_column);
}
$x=$sort.'_sort';
$$x=' class="'.strtolower($order).'" ';
$order_by="$order_column $order ";

$total=db_count('SELECT count(*) FROM '.SLA_TABLE.' sla ');
$page=($_GET['p'] && is_numeric($_GET['p']))?$_GET['p']:1;
$pageNav=new Pagenate($total, $page, PAGE_LIMIT);
$pageNav->setURL('slas.php',$qstr.'&sort='.urlencode($_REQUEST['sort']).'&order='.urlencode($_REQUEST['order']));
//Ok..lets roll...create the actual query
$qstr.='&order='.($order=='DESC'?'ASC':'DESC');
$query="$sql ORDER BY $order_by LIMIT ".$pageNav->getStart().",".$pageNav->getLimit();
$res=db_query($query);
if($res && ($num=db_num_rows($res)))
    $showing=$pageNav->showing().' '.lang('sla_plans');
else
    $showing=lang('plans_not_found');
?>

<div style="width:700;padding-top:5px; float:left;">
 <h2><?php echo lang('serv_level_agree'); ?></h2>
</div>
<div style="float:right;text-align:right;padding-top:5px;padding-right:5px;">
 <b><a href="slas.php?a=add" class="Icon newsla"><?php echo lang('sla_plan_added'); ?></a></b></div>
<div class="clear"></div>
<form action="slas.php" method="POST" name="slas">
 <?php csrf_token(); ?>
 <input type="hidden" name="do" value="mass_process" >
<input type="hidden" id="action" name="a" value="" >
 <table class="list" border="0" cellspacing="1" cellpadding="0" width="940">
    <caption><?php echo $showing; ?></caption>
    <thead>
        <tr>
            <th width="7">&nbsp;</th>        
            <th width="320"><a <?php echo $name_sort; ?> href="slas.php?<?php echo $qstr; ?>&sort=name"><?php echo lang('name'); ?></a></th>
            <th width="100"><a  <?php echo $status_sort; ?> href="slas.php?<?php echo $qstr; ?>&sort=status"><?php echo lang('status'); ?></a></th>
            <th width="130"><a  <?php echo $period_sort; ?> href="slas.php?<?php echo $qstr; ?>&sort=period"><?php echo lang('grace_period_hrs'); ?></a></th>
            <th width="120" nowrap><a  <?php echo $created_sort; ?>href="slas.php?<?php echo $qstr; ?>&sort=created"><?php echo lang('date_added'); ?></a></th>
            <th width="150" nowrap><a  <?php echo $updated_sort; ?>href="slas.php?<?php echo $qstr; ?>&sort=updated"><?php echo lang('last_update'); ?></a></th>
        </tr>
    </thead>
    <tbody>
    <?php
        $total=0;
        $ids=($errors && is_array($_POST['ids']))?$_POST['ids']:null;
        if($res && db_num_rows($res)):
            while ($row = db_fetch_array($res)) {
                $sel=false;
                if($ids && in_array($row['id'],$ids))
                    $sel=true;
                ?>
            <tr id="<?php echo $row['id']; ?>">
                <td width=7px>
                  <input type="checkbox" class="ckb" name="ids[]" value="<?php echo $row['id']; ?>" 
                            <?php echo $sel?'checked="checked"':''; ?>>
                </td>
                <td>&nbsp;<a href="slas.php?id=<?php echo $row['id']; ?>"><?php echo Format::htmlchars($row['name']); ?></a></td>
                <td><?php echo $row['isactive']?lang('active'):'<b>'.lang('disabled').'</b>'; ?></td>
                <td style="text-align:right;padding-right:35px;"><?php echo $row['grace_period']; ?>&nbsp;</td>
                <td>&nbsp;<?php echo Format::db_date($row['created']); ?></td>
                <td>&nbsp;<?php echo Format::db_datetime($row['updated']); ?></td>
            </tr>
            <?php
            } //end of while.
        endif; ?>
    <tfoot>
     <tr>
        <td colspan="6">
            <?php if($res && $num){ ?>
            <?php echo lang('select'); ?>:&nbsp;
            <a id="selectAll" href="#ckb"><?php echo lang('all'); ?></a>&nbsp;&nbsp;
            <a id="selectNone" href="#ckb"><?php echo lang('none'); ?></a>&nbsp;&nbsp;
            <a id="selectToggle" href="#ckb"><?php echo lang('toggle'); ?></a>&nbsp;&nbsp;
            <?php }else{
                echo lang('plans_not_found');
            } ?>
        </td>
     </tr>
    </tfoot>
</table>
<?php
if($res && $num): //Show options..
    echo '<div>&nbsp;'.lang('page').':'.$pageNav->getPageLinks().'&nbsp;</div>';
?>
<p class="centered" id="actions">
    <input class="button" type="submit" name="enable" value="<?php echo lang('enable'); ?>" >
    <input class="button" type="submit" name="disable" value="<?php echo ucfirst(lang('disable')); ?>">
    <input class="button" type="submit" name="delete" value="<?php echo lang('delete'); ?>">
</p>
<?php
endif;
?>
</form>

<div style="display:none;" class="dialog" id="confirm-action">
    <h3><?php echo lang('please_confirm'); ?></h3>
    <a class="close" href="">X</a>
    <hr/>
    <p class="confirm-action" style="display:none;" id="enable-confirm">
        <?php echo lang('sure_you_want_to'); ?> <b><?php echo lang('enable'); ?></b> <?php echo lang('selected_sla_plans'); ?>?
    </p>
    <p class="confirm-action" style="display:none;" id="disable-confirm">
        <?php echo lang('sure_you_want_to'); ?> <b><?php echo lang('disable'); ?></b> <?php echo lang('selected_sla_plans'); ?>?
    </p>
    <p class="confirm-action" style="display:none;" id="delete-confirm">
        <font color="red"><strong><?php echo lang('sure_delete_plan'); ?>?</strong></font>
    </p>
    <div><?php echo lang('confirm_to_continue'); ?></div>
    <hr style="margin-top:1em"/>
    <p class="full-width">
        <span class="buttons" style="float:left">
            <input type="button" value="<?php echo lang('no_cancel'); ?>" class="close">
        </span>
        <span class="buttons" style="float:right">
            <input type="button" value="<?php echo lang('yes_doit'); ?>!" class="confirm">
        </span>
     </p>
    <div class="clear"></div>
</div>
