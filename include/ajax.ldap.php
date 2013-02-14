<?php
/*********************************************************************
    ajax.ldap.php

    AJAX interface for ldap users

    Fabio Rauber <fabior@interlegis.leg.br>
    Copyright (c)  2006-2012 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/

if(!defined('INCLUDE_DIR')) die('403');

include_once(INCLUDE_DIR.'class.ticket.php');

class UsersLDAPAjaxAPI extends AjaxController {
   
    function search() {

        if(!isset($_REQUEST['q'])) {
            Http::response(400, 'Query argument is required');
        }
	if (LOGIN_TYPE != 'LDAP'){
	    Http::response(400, 'Login not configured for LDAP.');
	}

        $limit = isset($_REQUEST['limit']) ? (int) $_REQUEST['limit']:25;
        $users=array();

	$ds=ldap_connect('ldap://'.LDAP_DOMAIN_FQDN) or die(_("Couldn't connect to LDAP!"));
	$connect_u = LDAP_DOMAIN_NETBIOS."\\".LDAP_USER;
	$conect_p = LDAP_PASSWORD;
	$search_user_dn = LDAP_SEARCH_DN;

	$inforequired = array("mail","sAMAccountName","givenName","sn");


	if (!ldap_bind( $ds, $connect_u, $conect_p) ) {
        	 Http::response(400, "Could not bind AD connection");
	}else{
		if ($_REQUEST['type'] == "email") {
                	$curMail=$_REQUEST['q'];
               		$curMail = strtolower($curMail);
                	$curMail.='*';
                	$filter="(&(mail=$curMail)(objectCategory=person))";
       		}elseif ($_REQUEST['type'] == "username"){
			$curName=$_REQUEST['q'];
                	$curName = strtolower($curName);
                	$curName.='*';
                	$filter="(&(sAMAccountName=$curName)(objectCategory=person))";
		} else Http::response(400, "Unknown request type.");
		$user_result = ldap_search($ds,$search_user_dn,$filter,$inforequired);
        	$user_info = ldap_get_entries($ds,$user_result);
                
                if (count($user_info) > $limit)
                {
                        $max=$limit;
                }
                else
                {
                        $max=count($user_info);
                }
                for ( $i=1; $i<$max; $i+=1)
                {
			$email = $user_info[$i-1]['mail'][0];
			$username = $user_info[$i-1]['samaccountname'][0];
			$firstname = iconv("ISO-8859-1", "UTF-8",$user_info[$i-1]['givenname'][0]);
			$lastname = iconv("ISO-8859-1", "UTF-8",$user_info[$i-1]['sn'][0]);
			if ($_REQUEST['type'] == "email") {
				$users[] = array('email'=>$email, 
					 	'username'=>$username,
					 	'firstname'=>$firstname,
					 	'lastname'=>$lastname,	
					 	'info'=>"$email - $firstname $lastname");
			} elseif ($_REQUEST['type'] == "username") {
				$users[] = array('email'=>$email,
                                                'username'=>$username,
                                                'firstname'=>$firstname,
                                                'lastname'=>$lastname,
                                                'info'=>"$username - $firstname $lastname");
			} 	
                }
	}

        return $this->json_encode($users);
	}

}
?>
