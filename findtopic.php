<?php

/* File   : findtopic.php */
/* Purpose: This file will help you to display the help topic */
/*          automatically based on the selected department */
/*          in the open or new ticket form at the client side */
/* Author : Masino Sinaga, http://www.openscriptsolution.com */
/* Created: December 2, 2009 */

require_once('main.inc.php');
if(!defined('INCLUDE_DIR')) die('Fatal Error');
define('CLIENTINC_DIR',INCLUDE_DIR.'client/');
define('OSTCLIENTINC',TRUE); //make includes happy

        $topicsbydept=array();
	if($dept_id=$_GET['dept_id']) {
	        $query="SELECT topic_id,topic FROM ".TOPIC_TABLE."
			WHERE isactive=1 AND ispublic=1 AND dept_id = ".$dept_id."
			ORDER BY topic";
		$result=db_query($query);
		while (list($id, $name) = db_fetch_row($result)){$topicsbydept[$id]=$name;}
	}

?>

<select id="topicId" name="topicId" style="min-width:200px;">
                <option value="" selected="selected">&mdash; Select a Help Topic &mdash;</option>
                <?php
        if($topics=$topicsbydept) {
                    foreach($topics as $id =>$name) {
                        echo sprintf('<option value="%d" %s>%s</option>',
                                $id, ($info['topicId']==$id)?'selected="selected"':'', $name);
                    }
                } 
        else { ?>
<!--                    <option value="0" >General Inquiry</option>-->
                <?php
                } ?>
            </select>
