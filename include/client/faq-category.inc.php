<?php
if(!defined('OSTCLIENTINC') || !$category || !$category->isPublic()) die('Access Denied');
?>
<h1><a href='./'>FAQs</a> / <span class='h1_cat'><?php echo $category->getName() ?></span></h1>

<p class="intro">
<?php echo Format::safe_html($category->getDescription()); ?>
</p>
<div id="kb_faqs">

<?php
$sql='SELECT faq.faq_id, question, count(attach.file_id) as attachments '
    .' FROM '.FAQ_TABLE.' faq '
    .' LEFT JOIN '.FAQ_ATTACHMENT_TABLE.' attach ON(attach.faq_id=faq.faq_id) '
    .' WHERE faq.ispublished=1 AND faq.category_id='.db_input($category->getId())
    .' GROUP BY faq.faq_id';
if(($res=db_query($sql)) && db_num_rows($res)) {
    echo '<ul class="faqs_list">';
    while($row=db_fetch_array($res)) {
        $attachments=$row['attachments']?'<span class="Icon file"></span>':'';
        echo sprintf('
            <li><a href="faq.php?id=%d" >%s &nbsp;%s</a></li>',
            $row['faq_id'],Format::htmlchars($row['question']), $attachments);
    }
    echo '  </ul>';
}else {
    echo '<div class="kb_none">Category does not have any FAQs.</div>';
}
?>
</div>
 <p class="nav_back"><a class="back" href="./">&laquo; Go Back</a></p>