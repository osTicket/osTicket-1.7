<?php
/*********************************************************************
    helptopics.php

    Help Topics.

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2012 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require('admin.inc.php');
include_once(INCLUDE_DIR.'class.topic.php');

$topic=null;
if($_REQUEST['id'] && !($topic=Topic::lookup($_REQUEST['id'])))
    $errors['err']=_('Unknown or invalid help topic ID.');

if($_POST){
    switch(strtolower($_POST['do'])){
        case 'update':
            if(!$topic){
                $errors['err']=_('Unknown or invalid help topic.');
            }elseif($topic->update($_POST,$errors)){
                $msg=_('Help topic updated successfully');
            }elseif(!$errors['err']){
                $errors['err']=_('Error updating help topic. Try again!');
            }
            break;
        case 'create':
            if(($id=Topic::create($_POST,$errors))){
                $msg=_('Help topic added successfully');
                $_REQUEST['a']=null;
            }elseif(!$errors['err']){
                $errors['err']=_('Unable to add help topic. Correct error(s) below and try again.');
            }
            break;
        case 'mass_process':
            if(!$_POST['ids'] || !is_array($_POST['ids']) || !count($_POST['ids'])) {
                $errors['err']=_('You must select at least one help topic');
            }else{
                $count=count($_POST['ids']);
                if($_POST['enable']){
                    $sql='UPDATE '.TOPIC_TABLE.' SET isactive=1 WHERE topic_id IN ('.
                        implode(',', db_input($_POST['ids'])).')';
                    if(db_query($sql) && ($num=db_affected_rows())){
                        if($num==$count)
                            $msg=_('Selected help topics enabled');
                        else
                            $warn="$num "._("of")." $count "._("selected help topics enabled");
                    }else{
                        $errors['err']=_('Unable to enable selected help topics.');
                    }
                }elseif($_POST['disable']){
                    $sql='UPDATE '.TOPIC_TABLE.' SET isactive=0  WHERE topic_id IN ('.
                        implode(',', db_input($_POST['ids'])).')';
                    if(db_query($sql) && ($num=db_affected_rows())) {
                        if($num==$count)
                            $msg=_('Selected help topics disabled');
                        else
                            $warn="$num "._("of")." $count "._("selected help topics disabled");
                    }else{
                        $errors['err']=_('Unable to disable selected help topic(s)');
                    }

                }elseif($_POST['delete']){
                    $i=0;
                    foreach($_POST['ids'] as $k=>$v) {
                        if(($t=Topic::lookup($v)) && $t->delete())
                            $i++;
                    }

                    if($i && $i==$count)
                        $msg=_('Selected help topics deleted successfully');
                    elseif($i>0)
                        $warn="$i "._("of")." $count "._("selected help topics deleted");
                    elseif(!$errors['err'])
                        $errors['err']=_('Unable to delete selected help topics');
                    
                }else {
                    $errors['err']=_('Unknown action');
                }
            }
            break;
        default:
            $errors['err']=_('Unknown action');
            break;
    }
}

$page='helptopics.inc.php';
if($topic || ($_REQUEST['a'] && !strcasecmp($_REQUEST['a'],'add')))
    $page='helptopic.inc.php';

$nav->setTabActive('manage');
require(STAFFINC_DIR.'header.inc.php');
require(STAFFINC_DIR.$page);
include(STAFFINC_DIR.'footer.inc.php');
?>
