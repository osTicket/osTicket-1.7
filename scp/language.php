<?php
/*********************************************************************
    emails.php

    Emails

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require('admin.inc.php');
include_once(INCLUDE_DIR.'languages/language_control/languages_processor.php');

if($_POST){
    switch(strtolower($_POST['do'])){
        case 'create':
            if(isset($_REQUEST['language']))
            {
                if(createNewLanguage($_REQUEST['language']))
                {
                    $msg=lang('lang_created');
                    @header('Location: language.php');
                }
                else
                    $errors['err']=lang('error_create_lang');       
            }
        break;
        case 'update':
            if(isset($_REQUEST['language']))
            {
                $languageInfo=array();
                $json=$_REQUEST["data_language"];

                $obj = json_decode($json);
                foreach ($obj as $key => $value) {
                   $languageInfo[$key]=$value;
                }

                if(updateLanguage($_REQUEST['language'],$languageInfo))
                {
                    $msg=lang('lang_updated');
                }
                else
                    $errors['err']=lang('error_update_lang');
            }
        break;
    }
}

$page='languages.inc.php';
if($email || ($_REQUEST['a'] && !strcasecmp($_REQUEST['a'],'add')))
    $page='language.inc.php';
else 
    if($email || ($_REQUEST['a'] && !strcasecmp($_REQUEST['a'],'edit')))
        $page='languages_edit.inc.php';

$nav->setTabActive('language');
require(STAFFINC_DIR.'header.inc.php');
require(STAFFINC_DIR.$page);
include(STAFFINC_DIR.'footer.inc.php');
?>
