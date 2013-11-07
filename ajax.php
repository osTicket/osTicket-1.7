<?php
/*********************************************************************
    ajax.php

    Ajax utils for client interface.

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/

function clientLoginPage($msg=null) {
    if(!$msg)
        $msg = lang('unauthorized');
    Http::response(403,lang('must_login').': '.Format::htmlchars($msg));
    exit;
}

require('client.inc.php');
require_once(INCLUDE_DIR.'languages/language_control/languages_processor.php');

if(!defined('INCLUDE_DIR'))	Http::response(500, lang('server_conf_error'));
require_once INCLUDE_DIR.'/class.dispatcher.php';
require_once INCLUDE_DIR.'/class.ajax.php';

$dispatcher = patterns('',
    url('^/config/', patterns('ajax.config.php:ConfigAjaxAPI',
        url_get('^client', 'client')
    ))
);
print $dispatcher->resolve($ost->get_path_info());
?>
