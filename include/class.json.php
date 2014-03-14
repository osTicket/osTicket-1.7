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
require_once(INCLUDE_DIR.'languages/language_control/languages_processor.php');

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
            JSON_ERROR_NONE => lang('no_error'),
            JSON_ERROR_DEPTH => lang('max_stack_depth'),
            JSON_ERROR_STATE_MISMATCH => lang('underf_modes_mism'),
            JSON_ERROR_CTRL_CHAR => lang('unexpt_cont_char'),
            JSON_ERROR_SYNTAX => lang('syntax_error'),
            JSON_ERROR_UTF8 => lang('malmormed_utf')
            );
            if ($message = $errors[json_last_error()])
                return $message;
            return lang("unknown_error");
        } else {
            # Doesn't look like Servies_JSON supports errors for decode()
            return lang("unknown_json");
        }
    }
}

class JsonDataEncoder {
    function encode($var) {
        $decoder = new Services_JSON();
        return $decoder->encode($var);
    }
}
