<?php
/*********************************************************************
    slas.php

    SLA - Service Level Agreements

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2012 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require('admin.inc.php');
include_once(INCLUDE_DIR.'class.sla.php');

$sla=null;
if($_REQUEST['id'] && !($sla=SLA::lookup($_REQUEST['id'])))
    $errors['err']=_('Unknown or invalid API key ID.');

if($_POST){
    switch(strtolower($_POST['do'])){
        case 'update':
            if(!$sla){
                $errors['err']=_('Unknown or invalid SLA plan.');
            }elseif($sla->update($_POST,$errors)){
                $msg=_('SLA plan updated successfully');
            }elseif(!$errors['err']){
                $errors['err']=_('Error updating SLA plan. Try again!');
            }
            break;
        case 'add':
            if(($id=SLA::create($_POST,$errors))){
                $msg=_('SLA plan added successfully');
                $_REQUEST['a']=null;
            }elseif(!$errors['err']){
                $errors['err']=_('Unable to add SLA plan. Correct error(s) below and try again.');
            }
            break;
        case 'mass_process':
            if(!$_POST['ids'] || !is_array($_POST['ids']) || !count($_POST['ids'])) {
                $errors['err'] = _('You must select at least one plan.');
            } else {
                $count=count($_POST['ids']);
                switch(strtolower($_POST['a'])) {
                    case 'enable':
                        $sql='UPDATE '.SLA_TABLE.' SET isactive=1 '
                            .' WHERE id IN ('.implode(',', db_input($_POST['ids'])).')';
                    
                        if(db_query($sql) && ($num=db_affected_rows())) {
                            if($num==$count)
                                $msg = _('Selected SLA plans enabled');
                            else
                                $warn = "$num "._("of")." $count "._("selected SLA plans enabled");
                        } else {
                            $errors['err'] = _('Unable to enable selected SLA plans.');
                        }
                        break;
                    case 'disable':
                        $sql='UPDATE '.SLA_TABLE.' SET isactive=0 '
                            .' WHERE id IN ('.implode(',', db_input($_POST['ids'])).')';
                        if(db_query($sql) && ($num=db_affected_rows())) {
                            if($num==$count)
                                $msg = _('Selected SLA plans disabled');
                            else
                                $warn = "$num "._("of")." $count "._("selected SLA plans disabled");
                        } else {
                            $errors['err'] = _('Unable to disable selected SLA plans');
                        }
                        break;
                    case 'delete':
                        $i=0;
                        foreach($_POST['ids'] as $k=>$v) {
                            if(($p=SLA::lookup($v)) && $p->delete())
                                $i++;
                        }

                        if($i && $i==$count)
                            $msg = _('Selected SLA plans deleted successfully');
                        elseif($i>0)
                            $warn = "$i "._("of")." $count "._("selected SLA plans deleted");
                        elseif(!$errors['err'])
                            $errors['err'] = _('Unable to delete selected SLA plans');
                        break;
                    default:
                        $errors['err']=_('Unknown action - get technical help.');
                }
            }
            break;
        default:
            $errors['err']=_('Unknown action/command');
            break;
    }
}

$page='slaplans.inc.php';
if($sla || ($_REQUEST['a'] && !strcasecmp($_REQUEST['a'],'add')))
    $page='slaplan.inc.php';

$nav->setTabActive('manage');
require(STAFFINC_DIR.'header.inc.php');
require(STAFFINC_DIR.$page);
include(STAFFINC_DIR.'footer.inc.php');
?>
