<?php
if(!defined('OSTCLIENTINC')) die('Access Denied');

?>
<h1>Frequently Asked Questions</h1>
<form action="index.php" method="get" id="kb-search">
    <input type="hidden" name="a" value="search">
    <div>
        <input id="query" type="text" size="20" name="q" value="<?php echo Format::htmlchars($_REQUEST['q']); ?>">
        <select name="cid" id="cid">
            <option value="">&mdash; All Categories &mdash;</option>
            <?php
            $sql='SELECT category_id, name, count(faq.category_id) as faqs '
                .' FROM '.FAQ_CATEGORY_TABLE.' cat '
                .' LEFT JOIN '.FAQ_TABLE.' faq USING(category_id) '
                .' WHERE cat.ispublic=1 AND faq.ispublished=1 '
                .' GROUP BY cat.category_id '
                .' HAVING faqs>0 '
                .' ORDER BY cat.name DESC ';
            if(($res=db_query($sql)) && db_num_rows($res)) {
                while($row=db_fetch_array($res))
                    echo sprintf('<option value="%d" %s>%s (%d)</option>',
                            $row['category_id'],
                            ($_REQUEST['cid'] && $row['category_id']==$_REQUEST['cid']?'selected="selected"':''),
                            $row['name'],
                            $row['faqs']);
            }
            ?>
        </select>
        <input id="searchSubmit" type="submit" value="Search">
    </div>
    <div>
        <select name="topicId" id="topic-id">
            <option value="">&mdash; All Help Topics &mdash;</option>
            <?php
            $sql='SELECT ht.topic_id, CONCAT_WS(" / ", pht.topic, ht.topic) as helptopic, count(faq.topic_id) as faqs '
                .' FROM '.TOPIC_TABLE.' ht '
                .' LEFT JOIN '.TOPIC_TABLE.' pht ON (pht.topic_id=ht.topic_pid) '
                .' LEFT JOIN '.FAQ_TOPIC_TABLE.' faq ON(faq.topic_id=ht.topic_id) '
                .' WHERE ht.ispublic=1 '
                .' GROUP BY ht.topic_id '
                .' HAVING faqs>0 '
                .' ORDER BY helptopic ';
            if(($res=db_query($sql)) && db_num_rows($res)) {
                while($row=db_fetch_array($res))
                    echo sprintf('<option value="%d" %s>%s (%d)</option>',
                            $row['topic_id'],
                            ($_REQUEST['topicId'] && $row['topic_id']==$_REQUEST['topicId']?'selected="selected"':''),
                            $row['helptopic'], $row['faqs']);
            }
            ?>
        </select>
    </div>
</form>
<hr>
<div>
<?php
if($_REQUEST['q'] || $_REQUEST['cid'] || $_REQUEST['topicId']) { //Search.
    $sql='SELECT faq.faq_id, question '
        .' FROM '.FAQ_TABLE.' faq '
        .' LEFT JOIN '.FAQ_CATEGORY_TABLE.' cat ON(cat.category_id=faq.category_id) '
        .' LEFT JOIN '.FAQ_TOPIC_TABLE.' ft ON(ft.faq_id=faq.faq_id) '
        .' WHERE faq.ispublished=1 AND cat.ispublic=1';
    
    if($_REQUEST['cid'])
        $sql.=' AND faq.category_id='.db_input($_REQUEST['cid']);
    
    if($_REQUEST['topicId'])
        $sql.=' AND ft.topic_id='.db_input($_REQUEST['topicId']);


    if($_REQUEST['q']) {
        $sql.=" AND question LIKE ('%".db_input($_REQUEST['q'],false)."%') 
                 OR answer LIKE ('%".db_input($_REQUEST['q'],false)."%') 
                 OR keywords LIKE ('%".db_input($_REQUEST['q'],false)."%')";
    }

    $sql.=' GROUP BY faq.faq_id';
    echo "<div><strong>Search Results</strong></div><div class='clear'></div>";
    if(($res=db_query($sql)) && ($num=db_num_rows($res))) {
        echo '<div id="faq">'.$num.' FAQs matched your search criteria.
                <ol>';
        while($row=db_fetch_array($res)) {
            echo sprintf('
                <li><a href="faq.php?id=%d" class="previewfaq">%s</a></li>',
                $row['faq_id'],$row['question'],$row['ispublished']?'Published':'Internal');
        }
        echo '  </ol>
             </div>';
    } else {
        echo '<strong class="faded">The search did not match any FAQs.</strong>';
    }
} else { //Category Listing.
    $sql='SELECT cat.category_id, cat.name, cat.description, cat.ispublic, count(faq.faq_id) as faqs '
        .' FROM '.FAQ_CATEGORY_TABLE.' cat '
        .' LEFT JOIN '.FAQ_TABLE.' faq ON(faq.category_id=cat.category_id AND faq.ispublished=1) '
        .' WHERE cat.ispublic=1 '
        .' GROUP BY cat.category_id '
        .' HAVING faqs>0 '
        .' ORDER BY cat.name';
    if(($res=db_query($sql)) && db_num_rows($res)) {
        echo '<div>Click on the category to browse FAQs.</div>
                <ul id="kb">';
        while($row=db_fetch_array($res)) {

            echo sprintf('
                <li>
                    <i></i>
                    <h4><a href="faq.php?cid=%d">%s (%d)</a></h4>
                    %s
                </li>',$row['category_id'],
                Format::htmlchars($row['name']),$row['faqs'],
                Format::safe_html($row['description']));
        }
        echo '</ul>';
    } else {
        echo 'NO FAQs found';
    }
}
?>
</div>
