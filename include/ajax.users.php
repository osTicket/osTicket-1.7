<?php
/*********************************************************************
    ajax.users.php

    AJAX interface for  users (based on submitted tickets)
    XXX: osTicket doesn't support user accounts at the moment.

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/

if(!defined('INCLUDE_DIR')) die('403');

include_once(INCLUDE_DIR.'class.ticket.php');

class UsersAjaxAPI extends AjaxController {
   
    /* Assumes search by emal for now */
    function search() {

        if(!isset($_REQUEST['q'])) {
            Http::response(400, 'Query argument is required');
        }

        $limit = isset($_REQUEST['limit']) ? (int) $_REQUEST['limit']:25;
        $users=array();

        $sql='SELECT DISTINCT email, name, phone, phone_ext '
            .' FROM '.TICKET_TABLE
            .' WHERE email LIKE \'%'.db_input(strtolower($_REQUEST['q']), false).'%\' '
            .' ORDER BY created '
            .' LIMIT '.$limit;

        global $cfg;

        if(($res=db_query($sql)) && db_num_rows($res)){

            while(list($email,$name,$phone,$phone_ext)=db_fetch_row($res)) {
                $record = array('email'=>$email,
                    'name'=>$name,
                    'info'=>"$email - $name"
                );  

                if($cfg->getSearchPhone()) {
                    $record = array_merge($record, array(
                        'phone' =>$phone,
                        'phone_ext' =>$phone_ext
                    ));
                    $record['info'] .= " - $phone $phone_ext";
                }

                $users[] = $record;
            }                    
        }  
        
        return $this->json_encode($users);

    }
}
?>
