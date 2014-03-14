<?php
if(!defined('OSTSTAFFINC') || !$faq || !$thisstaff) die(lang('access_denied'));

$category=$faq->getCategory();

?>
<h2><?php echo lang('freq_asked_quest'); ?></h2>
<div id="breadcrumbs">
    <a href="kb.php"><?php echo lang('all_categories'); ?></a> 
    &raquo; <a href="kb.php?cid=<?php echo $category->getId(); ?>"><?php echo $category->getName(); ?></a>
    <span class="faded">(<?php echo $category->isPublic()?lang('public'):lang('internal'); ?>)</span>
</div>
<div style="width:700;padding-top:2px; float:left;">
<strong style="font-size:16px;"><?php echo $faq->getQuestion() ?></strong>&nbsp;&nbsp;<span class="faded"><?php echo $faq->isPublished()?'('.lang('published').')':''; ?></span>
</div>
<div style="float:right;text-align:right;padding-top:5px;padding-right:5px;">
<?php
if($thisstaff->canManageFAQ()) {
    echo sprintf('<a href="faq.php?id=%d&a=edit" class="Icon newHelpTopic">'.lang('edit_faq').'</a>',
            $faq->getId());
}
?>
&nbsp;
</div>
<div class="clear"></div>
<p>
<?php echo Format::safe_html($faq->getAnswer()); ?>
</p>
<p>
 <div><span class="faded"><b><?php echo lang('attachments'); ?>:</b></span> <?php echo $faq->getAttachmentsLinks(); ?></div>
 <div><span class="faded"><b><?php echo lang('help_topics'); ?>:</b></span> 
    <?php echo ($topics=$faq->getHelpTopics())?implode(', ',$topics):' '; ?>
    </div>
</p>
<div class="faded">&nbsp;<?php echo lang('last_update'); ?> <?php echo Format::db_daydatetime($category->getUpdateDate()); ?></div>
<hr>
<?php
if($thisstaff->canManageFAQ()) {
    //TODO: add js confirmation....
    ?>
   <div>
    <form action="faq.php?id=<?php echo  $faq->getId(); ?>" method="post">
	 <?php csrf_token(); ?>
        <input type="hidden" name="id" value="<?php echo  $faq->getId(); ?>">
        <input type="hidden" name="do" value="<?php echo lang('manage_faq'); ?>">
        <div>
            <strong><?php echo lang('options'); ?>: </strong>
            <select name="a" style="width:200px;">
                <option value=""><?php echo lang('select_action'); ?></option>
                <?php
                if($faq->isPublished()) { ?>
                <option value="unpublish"><?php echo lang('unpublish_faq'); ?></option>
                <?php
                }else{ ?>
                <option value="publish"><?php echo lang('publish_faq'); ?></option>
                <?php
                } ?>
                <option value="edit"><?php echo lang('edit_faq'); ?></option>
                <option value="delete"><?php echo lang('delete_faq'); ?></option>
            </select>
            &nbsp;&nbsp;<input type="submit" name="submit" value="<?php echo lang('go'); ?>">
        </div>
    </form>
   </div>
<?php
} 
?>
