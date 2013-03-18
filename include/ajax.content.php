<?php
/*********************************************************************
    ajax.content.php

    AJAX interface for content fetching...allowed methods.

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/

if(!defined('INCLUDE_DIR')) die('!');
	    
class ContentAjaxAPI extends AjaxController {
   
    function log($id) {

        if($id && ($log=Log::lookup($id))) {
            $content=sprintf('<div style="width:500px;">&nbsp;<strong>%s</strong><br><p>%s</p>
                    <hr><strong>Log Date:</strong> <em>%s</em> <strong>'._('IP Address').':</strong> <em>%s</em></div>',
                    $log->getTitle(),
                    Format::display(str_replace(',',', ',$log->getText())),
                    Format::db_daydatetime($log->getCreateDate()),
                    $log->getIP());
        }else {
            $content='<div style="width:295px;">&nbsp;<strong>'._('Error').':</strong>'._('Unknown or invalid log ID').'</div>';
        }

        return $content;
    }

    function ticket_variables() {

        $content='
<div style="width:680px;">
    <h2>'._('Ticket Variables').'</h2>
    '._('Please note that non-base variables depends on the context of use. Visit osTicket Wiki for up to date documentation.').'
    <br/>
    <table width="100%" border="0" cellspacing=1 cellpadding=2>
        <tr><td width="55%" valign="top"><b>'._('Base Variables').'</b></td><td><b>'._('Other Variables').'</b></td></tr>
        <tr>
            <td width="55%" valign="top">
                <table width="100%" border="0" cellspacing=1 cellpadding=1>
                    <tr><td width="130">%{ticket.id}</td><td>'._('Ticket ID').' ('._('internal ID').')</td></tr>
                    <tr><td>%{ticket.number}</td><td>'._('Ticket number').' ('._('external ID').')</td></tr>
                    <tr><td>%{ticket.email}</td><td>'._('Email address').'</td></tr>
                    <tr><td>%{ticket.name}</td><td>'._('Full name').'</td></tr>
                    <tr><td>%{ticket.subject}</td><td>'._('Subject').'</td></tr>
                    <tr><td>%{ticket.phone}</td><td>'._('Phone number | ext').'</td></tr>
                    <tr><td>%{ticket.status}</td><td>'._('Status').'</td></tr>
                    <tr><td>%{ticket.priority}</td><td>'._('Priority').'</td></tr>
                    <tr><td>%{ticket.assigned}</td><td>'._('Assigned staff and/or team').'</td></tr>
                    <tr><td>%{ticket.create_date}</td><td>'._('Date created').'</td></tr>
                    <tr><td>%{ticket.due_date}</td><td>'._('Due date').'</td></tr>
                    <tr><td>%{ticket.close_date}</td><td>'._('Date closed').'</td></tr>
                    <tr><td>%{ticket.auth_token}</td><td>'._('Auth. token used for auto-login').'</td></tr>
                    <tr><td>%{ticket.client_link}</td><td>'._('Client\'s ticket view link').'</td></tr>
                    <tr><td>%{ticket.staff_link}</td><td>'._('Staff\'s ticket view link').'</td></tr>
                    <tr><td colspan="2" style="padding:5px 0 5px 0;"><em>'._('Expandable Variables (See Wiki)').'</em></td></tr>
                    <tr><td>%{ticket.<b>topic</b>}</td><td>'._('Help topic').'</td></tr>
                    <tr><td>%{ticket.<b>dept</b>}</td><td>'._('Department').'</td></tr>
                    <tr><td>%{ticket.<b>staff</b>}</td><td>'._('Assigned/closing staff').'</td></tr>
                    <tr><td>%{ticket.<b>team</b>}</td><td>'._('Assigned/closing team').'</td></tr>
                </table>
            </td>
            <td valign="top">
                <table width="100%" border="0" cellspacing=1 cellpadding=1>
                    <tr><td width="100">%{message}</td><td>'._('Incoming message').'</td></tr>
                    <tr><td>%{response}</td><td>'._('Outgoing response').'</td></tr>
                    <tr><td>%{comments}</td><td>'._('Assign/transfer comments').'</td></tr>
                    <tr><td>%{note}</td><td>'._('Internal note <em>(expandable)</em>').'</td></tr>
                    <tr><td>%{assignee}</td><td>'._('Assigned staff/team').'</td></tr>
                    <tr><td>%{assigner}</td><td>'._('Staff assigning the ticket').'</td></tr>
                    <tr><td>%{url}</td><td>'._('osTicket\'s base url (FQDN)').'</td></tr>
                </table>
            </td>
        </tr>
    </table>
</div>';

        return $content;
    }
}
?>
