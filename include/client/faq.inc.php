<?php
if(!defined('OSTCLIENTINC') || !$faq  || !$faq->isPublished()) die('Access Denied');

$category=$faq->getCategory();

?>
<div id="kb_faq">
	<h1><a href='./'>FAQs</a> / <a href="faq.php?cid=<? echo $category->getId(); ?>"><?php echo $category->getName() ?></a></h1>

	<H2><?php echo $faq->getQuestion() ?></H2>

	<div class='faq_content'>
		<?php echo Format::safe_html($faq->getAnswer()); ?>
	</div>

	<div class='faq_attachments'>
<?php
if($faq->getNumAttachments()) { ?>
 		<span class="faded"><b>Attachments:</b></span>  <?php echo $faq->getAttachmentsLinks(); ?>
<?
}?>
	</div>

	<div class="faq_metas"><span class="faded"><b>Help Topics:</b></span>
    	<?php echo ($topics=$faq->getHelpTopics())?implode(', ',$topics):' '; ?>
	</div>

	<div class='faq_date'>Last updated <?php echo Format::db_daydatetime($category->getUpdateDate()); ?></div>

</div>