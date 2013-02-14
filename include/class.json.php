<?php
/*********************************************************************
    class.json.php

    Parses JSON text data to PHP associative array. Useful mainly for API
    JSON requests. The module will attempt to use the json_* functions
    builtin to PHP5.2+ if they exist and will fall back to a pure-php
    implementation included in JSON.php.

    Jared Hancock
    Copyright (c)  2006-2010 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
    $Id: $
**********************************************************************/

include_once "JSON.php";

class JsonDataParser {
    function parse($stream) {
        $contents = '';
        while (!feof($stream)) {
            $contents .= fread($stream, 8192);
        }
        if (function_exists("json_decode")) {
            return json_decode($contents, true);
        } else {
            # Create associative arrays rather than 'objects'
            $decoder = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
            return $decoder->decode($contents);
        }
    }
    function lastError() {
        if (function_exists("json_last_error")) {
            $errors = array(
            JSON_ERROR_NONE => _('No errors'),
            JSON_ERROR_DEPTH => _('Maximum stack depth exceeded'),
            JSON_ERROR_STATE_MISMATCH => _('Underflow or the modes mismatch'),
            JSON_ERROR_CTRL_CHAR => _('Unexpected control character found'),
            JSON_ERROR_SYNTAX => _('Syntax error, malformed JSON'),
            JSON_ERROR_UTF8 => _('Malformed UTF-8 characters, possibly incorrectly encoded'
            ));
            if ($message = $errors[json_last_error()])
                return $message;
            return _("Unknown error");
        } else {
            # Doesn't look like Servies_JSON supports errors for decode()
            return _("Unknown JSON parsing error");
        }
    }
}

class JsonDataEncoder {
    function encode($var) {
        $decoder = new Services_JSON();
        return $decoder->encode($var);
    }
}
