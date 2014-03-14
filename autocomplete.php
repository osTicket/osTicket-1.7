<?php 

$thisdir=str_replace('\\\\', '/', realpath(dirname(__FILE__))).'/'; 
if(!file_exists($thisdir.'main.inc.php')) die(lang('fatal_error'));

require_once($thisdir.'main.inc.php');

function error($n = 403) {
	header("HTTP/1.0 403 Forbidden");
	die('access denied');
}

if( ! isset($_GET['method']) ){	
	error();
}

if( function_exists($_GET['method']))
{
	$_GET['method']();
} else {
	error();
}

function json_output($data = array()) {
	header('Content-Type: application/json');
	print json_encode($data);
}

// -----------------------------------------------
// Handle requst
// -----------------------------------------------

function user_ticket() {
	$data = array('result' => true);

	$sql='SELECT email, name, phone, phone_ext FROM `ost_ticket` WHERE '
		. ' name LIKE(' . db_input('%'.$_POST['name'].'%') . ') GROUP BY name';
     
    if(!($res=db_query($sql)) || !db_num_rows($res))
    {
		json_output(array('result' => false));
    	return;
    }
    while ($row = mysqli_fetch_assoc($res)){
	    $data['data'][] = $row;
	}

	json_output($data);
}