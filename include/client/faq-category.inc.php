<?php
if(!defined('OSTCLIENTINC') || !$category || !$category->isPublic()) die(lang('access_denied'));
?>
<h1><strong><?php echo $category->getName() ?></strong></h1>
<p>
<?php echo Format::safe_html($category->getDescription()); ?>
</p>
<hr>
<?php
$sql='SELECT faq.faq_id, question, count(attach.file_id) as attachments '
    .' FROM '.FAQ_TABLE.' faq '
    .' LEFT JOIN '.FAQ_ATTACHMENT_TABLE.' attach ON(attach.faq_id=faq.faq_id) '
    .' WHERE faq.ispublished=1 AND faq.category_id='.db_input($category->getId())
    .' GROUP BY faq.faq_id';
if(($res=db_query($sql)) && db_num_rows($res)) {
    echo '
         <h2>'.lang('freq_asked_quest').'</h2>
         <div id="faq">
            <ol>';
    while($row=db_fetch_array($res)) {
        $attachments=$row['attachments']?'<span class="Icon file"></span>':'';
        echo sprintf('
            <li><a href="faq.php?id=%d" >%s &nbsp;%s</a></li>',
            $row['faq_id'],Format::htmlchars($row['question']), $attachments);
    }
    echo '  </ol>
         </div>
         <p><a class="back" href="index.php">&laquo; '.lang('go_back').'</a></p>';
}else {
    echo '<strong>'.lang('catg_no_have_faqs').' <a href="index.php">'.lang('back_to_index').'</a></strong>';
}
?>
