<div style="width:700;padding-top:5px; float:left;">
 <h2>Dynamic Lists</h2>
</div>
<div style="float:right;text-align:right;padding-top:5px;padding-right:5px;">
 <b><a href="dynamic-lists.php?a=add" class="Icon">Add New Dynamic List</a></b></div>
<div class="clear"></div>

<?php
$page = 1;
$pagesize = 25;
$start = ($page-1)*$pagesize;
$count = DynamicList::count();
$end = min($start+$pagesize, $count);
$showing = sprintf("Showing %s - %s of %s", $start+1, $end, $count);
?>

<table class="list" border="0" cellspacing="1" cellpadding="0" width="940">
    <caption><?php echo $showing; ?></caption>
    <thead>
        <tr>
            <th width="7">&nbsp;</th>
            <th>List Name</th>
            <th>Created</th>
            <th>Last Updated</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach (DynamicList::all() as $list) { ?>
        <tr>
            <td/>
            <td><a href="?id=<?php echo $list->get('id'); ?>"><?php echo $list->get('name'); ?></a></td>
            <td><?php echo $list->get('created'); ?></td>
            <td><?php echo $list->get('updated'); ?></td>
        </tr>
    <?php }
    ?>
    </tbody>
</table>
