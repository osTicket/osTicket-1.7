<?php
/*********************************************************************
    class.language.php

    For easy to localization
    
    Fanha Giang <fanha99@hotmail.com>
    Copyright (c)  2006-2012 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/

class Language {

  	private $directory;
	private $data = array();
 
	public function Language($directory = '') {
		if ($directory != '')
			$this->directory = $directory;
		else $this->directory = LANGUAGE;
	}
	
  	public function get($key) {
   		return (isset($this->data[$key]) ? $this->data[$key] : $key);
  	}
	
	public function load($filename) {
		$file = LANGUAGE_DIR . $this->directory . '/' . $filename . '.lang.php';
    	
		if (file_exists($file)) {
			$lang = array();
	  		
			require($file);
		
			$this->data = array_merge($this->data, $lang);
			
			return $this->data;
		} else {
			echo 'Error: Could not load language ' . $filename . '!';
			exit();
		}
  	}
}
?>
