<?php
class LDAP {

	
	static function ldapSqlConnect($ldap_id,&$outp=NULL,$debug=false) {
		require_once(ROOT_DIR.'main.inc.php');
		$sqlquery='SELECT ' . TABLE_PREFIX . 'ldap_config.ldap_controller, ' . TABLE_PREFIX . 'ldap_config.ldap_port from ' . TABLE_PREFIX . 'ldap_config WHERE ldap_id = '.$ldap_id.';';
		if(($tmp_res=db_query($sqlquery)) && db_num_rows($tmp_res)>0)
		{
			$tmp_row=db_fetch_array($tmp_res);
			if($debug==true)
			{
				$outp.='calling ldap_connect with: "';
			}
			if(LDAP::useSSL($ldap_id)==true)
			{
				$ldap = ldap_connect('ldaps://'.$tmp_row['ldap_controller'].':'.$tmp_row['ldap_port']);
				$outp.='ldaps://'.$tmp_row['ldap_controller'].':'.$tmp_row['ldap_port'].'"<br>';
				if(!$ldap)
				{
					if($debug==true)
					{
						$outp.=ldap_error($ldap).'<br>';
						$outp.='errno: '.strval(ldap_errno($ldap)).'<br>';
					}
				}
			}
			else
			{
				$ldap = ldap_connect($tmp_row['ldap_controller'], $tmp_row['ldap_port']);
				if($debug==true)
				{
					$outp.=$tmp_row['ldap_controller'] . '" and port "' . $tmp_row['ldap_port'] . '"<br>';
				}
			}
		}
		else
		{
			if($debug==true)
			{
				$outp.='no ldap config<br>';
			}
		}
		if($ldap)
		{
			if($debug==true)
			{
				$outp.='setting LDAP_OPT_PROTOCOL_VERSION to 3 and LDAP_OPT_REFERRALS to 0<br>';
			}
			ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
			ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);
		}
        return $ldap ? $ldap : false;
	}

    public static function ldapAuthenticate($username, $password) {
		return LDAP::ldapSqlAuthenticate($username, $password);
	}
	
    public static function ldapSqlAuthenticate($username, $password,$ldap_id=-1,&$outp=NULL, $debug=false) {
        if($password=='')
        {
            return false;
        }
        $sqlquery='SELECT ' . TABLE_PREFIX . 'ldap_config.ldap_id, ' . TABLE_PREFIX . 'ldap_config.ldap_suffix, ' . TABLE_PREFIX . 'ldap_config.ldap_rdn, ' . TABLE_PREFIX . 'ldap_config.ldap_admin, ' . TABLE_PREFIX . 'ldap_config.ldap_admin_cn from ' . TABLE_PREFIX . 'ldap_config';
        if($ldap_id!=-1)
        {
            $sqlquery.=' WHERE ' . TABLE_PREFIX . 'ldap_config.ldap_id='.$ldap_id;
        }
        $sqlquery.=' ORDER BY ' . TABLE_PREFIX . 'ldap_config.priority';
        if(($tmp_res=db_query($sqlquery)) && db_num_rows($tmp_res)>0)
        {
            while($rowset = db_fetch_array($tmp_res)) {
                $ldap = LDAP::ldapSqlConnect($rowset['ldap_id']);
                /*if($ldap!=false)
                {
                    echo 'connected successfully<br>';
                }*/
                $old_error_reporting = error_reporting();
                
                if($debug==false)
                {
                    error_reporting (E_ERROR);
                }
                $ldapusr="";
				if(!LDAP::useRDN($rowset['ldap_id']))
				{
					if(strpos($username,$rowset['ldap_suffix'])!==false)
					{
						$ldapusr=$username;
					}
					else
					{
						$ldapusr=$username . $rowset['ldap_suffix'];
					}
				}
				else
				{
					$ldapusr=str_replace('%UID%',$username,$rowset['ldap_rdn']);
					if($username==$rowset['ldap_admin'])
					{
						$ldapusr=str_replace('%CN%',$rowset['ldap_admin_cn'],$ldapusr);
					}
					else
					{
						$usercn=LDAP::ldapGetField($rowset['ldap_id'],'cn',$username);
						$ldapusr=str_replace('%CN%',$usercn,$ldapusr);
					}
					if($debug==true)
					{
						$outp.='using rdn for binding<br>';
					}
				}
                if($debug==true)
                {
                    $outp.='binding to ldap with "'.$ldapusr.'" and his password<br>';
                }
				
                $bind = ldap_bind($ldap, $ldapusr, $password);
                if(!$bind)
                {
                    if($debug==true)
                    {
                        $outp.=ldap_error($ldap).'<br>';
                        $outp.='errno: '.strval(ldap_errno($ldap)).'<br>';
                    }
                }
                ldap_unbind($ldap);
                if($debug==false)
                {
                    error_reporting($old_error_reporting);
                }
                if($bind)
                {
                    break;
                }
            }
            return $bind;
        }
        else
        {
            if($debug==true)
            {
                echo $outp.='no ldap config<br>';
            }
        }
        return false;
    }
	
	public static function ldapGetField($ldap_id, $field,$username, &$outp=NULL, $debug=false)
	{
		$sqlquery='SELECT ' . TABLE_PREFIX . 'ldap_config.ldap_id, ' . TABLE_PREFIX . 'ldap_config.ldap_suffix, ' . TABLE_PREFIX . 'ldap_config.ldap_rdn, ' . TABLE_PREFIX . 'ldap_config.ldap_admin_cn, ' . TABLE_PREFIX . 'ldap_config.ldap_filter, ' . TABLE_PREFIX . 'ldap_config.ldap_admin, ' . TABLE_PREFIX . 'ldap_config.ldap_domain from ' . TABLE_PREFIX . 'ldap_config WHERE ldap_id='.$ldap_id.';';
		$fieldcontent="";
		if(($tmp_res=db_query($sqlquery)) && db_num_rows($tmp_res)>0)
		{
			$rowset = db_fetch_array($tmp_res); 
			$ldap = LDAP::ldapSqlConnect($rowset['ldap_id'],$outp,true);
			if ($ldap) {
				if(LDAP::ldapSqlAuthenticate($rowset['ldap_admin'],LDAP::getPasswd($rowset['ldap_id']),$ldap_id,$outp,true))
				{
					$ldapusr="";
					if(!LDAP::useRDN($rowset['ldap_id']))
					{
						$ldapusr=$rowset['ldap_admin'] . $rowset['ldap_suffix'];
					}
					else
					{
						$ldapusr=str_replace('%UID%',$username,$rowset['ldap_rdn']);
						$ldapusr=str_replace('%CN%',$rowset['ldap_admin_cn'],$ldapusr);
					}
					$bind = ldap_bind($ldap, $ldapusr, LDAP::getPasswd($rowset['ldap_id']));
					if($bind)
					{
						$LDAPFieldsToFind = array($field);
						if($username=="")
						{
							$username=$rowset['ldap_admin'];
						}
						$ldapFilter="";
						if($rowset['ldap_filter']==""||$rowset['ldap_filter']==NULL)
						{
							$ldapFilter='(&(sAMAccountName='.$username.'))';
							if(debug==true)
							{
								$outp.='using the default filter: "'.$ldapFilter.'"<br>';
							}
						}
						else
						{
							$ldapFilter=str_replace('%USERNAME%',$username,$rowset['ldap_filter']);
							if(debug==true)
							{
								$outp.='using the filter: "'.$ldapFilter.'"<br>';
							}
						}
						if($debug==true)
						{
							$outp.='calling ldap_search with the domain: "'.$rowset['ldap_domain'].'", the Filter: "'.$ldapFilter.'" and the Attributes: "array("'.$field.'")"<br>';
						}
						$results = ldap_search($ldap, $rowset['ldap_domain'], $ldapFilter, $LDAPFieldsToFind);
						$info = ldap_get_entries($ldap, $results);
						$row=0;

						if($info["count"] > 0){
							$fieldcontent=$info[0][$field][0];
							if($debug==true)
							{
								$outp.='LDAP returned field data: "'.$fieldcontent.'"<br>';
							}
						}
						ldap_unbind($ldap);
					}
					else
					{
						if($debug==true)
						{
							$outp.="Bind failed, couldn't get field data<br>";
						}
					}
				}
				else
				{
					if($debug==true)
					{
						$outp.='Cannot authenticate with LDAP server.<br>';
					}
				}
			}else{
				if($debug==true)
				{
					$outp.='Cannot connect to LDAP server.<br>';
				}
			}
		}
		else
		{
			if($debug==true)
			{
				$outp.='no ldap config<br>';
			}
		}
		return $fieldcontent;
	}
	
	public static function useSSL($ldap_id)
	{
		$sqlquery='SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = "'. TABLE_PREFIX . 'ldap_config" AND COLUMN_NAME = "ldap_ssl"';
		if(($tmp_res=db_query($sqlquery)) && db_num_rows($tmp_res)>0)
		{
			$sqlquery='SELECT ' . TABLE_PREFIX . 'ldap_config.ldap_ssl from ' . TABLE_PREFIX . 'ldap_config WHERE ldap_id = '.$ldap_id.';';
			if(($tmp_res=db_query($sqlquery)) && db_num_rows($tmp_res)>0)
			{
				$tmp_row=db_fetch_array($tmp_res);
				return $tmp_row['ldap_ssl'];
			}
		}
		return false;
	}
	
	public static function useRDN($ldap_id)
	{
		$sqlquery='SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = "'. TABLE_PREFIX . 'ldap_config" AND COLUMN_NAME = "ldap_use_rdn"';
		if(($tmp_res=db_query($sqlquery)) && db_num_rows($tmp_res)>0)
		{
			$sqlquery='SELECT ' . TABLE_PREFIX . 'ldap_config.ldap_use_rdn from ' . TABLE_PREFIX . 'ldap_config WHERE ldap_id = '.$ldap_id.';';
			if(($tmp_res=db_query($sqlquery)) && db_num_rows($tmp_res)>0)
			{
				$tmp_row=db_fetch_array($tmp_res);
				return $tmp_row['ldap_use_rdn'];
			}
		}
		return false;
	}
	
	public static function getTemporaryTicketNum($email)
	{
		$sqlquery='SELECT email, subject FROM '.TICKET_TABLE.' T1 WHERE subject LIKE "ldap_temporary" AND email LIKE "'.$email.'"';
		if(($tmp_res=db_query($sqlquery)) && db_num_rows($tmp_res)>0)
		{
			return db_num_rows($tmp_res);
		}
		return 0;
	}
	
	public static function ldapClientForceLogin()
	{
		$sqlquery='SHOW TABLES LIKE "' . TABLE_PREFIX . 'ldap_config";';
		if(($tmp_res=db_query($sqlquery)) && db_num_rows($tmp_res)>0)
		{
			$sqlquery='SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = "'. TABLE_PREFIX . 'ldap_config" AND COLUMN_NAME = "ldap_client_forcelogin"';
			if(($tmp_res=db_query($sqlquery)) && db_num_rows($tmp_res)>0)
			{
				//get number of ldap entries with forced login
				$sqlquery='SELECT ' . TABLE_PREFIX . 'ldap_config.ldap_client_forcelogin FROM ' . TABLE_PREFIX . 'ldap_config WHERE ldap_client_forcelogin=1;';
				if(($tmp_res=db_query($sqlquery)) && db_num_rows($tmp_res)>0)
				{
					$forced_login_entries=db_num_rows($tmp_res);
					$sqlquery='SELECT ' . TABLE_PREFIX . 'ldap_config.ldap_client_forcelogin FROM ' . TABLE_PREFIX . 'ldap_config;';
					if(($tmp_res=db_query($sqlquery)) && db_num_rows($tmp_res)>0)
					{
						if($forced_login_entries==db_num_rows($tmp_res)) //forced login should be set in all ldap entries
						{
							return true;
						}
					}
				}
			}
		}
        return false;
	}
	
	public static function ldapGetAuthvar()
	{
		$sqlquery='SHOW TABLES LIKE "' . TABLE_PREFIX . 'ldap_config";';
		if(($tmp_res=db_query($sqlquery)) && db_num_rows($tmp_res)>0)
		{
			//check if the column 'ldap_auth_var' exists
			$sqlquery='SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = "'. TABLE_PREFIX . 'ldap_config" AND COLUMN_NAME = "ldap_auth_var"';
			if(($tmp_res=db_query($sqlquery)) && db_num_rows($tmp_res)>0)
			{
				$sqlquery='SELECT ' . TABLE_PREFIX . 'ldap_config.ldap_auth_var FROM ' . TABLE_PREFIX . 'ldap_config';
				if(($tmp_res=db_query($sqlquery)) && db_num_rows($tmp_res)>0)
				{
					$rowset = db_fetch_array($tmp_res);
					return $rowset['ldap_auth_var'];
				}
			}
			else
			{
				$sqlquery="ALTER TABLE `". TABLE_PREFIX . "ldap_config` ADD `ldap_auth_var` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `ldap_ssl` ";
				db_query($sqlquery);
			}
		}
        return "";
	}
	
	public static function useSSO()
	{
		$sqlquery='SHOW TABLES LIKE "' . TABLE_PREFIX . 'ldap_config";';
		if(($tmp_res=db_query($sqlquery)) && db_num_rows($tmp_res)>0)
		{
			//check if the column 'ldap_use_sso' exists
			$sqlquery='SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = "'. TABLE_PREFIX . 'ldap_config" AND COLUMN_NAME = "ldap_use_sso"';
			if(($tmp_res=db_query($sqlquery)) && db_num_rows($tmp_res)>0)
			{
				$sqlquery='SELECT ' . TABLE_PREFIX . 'ldap_config.ldap_use_sso FROM ' . TABLE_PREFIX . 'ldap_config WHERE ldap_use_sso=1;';
				if(($tmp_res=db_query($sqlquery)) && db_num_rows($tmp_res)>0)
				{
					return true;
				}
			}
			else
			{
				$sqlquery="ALTER TABLE `". TABLE_PREFIX . "ldap_config` ADD `ldap_use_sso` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `ldap_ssl` ";
				db_query($sqlquery);
			}
		}
        return false;
	}
	
	public static function ldapClientAutofill()
	{
		$sqlquery='SHOW TABLES LIKE "' . TABLE_PREFIX . 'ldap_config";';
		if(($tmp_res=db_query($sqlquery)) && db_num_rows($tmp_res)>0)
		{
			$sqlquery='SELECT ' . TABLE_PREFIX . 'ldap_config.ldap_client_autofill FROM ' . TABLE_PREFIX . 'ldap_config WHERE ldap_client_autofill=1;';
			if(($tmp_res=db_query($sqlquery)) && db_num_rows($tmp_res)>0)
			{
				return true;
			}
		}
        return false;
	}
	
	public static function ldapClientActive()
	{
		$sqlquery='SHOW TABLES LIKE "' . TABLE_PREFIX . 'ldap_config";';
		if(($tmp_res=db_query($sqlquery)) && db_num_rows($tmp_res)>0)
		{
			$sqlquery='SELECT ' . TABLE_PREFIX . 'ldap_config.ldap_client_active FROM ' . TABLE_PREFIX . 'ldap_config WHERE ldap_client_active=1;';
			if(($tmp_res=db_query($sqlquery)) && db_num_rows($tmp_res)>0)
			{
				return true;
			}
		}
        return false;
	}
	
	public static function ldapActive()
	{
		$sqlquery='SHOW TABLES LIKE "' . TABLE_PREFIX . 'ldap_config";';
		if(($tmp_res=db_query($sqlquery)) && db_num_rows($tmp_res)>0)
		{
			//check if the column 'ldap_filter' exists
			$sqlquery='SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = "'. TABLE_PREFIX . 'ldap_config" AND COLUMN_NAME = "ldap_filter"';
			if(($tmp_res=db_query($sqlquery)) && db_num_rows($tmp_res)>0)
			{
				//do nothing
			}
			else
			{
				$sqlquery="ALTER TABLE `". TABLE_PREFIX . "ldap_config` ADD `ldap_filter` text COLLATE utf8_unicode_ci NOT NULL AFTER `ldap_suffix` ";
				db_query($sqlquery);
			}
			//check if the column 'ldap_rdn' exists
			$sqlquery='SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = "'. TABLE_PREFIX . 'ldap_config" AND COLUMN_NAME = "ldap_rdn"';
			if(($tmp_res=db_query($sqlquery)) && db_num_rows($tmp_res)>0)
			{
				//do nothing
			}
			else
			{
				$sqlquery="ALTER TABLE `". TABLE_PREFIX . "ldap_config` ADD `ldap_rdn` text COLLATE utf8_unicode_ci NOT NULL AFTER `ldap_suffix` ";
				db_query($sqlquery);
			}
			//check if the column 'ldap_admin_cn' exists
			$sqlquery='SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = "'. TABLE_PREFIX . 'ldap_config" AND COLUMN_NAME = "ldap_admin_cn"';
			if(($tmp_res=db_query($sqlquery)) && db_num_rows($tmp_res)>0)
			{
				//do nothing
			}
			else
			{
				$sqlquery="ALTER TABLE `". TABLE_PREFIX . "ldap_config` ADD `ldap_admin_cn` text COLLATE utf8_unicode_ci NOT NULL AFTER `ldap_suffix` ";
				db_query($sqlquery);
			}
			//check if the column 'ldap_use_rdn' exists
			$sqlquery='SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = "'. TABLE_PREFIX . 'ldap_config" AND COLUMN_NAME = "ldap_use_rdn"';
			if(($tmp_res=db_query($sqlquery)) && db_num_rows($tmp_res)>0)
			{
				//do nothing
			}
			else
			{
				$sqlquery="ALTER TABLE `". TABLE_PREFIX . "ldap_config` ADD `ldap_use_rdn` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `ldap_client_autofill` ";
				db_query($sqlquery);
			}
			//check if the column 'ldap_use_sso' exists
			$sqlquery='SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = "'. TABLE_PREFIX . 'ldap_config" AND COLUMN_NAME = "ldap_use_sso"';
			if(($tmp_res=db_query($sqlquery)) && db_num_rows($tmp_res)>0)
			{
				//do nothing
			}
			else
			{
				$sqlquery="ALTER TABLE `". TABLE_PREFIX . "ldap_config` ADD `ldap_use_sso` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `ldap_ssl` ";
				db_query($sqlquery);
			}
			$sqlquery='SELECT ' . TABLE_PREFIX . 'ldap_config.ldap_active FROM ' . TABLE_PREFIX . 'ldap_config WHERE ldap_active=1;';
			if(($tmp_res=db_query($sqlquery)) && db_num_rows($tmp_res)>0)
			{
				return true;
			}
		}
		else
		{
			$sqlquery="CREATE TABLE IF NOT EXISTS `". TABLE_PREFIX . "ldap_config` (
				  `ldap_id` int(11) NOT NULL AUTO_INCREMENT,
				  `priority` int(11) NOT NULL,
				  `ldap_domain` text COLLATE utf8_unicode_ci NOT NULL,
				  `ldap_suffix` text COLLATE utf8_unicode_ci NOT NULL,
				  `ldap_filter` text COLLATE utf8_unicode_ci NOT NULL,
				  `ldap_controller` text COLLATE utf8_unicode_ci NOT NULL,
				  `ldap_use_rdn` tinyint(1) unsigned NOT NULL DEFAULT '0',
				  `ldap_rdn` text COLLATE utf8_unicode_ci NOT NULL,
				  `ldap_admin` text COLLATE utf8_unicode_ci NOT NULL,
				  `ldap_admin_cn` text COLLATE utf8_unicode_ci NOT NULL,
				  `ldap_admin_pw` text COLLATE utf8_unicode_ci NOT NULL,
				  `ldap_email_field` text COLLATE utf8_unicode_ci NOT NULL,
				  `ldap_firstname_field` text COLLATE utf8_unicode_ci NOT NULL,
				  `ldap_lastname_field` text COLLATE utf8_unicode_ci NOT NULL,
				  `ldap_user_field` text COLLATE utf8_unicode_ci NOT NULL,
				  `ldap_auth_var` text COLLATE utf8_unicode_ci NOT NULL,
				  `ldap_phone_field` text COLLATE utf8_unicode_ci NOT NULL,
				  `ldap_ext_length` int(11) NOT NULL,
				  `ldap_port` int(11) NOT NULL,
				  `ldap_active` tinyint(1) unsigned NOT NULL DEFAULT '0',
				  `ldap_client_active` tinyint(1) unsigned NOT NULL DEFAULT '0',
				  `ldap_client_autofill` tinyint(1) unsigned NOT NULL DEFAULT '0',
				  `ldap_client_forcelogin` tinyint(1) unsigned NOT NULL DEFAULT '0',
				  `ldap_ssl` tinyint(1) unsigned NOT NULL DEFAULT '0',
				  `ldap_use_sso` tinyint(1) unsigned NOT NULL DEFAULT '0',
				  PRIMARY KEY (`ldap_id`)
				) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;";
				db_query($sqlquery);
		}
        return false;
	}
	
	public static function getPasswd($id)
	{
		$sqlquery='SELECT ' . TABLE_PREFIX . 'ldap_config.ldap_id, ' . TABLE_PREFIX . 'ldap_config.ldap_admin_pw FROM ' . TABLE_PREFIX . 'ldap_config WHERE ldap_id='.$id.';';
		if(($tmp_res=db_query($sqlquery)) && db_num_rows($tmp_res)>0)
		{
			$tmparray=db_fetch_array($tmp_res);
			if(strlen($tmparray['ldap_admin_pw'])>1)
			{
				return $tmparray['ldap_admin_pw']?Mcrypt::decrypt($tmparray['ldap_admin_pw'],SECRET_SALT):'';
			}
		}
        return '';
	}

    public static function save($id,$vars,&$errors) {
        global $cfg;
		
        //very basic checks

        $vars['ldap_domain']=Format::striptags(trim($vars['ldap_domain']));
        $vars['ldap_admin']=Format::striptags(trim($vars['ldap_admin']));
        $vars['ldap_admin_cn']=Format::striptags(trim($vars['ldap_admin_cn']));
        $vars['ldap_controller']=Format::striptags(trim($vars['ldap_controller']));
        $vars['ldap_suffix']=Format::striptags(trim($vars['ldap_suffix']));
        $vars['ldap_filter']=Format::striptags(trim($vars['ldap_filter']));
        $vars['ldap_rdn']=Format::striptags(trim($vars['ldap_rdn']));
        $vars['ldap_admin_cn']=Format::striptags(trim($vars['ldap_admin_cn']));
        $vars['ldap_email_field']=Format::striptags(trim($vars['ldap_email_field']));
        $vars['ldap_firstname_field']=Format::striptags(trim($vars['ldap_firstname_field']));
        $vars['ldap_lastname_field']=Format::striptags(trim($vars['ldap_lastname_field']));
        $vars['ldap_user_field']=Format::striptags(trim($vars['ldap_user_field']));
        $vars['ldap_auth_var']=Format::striptags(trim($vars['ldap_auth_var']));
        $vars['ldap_phone_field']=Format::striptags(trim($vars['ldap_phone_field']));

        if(!$vars['ldap_domain'])
            $errors['ldap_domain']='Domain required';
			
        if(!$vars['ldap_controller'])
            $errors['ldap_controller']='Controller required';
			
        if(!$vars['ldap_port'])
            $errors['ldap_port']='Port required';
			
        if(!$vars['ldap_suffix'])
            $errors['ldap_suffix']='Suffix required';
			
        if(!$vars['ldap_filter'])
            $errors['ldap_filter']='Filter required';
			
        if(!$vars['ldap_email_field'])
            $errors['ldap_email_field']='Email Field required';
			
        if(!$vars['ldap_firstname_field'])
            $errors['ldap_firstname_field']='First Name Field required';
			
        if(!$vars['ldap_lastname_field'])
            $errors['ldap_lastname_field']='Last Name Field required';
			
        if(!$vars['ldap_user_field'])
            $errors['ldap_user_field']='User Field required';
			
        if(!$vars['ldap_phone_field'])
            $errors['ldap_phone_field']='Phone Field required';
		
		if($vars['ldap_use_rdn'])
		{
			if(!$vars['ldap_rdn'])
			{
				$errors['ldap_rdn']='RDN required';
			}
			if(!$vars['ldap_admin_cn'])
			{
				$errors['ldap_admin_cn']='CN required';
			}
		}
		
		if($vars['ldap_use_sso'])
		{
			if(!$vars['ldap_auth_var'])
				$errors['ldap_auth_var']='Auth Variable required';
		}
			
        if(!$vars['priority'])
		{
            $errors['priority']='Priority required';
		}
		else if($vars['priority']<1||$vars['priority']>99)
		{
            $errors['priority']='Invalid Priority';
		}
		if($vars['ldap_client_autofill']&&!$vars['ldap_client_active'])
		{
			$errors['ldap_client_autofill']='LDAP Clientaccess must be active';
		}
		
		//check if the ldap config table exists
		$sqlquery='SHOW TABLES LIKE "' . TABLE_PREFIX . 'ldap_config";';
		if(($tmp_res=db_query($sqlquery)) && db_num_rows($tmp_res)>0)
		{
			//check if the column 'ldap_ssl' exists
			$sqlquery='SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = "'. TABLE_PREFIX . 'ldap_config" AND COLUMN_NAME = "ldap_ssl"';
			if(($tmp_res=db_query($sqlquery)) && db_num_rows($tmp_res)>0)
			{
				//do nothing
			}
			else
			{
				$sqlquery="ALTER TABLE `". TABLE_PREFIX . "ldap_config` ADD `ldap_ssl` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `ldap_client_autofill` ";
				db_query($sqlquery);
			}
			//check if the column 'ldap_client_forcelogin' exists
			$sqlquery='SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = "'. TABLE_PREFIX . 'ldap_config" AND COLUMN_NAME = "ldap_client_forcelogin"';
			if(($tmp_res=db_query($sqlquery)) && db_num_rows($tmp_res)>0)
			{
				//do nothing
			}
			else
			{
				$sqlquery="ALTER TABLE `". TABLE_PREFIX . "ldap_config` ADD `ldap_client_forcelogin` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `ldap_client_autofill` ";
				db_query($sqlquery);
			}
			//check if the column 'ldap_filter' exists
			$sqlquery='SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = "'. TABLE_PREFIX . 'ldap_config" AND COLUMN_NAME = "ldap_filter"';
			if(($tmp_res=db_query($sqlquery)) && db_num_rows($tmp_res)>0)
			{
				//do nothing
			}
			else
			{
				$sqlquery="ALTER TABLE `". TABLE_PREFIX . "ldap_config` ADD `ldap_filter` text COLLATE utf8_unicode_ci NOT NULL AFTER `ldap_suffix` ";
				db_query($sqlquery);
			}
			//check if the column 'ldap_rdn' exists
			$sqlquery='SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = "'. TABLE_PREFIX . 'ldap_config" AND COLUMN_NAME = "ldap_rdn"';
			if(($tmp_res=db_query($sqlquery)) && db_num_rows($tmp_res)>0)
			{
				//do nothing
			}
			else
			{
				$sqlquery="ALTER TABLE `". TABLE_PREFIX . "ldap_config` ADD `ldap_rdn` text COLLATE utf8_unicode_ci NOT NULL AFTER `ldap_suffix` ";
				db_query($sqlquery);
			}
			//check if the column 'ldap_admin_cn' exists
			$sqlquery='SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = "'. TABLE_PREFIX . 'ldap_config" AND COLUMN_NAME = "ldap_admin_cn"';
			if(($tmp_res=db_query($sqlquery)) && db_num_rows($tmp_res)>0)
			{
				//do nothing
			}
			else
			{
				$sqlquery="ALTER TABLE `". TABLE_PREFIX . "ldap_config` ADD `ldap_admin_cn` text COLLATE utf8_unicode_ci NOT NULL AFTER `ldap_suffix` ";
				db_query($sqlquery);
			}
			//check if the column 'ldap_use_rdn' exists
			$sqlquery='SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = "'. TABLE_PREFIX . 'ldap_config" AND COLUMN_NAME = "ldap_use_rdn"';
			if(($tmp_res=db_query($sqlquery)) && db_num_rows($tmp_res)>0)
			{
				//do nothing
			}
			else
			{
				$sqlquery="ALTER TABLE `". TABLE_PREFIX . "ldap_config` ADD `ldap_use_rdn` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `ldap_client_autofill` ";
				db_query($sqlquery);
			}
			//check if the column 'ldap_user_field' exists
			$sqlquery='SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = "'. TABLE_PREFIX . 'ldap_config" AND COLUMN_NAME = "ldap_user_field"';
			if(($tmp_res=db_query($sqlquery)) && db_num_rows($tmp_res)>0)
			{
				//do nothing
			}
			else
			{
				$sqlquery="ALTER TABLE `". TABLE_PREFIX . "ldap_config` ADD `ldap_user_field` text COLLATE utf8_unicode_ci NOT NULL AFTER `ldap_lastname_field` ";
				db_query($sqlquery);
			}
			//check if the column 'ldap_auth_var' exists
			$sqlquery='SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = "'. TABLE_PREFIX . 'ldap_config" AND COLUMN_NAME = "ldap_auth_var"';
			if(($tmp_res=db_query($sqlquery)) && db_num_rows($tmp_res)>0)
			{
				//do nothing
			}
			else
			{
				$sqlquery="ALTER TABLE `". TABLE_PREFIX . "ldap_config` ADD `ldap_auth_var` text COLLATE utf8_unicode_ci NOT NULL AFTER `ldap_lastname_field` ";
				db_query($sqlquery);
			}
			//check if the column 'ldap_use_sso' exists
			$sqlquery='SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = "'. TABLE_PREFIX . 'ldap_config" AND COLUMN_NAME = "ldap_use_sso"';
			if(($tmp_res=db_query($sqlquery)) && db_num_rows($tmp_res)>0)
			{
				//do nothing
			}
			else
			{
				$sqlquery="ALTER TABLE `". TABLE_PREFIX . "ldap_config` ADD `ldap_use_sso` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `ldap_ssl` ";
				db_query($sqlquery);
			}
		}
		else
		{
			$sqlquery="CREATE TABLE IF NOT EXISTS `". TABLE_PREFIX . "ldap_config` (
				  `ldap_id` int(11) NOT NULL AUTO_INCREMENT,
				  `priority` int(11) NOT NULL,
				  `ldap_domain` text COLLATE utf8_unicode_ci NOT NULL,
				  `ldap_suffix` text COLLATE utf8_unicode_ci NOT NULL,
				  `ldap_filter` text COLLATE utf8_unicode_ci NOT NULL,
				  `ldap_controller` text COLLATE utf8_unicode_ci NOT NULL,
				  `ldap_use_rdn` tinyint(1) unsigned NOT NULL DEFAULT '0',
				  `ldap_rdn` text COLLATE utf8_unicode_ci NOT NULL,
				  `ldap_admin` text COLLATE utf8_unicode_ci NOT NULL,
				  `ldap_admin_cn` text COLLATE utf8_unicode_ci NOT NULL,
				  `ldap_admin_pw` text COLLATE utf8_unicode_ci NOT NULL,
				  `ldap_email_field` text COLLATE utf8_unicode_ci NOT NULL,
				  `ldap_firstname_field` text COLLATE utf8_unicode_ci NOT NULL,
				  `ldap_lastname_field` text COLLATE utf8_unicode_ci NOT NULL,
				  `ldap_user_field` text COLLATE utf8_unicode_ci NOT NULL,
				  `ldap_auth_var` text COLLATE utf8_unicode_ci NOT NULL,
				  `ldap_phone_field` text COLLATE utf8_unicode_ci NOT NULL,
				  `ldap_ext_length` int(11) NOT NULL,
				  `ldap_port` int(11) NOT NULL,
				  `ldap_active` tinyint(1) unsigned NOT NULL DEFAULT '0',
				  `ldap_client_active` tinyint(1) unsigned NOT NULL DEFAULT '0',
				  `ldap_client_autofill` tinyint(1) unsigned NOT NULL DEFAULT '0',
				  `ldap_client_forcelogin` tinyint(1) unsigned NOT NULL DEFAULT '0',
				  `ldap_ssl` tinyint(1) unsigned NOT NULL DEFAULT '0',
				  `ldap_use_sso` tinyint(1) unsigned NOT NULL DEFAULT '0',
				  PRIMARY KEY (`ldap_id`)
				) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;";
				db_query($sqlquery);
		}
		
		
		if($vars['ldap_client_active'])
		{
			$sqlquery='SELECT ' . TABLE_PREFIX . 'config.show_related_tickets FROM ' . TABLE_PREFIX . 'config;';
			if(($tmp_res=db_query($sqlquery)) && db_num_rows($tmp_res)>0)
			{
				$tmparray=db_fetch_array($tmp_res);
				if(!$tmparray['show_related_tickets'])
				{
					$errors['ldap_client_active']='The <b>Show Related Tickets</b> setting must be active.';
				}
			}
		}

        if($vars['ldap_active']) {
            if(!$vars['ldap_admin'])
                $errors['ldap_admin']='Admin missing';
                
            if(!$vars['ldap_admin_pw'] && (!$vars['dpasswd'] || $vars['dpasswd']==''))
                $errors['ldap_admin_pw']='Password required';
        }

        //abort on errors
        if($errors) return false;
        
        if(!$errors) {
            $sql='SELECT ldap_id FROM ' . TABLE_PREFIX . 'ldap_config'
                .' WHERE ldap_domain='.db_input($vars['ldap_domain']).' AND ldap_controller='.db_input($vars['ldap_controller']);
            if($id)
                $sql.=' AND ldap_id!='.db_input($id);
                
            if(db_num_rows(db_query($sql)))
                $errors['ldap_domain']=$errors['ldap_controller']='Domain/Controller combination already in-use.';
        }
        
        $passwd=$vars['ldap_admin_pw']?$vars['ldap_admin_pw']:$vars['dpasswd'];
        if(!$errors && $vars['ldap_active']) {
			$ldap = ldap_connect($vars['ldap_controller'], $vars['ldap_port']);
			ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
			ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);
            if(!$ldap) {
                $errors['err']=sprintf("Couldn't connect to %s with current settings.",$vars['ldap_controller']);
                $errors['ldap_controller']=$errors['err'];
			}
        }
		
        if($errors) return false;
       
        $sql='ldap_domain='.db_input($vars['ldap_domain']).
             ',ldap_admin='.db_input(Format::striptags($vars['ldap_admin'])).
             ',ldap_admin_cn='.db_input(Format::striptags($vars['ldap_admin_cn'])).
             ',ldap_controller='.db_input($vars['ldap_controller']).
             ',ldap_suffix='.db_input($vars['ldap_suffix']).
             ',ldap_filter='.db_input($vars['ldap_filter']).
             ',ldap_rdn='.db_input($vars['ldap_rdn']).
             ',ldap_use_rdn='.db_input($vars['ldap_use_rdn']).
             ',ldap_active='.db_input($vars['ldap_active']).
             ',priority='.db_input($vars['priority']).
             ',ldap_email_field='.db_input($vars['ldap_email_field']).
             ',ldap_firstname_field='.db_input($vars['ldap_firstname_field']).
             ',ldap_lastname_field='.db_input($vars['ldap_lastname_field']).
             ',ldap_user_field='.db_input($vars['ldap_user_field']).
             ',ldap_auth_var='.db_input($vars['ldap_auth_var']).
             ',ldap_phone_field='.db_input($vars['ldap_phone_field']).
             ',ldap_port='.db_input($vars['ldap_port']).
             ',ldap_ext_length='.db_input($vars['ldap_ext_length']).
             ',ldap_client_active='.db_input($vars['ldap_client_active']).
             ',ldap_client_autofill='.db_input($vars['ldap_client_autofill']).
             ',ldap_client_forcelogin='.db_input($vars['ldap_client_forcelogin']).
             ',ldap_use_sso='.db_input($vars['ldap_use_sso']).
             ',ldap_ssl='.db_input($vars['ldap_ssl']);
        
        if($vars['ldap_admin_pw']) //New password - encrypt.
            $sql.=',ldap_admin_pw='.db_input(Mcrypt::encrypt($vars['ldap_admin_pw'],SECRET_SALT));
        
        if($id) { //update
            $sql='UPDATE '.TABLE_PREFIX . 'ldap_config SET '.$sql.' WHERE ldap_id='.db_input($id);
            if(db_query($sql))
                return true;
                
            $errors['err']='Unable to update ldap connection. Internal error occurred';
        }else {
            $sql='INSERT INTO '.TABLE_PREFIX . 'ldap_config SET '.$sql;
            if(db_query($sql) && ($id=db_insert_id()))
                return $id;

            $errors['err']='Unable to add ldap connection. Internal error';
        }
        
        return false;
    }

    function create($vars,&$errors) {
        return LDAP::save(0,$vars,$errors);
    }

    function delete($id) {
		if(LDAP::checkID($id))
		{
			$sql='DELETE FROM '.TABLE_PREFIX . 'ldap_config WHERE ldap_id='.db_input($id).' LIMIT 1';
			if(db_query($sql) && ($num=db_affected_rows())) {
				return $num;
			}
		}
        return 0;
    }
	
	public static function update($id,$vars,&$errors)
	{
		$vars=$vars;
        $vars['dpasswd']=LDAP::getPasswd($id); //Current decrypted password.

        if(!LDAP::save($id, $vars, $errors))
            return false;
			
		return true;
	}
	
	public static function checkID($id)
	{
		$sqlquery='SELECT ' . TABLE_PREFIX . 'ldap_config.ldap_id FROM ' . TABLE_PREFIX . 'ldap_config WHERE ldap_id='.$id.';';
		if(($tmp_res=db_query($sqlquery)) && db_num_rows($tmp_res)>0)
		{
			return true;
		}
		return false;
	}
	
	public static function ldapGetUsernameFromEmail($email,&$outp=NULL,$debug=false)
	{
		$sqlquery='SELECT ' . TABLE_PREFIX . 'ldap_config.ldap_id, ' . TABLE_PREFIX . 'ldap_config.ldap_suffix, ' . TABLE_PREFIX . 'ldap_config.ldap_rdn, ' . TABLE_PREFIX . 'ldap_config.ldap_admin_cn, ' . TABLE_PREFIX . 'ldap_config.ldap_admin, ' . TABLE_PREFIX . 'ldap_config.ldap_domain, ' . TABLE_PREFIX . 'ldap_config.ldap_email_field, ' . TABLE_PREFIX . 'ldap_config.ldap_user_field from ' . TABLE_PREFIX . 'ldap_config ORDER BY ' . TABLE_PREFIX . 'ldap_config.priority;';
		$user="";
		if(($tmp_res=db_query($sqlquery)) && db_num_rows($tmp_res)>0)
		{
			while($rowset = db_fetch_array($tmp_res)) {
				$ldap = LDAP::ldapSqlConnect($rowset['ldap_id']);
				if ($ldap) {
					if($debug==true)
					{
						$outp.='<br><br>Debug of function ldapGetUsernameFromEmail(): <br><br>';
						$outp.='getting the user of email: "'.$email.'"<br>';
					}
					$ldapusr="";
					if(!LDAP::useRDN($rowset['ldap_id']))
					{
						$ldapusr=$rowset['ldap_admin'] . $rowset['ldap_suffix'];
					}
					else
					{
						$ldapusr=str_replace('%UID%',$username,$rowset['ldap_rdn']);
						$ldapusr=str_replace('%CN%',$rowset['ldap_admin_cn'],$ldapusr);
						if($debug==true)
						{
							$outp.='using rdn for binding<br>';
						}
					}
					if($debug==true)
					{
						$outp.='binding to ldap with "'.$ldapusr.'" and his password<br>';
					}
					$bind = ldap_bind($ldap, $ldapusr, LDAP::getPasswd($rowset['ldap_id']));
					if($bind)
					{
						$LDAPFieldsToFind = array($rowset['ldap_user_field']);
						if($debug==true)
						{
							$outp.='calling ldap_search with the domain: "'.$rowset['ldap_domain'].'", the Filter: "(&('.$rowset['ldap_email_field'].'='.$email.'))" and the Attributes: "array("'.$rowset['ldap_user_field'].'")"<br>';
						}
						$results = ldap_search($ldap, $rowset['ldap_domain'], "(&(".$rowset['ldap_email_field']."=".$email."))", $LDAPFieldsToFind);
						$info = ldap_get_entries($ldap, $results);
						$row=0;

						if($info["count"] > 0){
							$user=$info[0][$rowset['ldap_user_field']][0];
							if($debug==true)
							{
								$outp.='LDAP returned field data: "'.$user.'"<br>';
							}
							if(trim($user)!="")
							{
								break;
							}
						}
						else
						{
							if($debug==true)
							{
								$outp.='LDAP returned nothing...<br>';
							}
						}
						ldap_unbind($ldap);
					}
					else
					{
						if($debug==true)
						{
							$outp.=ldap_error($ldap).'<br>';
							$outp.='errno: '.strval(ldap_errno($ldap)).'<br>';
						}
						echo 'Cannot authenticate with LDAP server.';
					}
				}else{
					echo 'Cannot connect to LDAP server.';
				}
			}
		}
		else
		{
			echo 'no ldap config';
		}
		return $user;
	}
	
	public static function ldapGetEmail($username,&$outp=NULL,$debug=false)
	{
		$sqlquery='SELECT ' . TABLE_PREFIX . 'ldap_config.ldap_id, ' . TABLE_PREFIX . 'ldap_config.ldap_suffix, ' . TABLE_PREFIX . 'ldap_config.ldap_rdn, ' . TABLE_PREFIX . 'ldap_config.ldap_admin_cn, ' . TABLE_PREFIX . 'ldap_config.ldap_filter, ' . TABLE_PREFIX . 'ldap_config.ldap_admin, ' . TABLE_PREFIX . 'ldap_config.ldap_domain, ' . TABLE_PREFIX . 'ldap_config.ldap_email_field from ' . TABLE_PREFIX . 'ldap_config ORDER BY ' . TABLE_PREFIX . 'ldap_config.priority;';
		$email="";
		if(($tmp_res=db_query($sqlquery)) && db_num_rows($tmp_res)>0)
		{
			while($rowset = db_fetch_array($tmp_res)) {
				$ldap = LDAP::ldapSqlConnect($rowset['ldap_id']);
				if ($ldap) {
					if($debug==true)
					{
						$outp.='<br><br>Debug of function ldapGetEmail(): <br><br>';
						$outp.='getting the email of user: "'.$username.'"<br>';
					}
					$ldapusr="";
					if(!LDAP::useRDN($rowset['ldap_id']))
					{
						$ldapusr=$rowset['ldap_admin'] . $rowset['ldap_suffix'];
					}
					else
					{
						$ldapusr=str_replace('%UID%',$username,$rowset['ldap_rdn']);
						$ldapusr=str_replace('%CN%',$rowset['ldap_admin_cn'],$ldapusr);
						if($debug==true)
						{
							$outp.='using rdn for binding<br>';
						}
					}
					if($debug==true)
					{
						$outp.='binding to ldap with "'.$ldapusr.'" and his password<br>';
					}
					$bind = ldap_bind($ldap, $ldapusr, LDAP::getPasswd($rowset['ldap_id']));
					if($bind)
					{
						$ldapFilter="";
						if($rowset['ldap_filter']==""||$rowset['ldap_filter']==NULL)
						{
							$ldapFilter='(&(sAMAccountName='.$username.'))';
						}
						else
						{
							$ldapFilter=str_replace('%USERNAME%',$username,$rowset['ldap_filter']);
						}
						$LDAPFieldsToFind = array($rowset['ldap_email_field']);
						if($debug==true)
						{
							$outp.='calling ldap_search with the domain: "'.$rowset['ldap_domain'].'", the Filter: "'.$ldapFilter.'" and the Attributes: "array("'.$rowset['ldap_email_field'].'")"<br>';
						}
						$results = ldap_search($ldap, $rowset['ldap_domain'], $ldapFilter, $LDAPFieldsToFind);
						$info = ldap_get_entries($ldap, $results);
						$row=0;

						if($info["count"] > 0){
							$email=$info[0][$rowset['ldap_email_field']][0];
							if($debug==true)
							{
								$outp.='LDAP returned field data: "'.$email.'"<br>';
							}
							if(trim($email)!="")
							{
								break;
							}
						}
						else
						{
							if($debug==true)
							{
								$outp.='LDAP returned nothing...<br>';
							}
						}
						ldap_unbind($ldap);
					}
					else
					{
						if($debug==true)
						{
							$outp.=ldap_error($ldap).'<br>';
							$outp.='errno: '.strval(ldap_errno($ldap)).'<br>';
						}
						echo 'Cannot authenticate with LDAP server.';
					}
				}else{
					echo 'Cannot connect to LDAP server.';
				}
				
			}
		}
		else
		{
			echo 'no ldap config';
		}
		return $email;
	}
	
	public static function ldapGetPhone($username)
	{
		$sqlquery='SELECT ' . TABLE_PREFIX . 'ldap_config.ldap_id, ' . TABLE_PREFIX . 'ldap_config.ldap_suffix, ' . TABLE_PREFIX . 'ldap_config.ldap_rdn, ' . TABLE_PREFIX . 'ldap_config.ldap_admin_cn, ' . TABLE_PREFIX . 'ldap_config.ldap_filter, ' . TABLE_PREFIX . 'ldap_config.ldap_admin, ' . TABLE_PREFIX . 'ldap_config.ldap_domain, ' . TABLE_PREFIX . 'ldap_config.ldap_phone_field, ' . TABLE_PREFIX . 'ldap_config.ldap_ext_length from ' . TABLE_PREFIX . 'ldap_config ORDER BY ' . TABLE_PREFIX . 'ldap_config.priority;';
		$phone="";
		if(($tmp_res=db_query($sqlquery)) && db_num_rows($tmp_res)>0)
		{
			while($rowset = db_fetch_array($tmp_res)) {
				$ldap = LDAP::ldapSqlConnect($rowset['ldap_id']);
				if ($ldap) {
					$ldapusr="";
					if(!LDAP::useRDN($rowset['ldap_id']))
					{
						$ldapusr=$rowset['ldap_admin'] . $rowset['ldap_suffix'];
					}
					else
					{
						$ldapusr=str_replace('%UID%',$username,$rowset['ldap_rdn']);
						$ldapusr=str_replace('%CN%',$rowset['ldap_admin_cn'],$ldapusr);
					}
					$bind = ldap_bind($ldap, $ldapusr, LDAP::getPasswd($rowset['ldap_id']));
					if($bind)
					{
						$ldapFilter="";
						if($rowset['ldap_filter']==""||$rowset['ldap_filter']==NULL)
						{
							$ldapFilter='(&(sAMAccountName='.$username.'))';
						}
						else
						{
							$ldapFilter=str_replace('%USERNAME%',$username,$rowset['ldap_filter']);
						}
						$LDAPFieldsToFind = array($rowset['ldap_phone_field']);
						$results = ldap_search($ldap, $rowset['ldap_domain'], $ldapFilter, $LDAPFieldsToFind);
						$info = ldap_get_entries($ldap, $results);
						$row=0;

						if($info["count"] > 0){
							$phone=$info[0][$rowset['ldap_phone_field']][0];
							if($rowset['ldap_ext_length']>0)
							{
								$phone=substr($phone,0,$rowset['ldap_ext_length']*(-1));
								$phone=trim($phone);
								if($phone!="")
								{
									break;
								}
							}
						}
						ldap_unbind($ldap);
					}
					else
					{
						echo 'Cannot authenticate with LDAP server.';
					}
				}else{
					echo 'Cannot connect to LDAP server.';
				}
				
			}
		}
		else
		{
			echo 'no ldap config';
		}
		return $phone;
	}
	
	public static function ldapGetPhoneExt($username)
	{
		$sqlquery='SELECT ' . TABLE_PREFIX . 'ldap_config.ldap_id, ' . TABLE_PREFIX . 'ldap_config.ldap_suffix, ' . TABLE_PREFIX . 'ldap_config.ldap_rdn, ' . TABLE_PREFIX . 'ldap_config.ldap_admin_cn, ' . TABLE_PREFIX . 'ldap_config.ldap_filter, ' . TABLE_PREFIX . 'ldap_config.ldap_admin, ' . TABLE_PREFIX . 'ldap_config.ldap_domain, ' . TABLE_PREFIX . 'ldap_config.ldap_phone_field, ' . TABLE_PREFIX . 'ldap_config.ldap_ext_length from ' . TABLE_PREFIX . 'ldap_config ORDER BY ' . TABLE_PREFIX . 'ldap_config.priority;';
		$phone="";
		if(($tmp_res=db_query($sqlquery)) && db_num_rows($tmp_res)>0)
		{
			while($rowset = db_fetch_array($tmp_res)) {
				$ldap = LDAP::ldapSqlConnect($rowset['ldap_id']);
				if ($ldap) {
					$ldapusr="";
					if(!LDAP::useRDN($rowset['ldap_id']))
					{
						$ldapusr=$rowset['ldap_admin'] . $rowset['ldap_suffix'];
					}
					else
					{
						$ldapusr=str_replace('%UID%',$username,$rowset['ldap_rdn']);
						$ldapusr=str_replace('%CN%',$rowset['ldap_admin_cn'],$ldapusr);
					}
					$bind = ldap_bind($ldap, $ldapusr, LDAP::getPasswd($rowset['ldap_id']));
					if($bind)
					{
						$ldapFilter="";
						if($rowset['ldap_filter']==""||$rowset['ldap_filter']==NULL)
						{
							$ldapFilter='(&(sAMAccountName='.$username.'))';
						}
						else
						{
							$ldapFilter=str_replace('%USERNAME%',$username,$rowset['ldap_filter']);
						}
						$LDAPFieldsToFind = array($rowset['ldap_phone_field']);
						$results = ldap_search($ldap, $rowset['ldap_domain'], $ldapFilter, $LDAPFieldsToFind);
						$info = ldap_get_entries($ldap, $results);
						$row=0;

						if($info["count"] > 0){
							$phone=$info[0][$rowset['ldap_phone_field']][0];
							$extlen=$rowset['ldap_ext_length'];
							$phone=substr($phone,$extlen*(-1));
							$phone=trim($phone);
							if($phone!="")
							{
								break;
							}
						}
						ldap_unbind($ldap);
					}
					else
					{
						echo 'Cannot authenticate with LDAP server.';
					}
				}else{
					echo 'Cannot connect to LDAP server.';
				}
				
			}
		}
		else
		{
			echo 'no ldap config';
		}
		return $phone;
	}
	
	public static function ldapGetName($username)
	{
		$sqlquery='SELECT ' . TABLE_PREFIX . 'ldap_config.ldap_id, ' . TABLE_PREFIX . 'ldap_config.ldap_suffix, ' . TABLE_PREFIX . 'ldap_config.ldap_rdn, ' . TABLE_PREFIX . 'ldap_config.ldap_admin_cn, ' . TABLE_PREFIX . 'ldap_config.ldap_filter, ' . TABLE_PREFIX . 'ldap_config.ldap_admin, ' . TABLE_PREFIX . 'ldap_config.ldap_domain, ' . TABLE_PREFIX . 'ldap_config.ldap_firstname_field, ' . TABLE_PREFIX . 'ldap_config.ldap_lastname_field from ' . TABLE_PREFIX . 'ldap_config ORDER BY ' . TABLE_PREFIX . 'ldap_config.priority;';
		$name="";
		if(($tmp_res=db_query($sqlquery)) && db_num_rows($tmp_res)>0)
		{
			while($rowset = db_fetch_array($tmp_res)) {
				$ldap = LDAP::ldapSqlConnect($rowset['ldap_id']);
				if ($ldap) {
					$ldapusr="";
					if(!LDAP::useRDN($rowset['ldap_id']))
					{
						$ldapusr=$rowset['ldap_admin'] . $rowset['ldap_suffix'];
					}
					else
					{
						$ldapusr=str_replace('%UID%',$username,$rowset['ldap_rdn']);
						$ldapusr=str_replace('%CN%',$rowset['ldap_admin_cn'],$ldapusr);
					}
					$bind = ldap_bind($ldap, $ldapusr, LDAP::getPasswd($rowset['ldap_id']));
					if($bind)
					{
						$ldapFilter="";
						if($rowset['ldap_filter']==""||$rowset['ldap_filter']==NULL)
						{
							$ldapFilter='(&(sAMAccountName='.$username.'))';
						}
						else
						{
							$ldapFilter=str_replace('%USERNAME%',$username,$rowset['ldap_filter']);
						}
						$LDAPFieldsToFind = array($rowset['ldap_firstname_field'],$rowset['ldap_lastname_field']);
						$results = ldap_search($ldap, $rowset['ldap_domain'], $ldapFilter, $LDAPFieldsToFind);
						$info = ldap_get_entries($ldap, $results);
						$row=0;

						if($info["count"] > 0){
							$name.=$info[0][$rowset['ldap_firstname_field']][0];
							$name.=' ';
							$name.=$info[0][$rowset['ldap_lastname_field']][0];
							if(trim($rowset['ldap_firstname_field'])!=""||trim($rowset['ldap_lastname_field'])!="")
							{
								break;
							}
						}
						ldap_unbind($ldap);
					}
					else
					{
						echo 'Cannot authenticate with LDAP server.';
					}
				}else{
					echo 'Cannot connect to LDAP server.';
				}
				
			}
		}
		else
		{
			echo 'no ldap config';
		}
		return $name;
	}

}  
?>
