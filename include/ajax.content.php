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
                    <hr><strong>'.lang('log_date').':</strong> <em>%s</em> <strong>'.lang('ip_address').':</strong> <em>%s</em></div>',
                    $log->getTitle(),
                    Format::display(str_replace(',',', ',$log->getText())),
                    Format::db_daydatetime($log->getCreateDate()),
                    $log->getIP());
        }else {
            $content='<div style="width:295px;">&nbsp;<strong>Error:</strong>'.lang('unknown_log_id').'</div>';

        }

        return $content;
    }

    function ticket_variables() {

        $content='
<div style="width:680px;">
    <h2>Ticket Variables</h2>
    '
    .lang('non_base_var').
    '<br/>
    <table width="100%" border="0" cellspacing=1 cellpadding=2>
        <tr><td width="55%" valign="top"><b>'.lang('base_variables').'</b></td><td><b>'.lang('other_variables').'</b></td></tr>
        <tr>
            <td width="55%" valign="top">
                <table width="100%" border="0" cellspacing=1 cellpadding=1>
                    <tr><td width="130">%{ticket.id}</td><td>'.lang('ticket_id_int_id').'</td></tr>
                    <tr><td>%{ticket.number}</td><td>'.lang('ticket_number').'</td></tr>
                    <tr><td>%{ticket.email}</td><td>'.lang('email_address').'</td></tr>
                    <tr><td>%{ticket.name}</td><td>'.lang('full_name').'</td></tr>
                    <tr><td>%{ticket.subject}</td><td>'.lang('subject').'</td></tr>
                    <tr><td>%{ticket.phone}</td><td>'.lang('phone_number').' | '.lang('ext').'</td></tr>
                    <tr><td>%{ticket.status}</td><td>'.lang('status').'</td></tr>
                    <tr><td>%{ticket.priority}</td><td>'.lang('priority').'</td></tr>
                    <tr><td>%{ticket.assigned}</td><td>'.lang('assigned_st_te').'</td></tr>
                    <tr><td>%{ticket.create_date}</td><td>'.lang('date_created').'</td></tr>
                    <tr><td>%{ticket.due_date}</td><td>'.lang('due_date').'</td></tr>
                    <tr><td>%{ticket.close_date}</td><td>'.lang('date_closed').'</td></tr>
                    <tr><td>%{ticket.auth_token}</td><td>'.lang('auth_used').'</td></tr>
                    <tr><td>%{ticket.client_link}</td><td>'.lang('client_view_link').'</td></tr>
                    <tr><td>%{ticket.staff_link}</td><td>'.lang('staff_view_link').'</td></tr>
                    <tr><td colspan="2" style="padding:5px 0 5px 0;"><em>'.lang('expan_var').'</em></td></tr>
                    <tr><td>%{ticket.<b>topic</b>}</td><td>'.lang('help_topic').'</td></tr>
                    <tr><td>%{ticket.<b>dept</b>}</td><td>'.lang('department').'</td></tr>
                    <tr><td>%{ticket.<b>staff</b>}</td><td>'.lang('assigned_c_staff').'</td></tr>
                    <tr><td>%{ticket.<b>team</b>}</td><td>'.lang('assigned_closing').'</td></tr>
                </table>
            </td>
            <td valign="top">
                <table width="100%" border="0" cellspacing=1 cellpadding=1>
                    <tr><td width="100">%{message}</td><td>'.lang('incoming_message').'</td></tr>
                    <tr><td>%{response}</td><td>'.lang('outgoing_response').'</td></tr>
                    <tr><td>%{comments}</td><td>'.lang('assign_comments').'</td></tr>
                    <tr><td>%{note}</td><td>'.lang('no_note').' <em>('.lang('expandable').')</em></td></tr>
                    <tr><td>%{assignee}</td><td>'.lang('assigned_st_te').'</td></tr>
                    <tr><td>%{assigner}</td><td>'.lang('staff_assigning').'</td></tr>
                    <tr><td>%{url}</td><td>'.lang('fqdn').'</td></tr>
                </table>
            </td>
        </tr>
    </table>
</div>';

        return $content;
    }
}
?>
