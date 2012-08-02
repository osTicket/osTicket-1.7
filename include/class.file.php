<?php
/*********************************************************************
    class.file.php

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2012 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/

class AttachmentFile {

    var $id;
    var $ht;

    function AttachmentFile($id) {
        $this->id =0;
        return ($this->load($id));
    }

    function load($id=0) {

        if(!$id && !($id=$this->getId()))
            return false;

        $sql='SELECT f.*, count(DISTINCT c.canned_id) as canned, count(DISTINCT t.ticket_id) as tickets '
            .' FROM '.FILE_TABLE.' f '
            .' LEFT JOIN '.CANNED_ATTACHMENT_TABLE.' c ON(c.file_id=f.id) '
            .' LEFT JOIN '.TICKET_ATTACHMENT_TABLE.' t ON(t.file_id=f.id) '
            .' WHERE f.id='.db_input($id)
            .' GROUP BY f.id';
        if(!($res=db_query($sql)) || !db_num_rows($res))
            return false;

        $this->ht=db_fetch_array($res);
        $this->id =$this->ht['id'];

        return true;
    }

    function reload() {
        return $this->load();
    }

    function getHashtable() {
        return $this->ht;
    }

    function getInfo() {
        return $this->getHashtable();
    }

    function getNumTickets() {
        return $this->ht['tickets'];
    }

    function isCanned() {
        return ($this->ht['canned']);
    }

    function isInUse() {
        return ($this->getNumTickets() || $this->isCanned());
    }

    function getId() {
        return $this->id;
    }

    function getType() {
        return $this->ht['type'];
    }

    function getMime() {
        return $this->getType();
    }

    function getSize() {
        return $this->ht['size'];
    }

    function getName() {
        return $this->ht['name'];
    }

    function getHash() {
        return $this->ht['hash'];
    }

    function getBinary() {
        return $this->ht['filedata'];
    }

    function getData() {
        return $this->getBinary();
    }

    function delete() {

        $sql='DELETE FROM '.FILE_TABLE.' WHERE id='.db_input($this->getId()).' LIMIT 1';
        return (db_query($sql) && db_affected_rows());
    }


    function display() {
       
         //strangely, putting this directly in the header line results in apache forcing a text/html type
        $mime=$this->getType() ? $this->getType() : $this->getMimeType();
        header('Content-Type: '.$mime);
        header('Content-Length: '.$this->getSize());
        echo $this->getData();
        exit();
    }

    function download() {

        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Cache-Control: public');
        //header('Content-Type: application/octet-stream');

         //strangely, putting this directly in the header line results in apache forcing a text/html type
        $mime=$this->getType() ? $this->getType() : $this->getMimeType();
        header('Content-Type: '.$mime);
        
        $filename=basename($this->getName());
        $user_agent = strtolower ($_SERVER['HTTP_USER_AGENT']);
        if ((is_integer(strpos($user_agent,'msie'))) && (is_integer(strpos($user_agent,'win')))) {
            header('Content-Disposition: filename='.$filename.';');
        }else{
            header('Content-Disposition: attachment; filename='.$filename.';' );
        }
        
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: '.$this->getSize());
        echo $this->getBinary();
        exit();
    }


    // return the Mime type header, based on the file extension
    function getMimeType($filename=''){
     	$filename or $filename=$this->getName();
   		$mimetypes  = array( 
            'ez'    => 'application/andrew-inset' ,
            'hqx'   => 'application/mac-binhex40' ,
            'cpt'   => 'application/mac-compactpro' ,
            'doc'   => 'application/msword' ,
            'bin'   => 'application/octet-stream' ,
            'dms'   => 'application/octet-stream' ,
            'lha'   => 'application/octet-stream' ,
            'lzh'   => 'application/octet-stream' ,
            'exe'   => 'application/octet-stream' ,
            'class' => 'application/octet-stream' ,
            'so'    => 'application/octet-stream' ,
            'dll'   => 'application/octet-stream' ,
            'oda'   => 'application/oda' ,
            'pdf'   => 'application/pdf' ,
            'ai'    => 'application/postscript' ,
            'eps'   => 'application/postscript' ,
            'ps'    => 'application/postscript' ,
        //  'smi'   => 'application/smil' ,
            'smil'  => 'application/smil' ,
            'mif'   => 'application/vnd.mif' ,
            'xls'   => 'application/vnd.ms-excel' ,
            'ppt'   => 'application/vnd.ms-powerpoint' ,
            'wbxml' => 'application/vnd.wap.wbxml' ,
            'wmlc'  => 'application/vnd.wap.wmlc' ,
            'wmlsc' => 'application/vnd.wap.wmlscriptc' ,
            'bcpio' => 'application/x-bcpio' ,
            'vcd'   => 'application/x-cdlink' ,
            'pgn'   => 'application/x-chess-pgn' ,
            'cpio'  => 'application/x-cpio' ,
            'csh'   => 'application/x-csh' ,
            'dcr'   => 'application/x-director' ,
            'dir'   => 'application/x-director' ,
            'dxr'   => 'application/x-director' ,
            'dvi'   => 'application/x-dvi' ,
            'spl'   => 'application/x-futuresplash' ,
            'gtar'  => 'application/x-gtar' ,
            'hdf'   => 'application/x-hdf' ,
            'js'    => 'application/x-javascript' ,
            'skp'   => 'application/x-koan' ,
            'skd'   => 'application/x-koan' ,
            'skt'   => 'application/x-koan' ,
            'skm'   => 'application/x-koan' ,
            'latex' => 'application/x-latex' ,
            'nc'    => 'application/x-netcdf' ,
            'cdf'   => 'application/x-netcdf' ,
            'sh'    => 'application/x-sh' ,
            'shar'  => 'application/x-shar' ,
            'swf'   => 'application/x-shockwave-flash' ,
            'sit'   => 'application/x-stuffit' ,
            'sv4cpio'   => 'application/x-sv4cpio' ,
            'sv4crc'    => 'application/x-sv4crc' ,
            'tar'   => 'application/x-tar' ,
            'tcl'   => 'application/x-tcl' ,
            'tex'   => 'application/x-tex' ,
            'texinfo'   => 'application/x-texinfo' ,
            'texi'  => 'application/x-texinfo' ,
            't'     => 'application/x-troff' ,
            'tr'    => 'application/x-troff' ,
            'roff'  => 'application/x-troff' ,
            'man'   => 'application/x-troff-man' ,
            'me'    => 'application/x-troff-me' ,
            'ms'    => 'application/x-troff-ms' ,
            'ustar' => 'application/x-ustar' ,
            'src'   => 'application/x-wais-source' ,
            'xhtml' => 'application/xhtml+xml' ,
            'xht'   => 'application/xhtml+xml' ,
            'zip'   => 'application/zip' ,
            'au'    => 'audio/basic' ,
            'snd'   => 'audio/basic' ,
            'mid'   => 'audio/midi' ,
            'midi'  => 'audio/midi' ,
            'kar'   => 'audio/midi' ,
            'mpga'  => 'audio/mpeg' ,
            'mp2'   => 'audio/mpeg' ,
            'mp3'   => 'audio/mpeg' ,
            'aif'   => 'audio/x-aiff' ,
            'aiff'  => 'audio/x-aiff' ,
            'aifc'  => 'audio/x-aiff' ,
            'm3u'   => 'audio/x-mpegurl' ,
            'ram'   => 'audio/x-pn-realaudio' ,
            'rm'    => 'audio/x-pn-realaudio' ,
            'rpm'   => 'audio/x-pn-realaudio-plugin' ,
            'ra'    => 'audio/x-realaudio' ,
            'wav'   => 'audio/x-wav' ,
            'pdb'   => 'chemical/x-pdb' ,
            'xyz'   => 'chemical/x-xyz' ,
            'bmp'   => 'image/bmp' ,
            'gif'   => 'image/gif' ,
            'ief'   => 'image/ief' ,
            'jpeg'  => 'image/jpeg' ,
            'jpg'   => 'image/jpeg' ,
            'jpe'   => 'image/jpeg' ,
            'png'   => 'image/png' ,
            'tiff'  => 'image/tiff' ,
            'tif'   => 'image/tiff' ,
            'djvu'  => 'image/vnd.djvu' ,
            'djv'   => 'image/vnd.djvu' ,
            'wbmp'  => 'image/vnd.wap.wbmp' ,
            'ras'   => 'image/x-cmu-raster' ,
            'pnm'   => 'image/x-portable-anymap' ,
            'pbm'   => 'image/x-portable-bitmap' ,
            'pgm'   => 'image/x-portable-graymap' ,
            'ppm'   => 'image/x-portable-pixmap' ,
            'rgb'   => 'image/x-rgb' ,
            'xbm'   => 'image/x-xbitmap' ,
            'xpm'   => 'image/x-xpixmap' ,
            'xwd'   => 'image/x-xwindowdump' ,
            'igs'   => 'model/iges' ,
            'iges'  => 'model/iges' ,
            'msh'   => 'model/mesh' ,
            'mesh'  => 'model/mesh' ,
            'silo'  => 'model/mesh' ,
            'wrl'   => 'model/vrml' ,
            'vrml'  => 'model/vrml' ,
            'css'   => 'text/css' ,
            'html'  => 'text/html' ,
            'htm'   => 'text/html' ,
            'asc'   => 'text/plain' ,
            'txt'   => 'text/plain' ,
            'rtx'   => 'text/richtext' ,
            'rtf'   => 'text/rtf' ,
            'sgml'  => 'text/sgml' ,
            'sgm'   => 'text/sgml' ,
            'tsv'   => 'text/tab-separated-values' ,
            'wml'   => 'text/vnd.wap.wml' ,
            'wmls'  => 'text/vnd.wap.wmlscript' ,
            'etx'   => 'text/x-setext' ,
            'xsl'   => 'text/xml' ,
            'xml'   => 'text/xml' ,
            'mpeg'  => 'video/mpeg' ,
            'mpg'   => 'video/mpeg' ,
            'mpe'   => 'video/mpeg' ,
            'qt'    => 'video/quicktime' ,
            'mov'   => 'video/quicktime' ,
            'mxu'   => 'video/vnd.mpegurl' ,
            'avi'   => 'video/x-msvideo' ,
            'movie' => 'video/x-sgi-movie' ,
            'ice'   => 'x-conference/x-cooltalk'
        ); 
    
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if( $ext && $mime=$mimetypes[$ext]) { 
             return $mime; 
        }
        else { 
             return "application/octet-stream"; 
        }
    }

    /* Function assumes the files types have been validated */
    function upload($file) {
        
        if(!$file['name'] || $file['error'] || !is_uploaded_file($file['tmp_name']))
            return false;

        $info=array('type'=> AttachmentFile::getMimeType($file['name']),
                    'size'=>$file['size'],
                    'name'=>$file['name'],
                    'hash'=>MD5(MD5_FILE($file['tmp_name']).time()),
                    'data'=>file_get_contents($file['tmp_name'])
                    );

        return AttachmentFile::save($info);
    }

    function save($file) {

        if(!$file['hash'])
            $file['hash']=MD5(MD5($file['data']).time());
        if(!$file['size'])
            $file['size']=strlen($file['data']);
        
        $sql='INSERT INTO '.FILE_TABLE.' SET created=NOW() '
            .',type='.db_input($file['type'])
            .',size='.db_input($file['size'])
            .',name='.db_input($file['name'])
            .',hash='.db_input($file['hash']);

        if (!(db_query($sql) && ($id=db_insert_id())))
            return false;

        foreach (str_split($file['data'], 1024*100) as $chunk) {
            $sql='UPDATE '.FILE_TABLE
                .' SET filedata = CONCAT(filedata,'.db_input($chunk).')'
                .' WHERE id='.db_input($id);
            if(!db_query($sql)) {
                db_query('DELETE FROM '.FILE_TABLE.' WHERE id='.db_input($id).' LIMIT 1');
                return false;
            }
        }

        return $id;
    }

    /* Static functions */
    function getIdByHash($hash) {

        $sql='SELECT id FROM '.FILE_TABLE.' WHERE hash='.db_input($hash);
        if(($res=db_query($sql)) && db_num_rows($res))
            list($id)=db_fetch_row($res);

        return $id;
    }

    function lookup($id) {

        $id = is_numeric($id)?$id:AttachmentFile::getIdByHash($id);
        
        return ($id && ($file = new AttachmentFile($id)) && $file->getId()==$id)?$file:null;
    }
    /**
     * Removes files and associated meta-data for files which no ticket,
     * canned-response, or faq point to any more.
     */
    /* static */ function deleteOrphans() {
        $res=db_query(
            'DELETE FROM '.FILE_TABLE.' WHERE id NOT IN ('
                # DISTINCT implies sort and may not be necessary
                .'SELECT DISTINCT(file_id) FROM ('
                    .'SELECT file_id FROM '.TICKET_ATTACHMENT_TABLE
                    .' UNION ALL '
                    .'SELECT file_id FROM '.CANNED_ATTACHMENT_TABLE
                    .' UNION ALL '
                    .'SELECT file_id FROM '.FAQ_ATTACHMENT_TABLE
                .') still_loved'
            .')');
        return db_affected_rows();
    }
}

class AttachmentList {
    function AttachmentList($table, $key) {
        $this->table = $table;
        $this->key = $key;
    }

    function all() {
        if (!isset($this->list)) {
            $this->list = array();
            $res=db_query('SELECT file_id FROM '.$this->table
                .' WHERE '.$this->key);
            while(list($id) = db_fetch_row($res)) {
                $this->list[] = new AttachmentFile($id);
            }
        }
        return $this->list;
    }
    
    function getCount() {
        return count($this->all());
    }

    function add($fileId) {
        db_query(
            'INSERT INTO '.$this->table
                .' SET '.$this->key
                .' file_id='.db_input($fileId));
    }

    function remove($fileId) {
        db_query(
            'DELETE FROM '.$this->table
                .' WHERE '.$this->key
                .' AND file_id='.db_input($fileId));
    }
}
?>
