<?php
/*********************************************************************
    class.api.php

    API

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2012 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
class API {

    var $id;

    var $ht;

    function API($id) {
        $this->id = 0;
        $this->load($id);
    }

    function load($id=0) {

        if(!$id && !($id=$this->getId()))
            return false;

        $sql='SELECT * FROM '.API_KEY_TABLE.' WHERE id='.db_input($id);
        if(!($res=db_query($sql)) || !db_num_rows($res))
            return false;
        
        $this->ht = db_fetch_array($res);
        $this->id = $this->ht['id'];
        
        return true;
    }

    function reload() {
        return $this->load();
    }

    function getId() {
        return $this->id;
    }

    function getKey() {
        return $this->ht['apikey'];
    }

    function getIPAddr() {
        return $this->ht['ipaddr'];
    }
        
    function getNotes() {
        return $this->ht['notes'];
    }

    function getHashtable() {
        return $this->ht;
    }

    function isActive() {
        return ($this->ht['isactive']);
    }

    function canCreateTickets() {
        return ($this->ht['can_create_tickets']);
    }

    function update($vars, &$errors) {

        if(!API::save($this->getId(), $vars, $errors))
            return false;
        
        $this->reload();

        return true;
    }

    function delete() {
        $sql='DELETE FROM '.API_KEY_TABLE.' WHERE id='.db_input($this->getId()).' LIMIT 1';
        return (db_query($sql) && ($num=db_affected_rows()));
    }

    /** Static functions **/
    function add($vars, &$errors) {
        return API::save(0, $vars, $errors);
    }

    function validate($key, $ip) {
        return ($key && $ip && self::getIdByKey($key, $ip));
    }

    function getIdByKey($key, $ip='') {

        $sql='SELECT id FROM '.API_KEY_TABLE.' WHERE apikey='.db_input($key);
        if($ip)
            $sql.=' AND ipaddr='.db_input($ip);
        
        if(($res=db_query($sql)) && db_num_rows($res))
            list($id) = db_fetch_row($res);

        return $id;
    }

    function lookupByKey($key, $ip='') {
        return self::lookup(self::getIdByKey($key, $ip));
    }

    function lookup($id) {
        return ($id && is_numeric($id) && ($k= new API($id)) && $k->getId()==$id)?$k:null;
    }

    function save($id, $vars, &$errors) {

        if(!$id && (!$vars['ipaddr'] || !Validator::is_ip($vars['ipaddr'])))
            $errors['ipaddr'] = _('Valid IP required');
        
        if($errors) return false;

        $sql=' updated=NOW() '
            .',isactive='.db_input($vars['isactive'])
            .',can_create_tickets='.db_input($vars['can_create_tickets'])
            .',notes='.db_input($vars['notes']);

        if($id) {
            $sql='UPDATE '.API_KEY_TABLE.' SET '.$sql.' WHERE id='.db_input($id);
            if(db_query($sql))
                return true;

            $errors['err']=_('Unable to update API key. Internal error occurred');

        } else {
            $sql='INSERT INTO '.API_KEY_TABLE.' SET '.$sql
                .',created=NOW() '
                .',ipaddr='.db_input($vars['ipaddr'])
                .',apikey='.db_input(strtoupper(md5(time().$vars['ipaddr'].md5(Misc::randcode(16)))));

            if(db_query($sql) && ($id=db_insert_id()))
                return $id;

            $errors['err']=_('Unable to add API key. Try again!');
        }

        return false;
    }
}

/**
 * Controller for API methods. Provides methods to check to make sure the
 * API key was sent and that the Client-IP and API-Key have been registered
 * in the database, and methods for parsing and validating data sent in the
 * API request.
 */
class ApiController {

    function requireApiKey() {
        # Validate the API key -- required to be sent via the X-API-Key
        # header
        if (!isset($_SERVER['HTTP_X_API_KEY']) || !isset($_SERVER['REMOTE_ADDR']))
            Http::response(403, "API key required");
        elseif (!($key=API::lookupByKey($_SERVER['HTTP_X_API_KEY'], $_SERVER['REMOTE_ADDR']))
                || !$key->isActive() 
                || $key->getIPAddr()!=$_SERVER['REMOTE_ADDR'])
            Http::response(401, "API key not found/active or source IP not authorized");

        return $key;
    }

    function getApiKey() {
        return $this->requireApiKey();
    }

    /**
     * Retrieves the body of the API request and converts it to a common
     * hashtable. For JSON formats, this is mostly a noop, the conversion
     * work will be done for XML requests
     */
    function getRequest($format) {
        if (!($stream = @fopen("php://input", "r")))
            Http::response(400, "Unable to read request body");
        if ($format == "xml") {
            if (!function_exists("xml_parser_create"))
                Http::response(500, "XML extension not supported");
            $tree = new ApiXmlDataParser();
        } elseif ($format == "json") {
            $tree = new ApiJsonDataParser();
        }
        if (!($data = $tree->parse($stream)))
            Http::response(400, $tree->lastError());
        $this->validate($data, $this->getRequestStructure($format));
        return $data;
    }
    /**
     * Structure to validate the request against -- must be overridden to be
     * useful
     */
    function getRequestStructure($format) { return array(); }
    /**
     * Simple validation that makes sure the keys of a parsed request are
     * expected. It is assumed that the functions actually implementing the
     * API will further validate the contents of the request
     */
    function validate($data, $structure, $prefix="") {
        foreach ($data as $key=>$info) {
            if (is_array($structure) and is_array($info)) {
                $search = (isset($structure[$key]) && !is_numeric($key)) ? $key : "*"; 
                if (isset($structure[$search])) {
                    $this->validate($info, $structure[$search], "$prefix$key/");
                    continue;
                }
            } elseif (in_array($key, $structure)) {
                continue;
            }
            Http::response(400, "$prefix$key: Unexpected data received");
        }
    }
}

include_once "class.xml.php";
class ApiXmlDataParser extends XmlDataParser {

    function parse($stream) {
        return $this->fixup(parent::parse($stream));
    }
    /**
     * Perform simple operations to make data consistent between JSON and
     * XML data types
     */
    function fixup($current) {
        if (!is_array($current))
            return $current;
        foreach ($current as $key=>&$value) {
            if ($key == "phone") {
                $current["phone_ext"] = $value["ext"];  # PHP [like] point
                $value = $value[":text"];
            } else if ($key == "alert") {
                $value = (bool)$value;
            } else if ($key == "autorespond") {
                $value = (bool)$value;
            } else if ($key == "attachments") {
                if(!isset($value['file'][':text']))
                    $value = $value['file'];

                if($value && is_array($value)) {
                    foreach ($value as &$info) { 
                        $info["data"] = $info[":text"]; 
                        unset($info[":text"]);
                    }
                    unset($info);
                }
            } else if(is_array($value)) {
                $value = $this->fixup($value);
            }
        }

        return $current;
    }
}

include_once "class.json.php";
class ApiJsonDataParser extends JsonDataParser {
    function parse($stream) {
        return $this->fixup(parent::parse($stream));
    }
    function fixup($current) {
        if (!is_array($current))
            return $current;
        foreach ($current as $key=>&$value) {
            if ($key == "phone") {
                list($value, $current["phone_ext"])
                    = explode("X", strtoupper($value), 2); 
            } else if ($key == "alert") {
                $value = (bool)$value;
            } else if ($key == "autorespond") {
                $value = (bool)$value;
            } else if ($key == "attachments") {
                foreach ($value as &$info) {
                    $data = reset($info);
                    # PHP5: fopen("data://$data[5:]");
                    if (substr($data, 0, 5) != "data:") {
                        $info = array(
                            "data" => $data,
                            "type" => "text/plain",
                            "name" => key($info));
                    } else {
                        $data = substr($data,5);
                        list($meta, $contents) = explode(",", $data);
                        list($type, $extra) = explode(";", $meta);
                        $info = array(
                            "data" => $contents,
                            "type" => ($type) ? $type : "text/plain",
                            "name" => key($info));
                        if (substr($extra, -6) == "base64")
                            $info["encoding"] = "base64";
                        # Handle 'charset' hint in $extra, such as
                        # data:text/plain;charset=iso-8859-1,Blah
                        # Convert to utf-8 since it's the encoding scheme
                        # for the database. Otherwise, assume utf-8
                        list($param,$charset) = explode('=', $extra);
                        if ($param == 'charset' && function_exists('iconv'))
                            $contents = iconv($charset, "UTF-8", $contents);
                    }
                }
                unset($value);
            } 
            if (is_array($value)) {
                $value = $this->fixup($value);
            }
        }
        return $current;
    }
}

?>
