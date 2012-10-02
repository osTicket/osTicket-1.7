<?php

include "../include/ost-config.php";

//Replace this with your AD Domain Controller
$ds=ldap_connect('ldap://'.LDAP_DOMAIN_FQDN) or die(_("Couldn't connect to LDAP!"));
//Replace this with a username that has read permissions on your AD
$connect_u = LDAP_DOMAIN_NETBIOS."\\".LDAP_USER;
//Replace this with the password of the user
$conect_p = LDAP_PASSWORD;
//Replace this with the DN of the base OU you want to search
$search_user_dn = LDAP_SEARCH_DN;

$inforequired = array("mail","sAMAccountName");


if (!ldap_bind( $ds, $connect_u, $conect_p) ) {
        $error_msg[] = "Could not bind AD connection<br>";
}
else
{
        if (!empty($_REQUEST['mail'])) {
                $curMail=$_REQUEST['mail'];
                $curMail = strtolower($curMail);
                $curMail.='*';
                $filter="(&(mail=$curMail)(objectCategory=person))";
        }
        elseif (!empty($_REQUEST['username'])) {
                $curName=$_REQUEST['username'];
                $curName = strtolower($curName);
                $curName.='*';
                $filter="(&(sAMAccountName=$curName)(objectCategory=person))";
        }
        $user_result = ldap_search($ds,$search_user_dn,$filter,$inforequired);
        $user_info = ldap_get_entries($ds,$user_result);
        header("Content-Type: application/json");
                echo"{\"results\": [";
                $arr=Array();
                if (count($user_info) > $_REQUEST['maxEntries'])
                {
                        $max=$_REQUEST['maxEntries'];
                }
                else
                {
                        $max=count($user_info);
                }
                for ( $i=1; $i<$max; $i+=1)
                {

                        if (!empty($_REQUEST['mail'])) {
                                $arr[]= "{\"id\": \"".$i."\", \"value\": \"".$user_info[$i-1]['mail'][0]."\", \"info\": \"".$user_info[$i-1]['samaccountname'][0]."\"}";
                        }
                        elseif (!empty($_REQUEST['username'])) {
                                $arr[]= "{\"id\": \"".$i."\", \"value\": \"".$user_info[$i-1]['samaccountname'][0]."\", \"info\": \"".$user_info[$i-1]['mail'][0]."\"}";
                        }
                }
        echo implode (", ", $arr);
        echo "]}";
}

?> 
