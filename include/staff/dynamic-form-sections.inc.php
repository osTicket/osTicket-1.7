<div style="width:700;padding-top:5px; float:left;">
 <h2>Dynamic Form Sections</h2>
</div>
<div style="float:right;text-align:right;padding-top:5px;padding-right:5px;">
 <b><a href="dynamic-form-sections.php?a=add" class="Icon">Add New Dynamic Form Section</a></b></div>
<div class="clear"></div>

<?php
$page = 1;
$pagesize = 25;
$start = ($page-1)*$pagesize;
$count = DynamicFormSection::count();
$end = min($start+$pagesize, $count);
$showing = sprintf("Showing %s - %s of %s", $start+1, $end, $count);
?>

<table class="list" border="0" cellspacing="1" cellpadding="0" width="940">
    <caption><?php echo $showing; ?></caption>
    <thead>
        <tr>
            <th width="7">&nbsp;</th>        
            <th>Title</th>
            <th>Last Updated</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach (DynamicFormSection::all() as $form) { ?>
        <tr>
            <td/>
            <td><a href="?id=<?php echo $form->get('id'); ?>"><?php echo $form->get('title'); ?></a></td>
            <td><?php echo $form->get('updated'); ?></td>
        </tr>
    <?php }
    ?>
    </tbody>
</table>
