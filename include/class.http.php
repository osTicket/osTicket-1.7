<?php
/*********************************************************************
    class.http.php

    Http helper.

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require_once(INCLUDE_DIR.'languages/language_control/languages_processor.php');
class Http {
    
    function header_code_verbose($code) {
        switch($code):
        case 200: return '200 '.lang('ok');
        case 201: return '201 '.lang('created');
        case 204: return '204 '.lang('nocontent');
        case 400: return '400 '.lang('bad_request');
        case 401: return '401 '.lang('unauthorized');
        case 403: return '403 '.lang('forbidden');
        case 404: return '404 '.lang('not_found');
        case 405: return '405 '.lang('method_not_allow');
        case 416: return '416 '.lang('request_range');
        default:  return '500 '.lang('int_server_error');
        endswitch;
    }
    
    function response($code,$content,$contentType='text/html',$charset='UTF-8') {
		
        header('HTTP/1.1 '.Http::header_code_verbose($code));
		header('Status: '.Http::header_code_verbose($code)."\r\n");
		header("Connection: Close\r\n");
		header("Content-Type: $contentType; charset=$charset\r\n");
        header('Content-Length: '.strlen($content)."\r\n\r\n");
       	print $content;
        exit;
    }
	
	function redirect($url,$delay=0,$msg='') {

        if(strstr($_SERVER['SERVER_SOFTWARE'], 'IIS')){
            header("Refresh: $delay; URL=$url");
        }else{
            header("Location: $url");
        }
        exit;
    }

    function download($filename, $type, $data=null) {
        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Cache-Control: public');
        header('Content-Type: '.$type);
        $user_agent = strtolower ($_SERVER['HTTP_USER_AGENT']);
        if (strpos($user_agent,'msie') !== false
                && strpos($user_agent,'win') !== false) {
            header('Content-Disposition: filename="'.basename($filename).'";');
        } else {
            header('Content-Disposition: attachment; filename="'
                .basename($filename).'"');
        }
        header('Content-Transfer-Encoding: binary');
        if ($data !== null) {
            header('Content-Length: '.strlen($data));
            print $data;
            exit;
        }
    }
}
?>
