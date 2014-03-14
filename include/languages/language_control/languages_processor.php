<?php

/*******************************************************************************
* languages_processor                                                                        *
*                                                                              *                                                             *
* Date:    2013-05-20                                                          *
* Author:  Luis Alfredo Dilone                                                    *
*******************************************************************************/


/**
 * Fetch a single line of a text from the language array
 *
 * @access  public
 * @param   string  $line   the language line
 * @return  string
 */
if (!function_exists('line')) {
    function line($line = '')
    {
        $default='';
        require(INCLUDE_DIR.'languages/default.php');
        if($default!='')
            require(INCLUDE_DIR.'languages/'.$default);
        else
            return FALSE;

        $value = ($line == '' || !isset($word[$line])) ? FALSE : $word[$line];

        return $value;
    }
}

/**
 *lang
 *
 * Fetches a language variable and optionally outputs a form label
 *
 * @access  public
 * @param   string  the language line
 * @param   bool  optional adding element if does not exist
 * @return  string
 */
if (!function_exists('lang')) {

    function lang($line, $addIfNotExists = TRUE) {
        $line_ = line($line);        
        return $line_ ? $line_ : ($addIfNotExists ? addLangIfNotExists($line) : '[' . $line . ']' );
    }
}

/**
 *addLangIfNotExists
 *
 * Insert the received in the parameter Key into a every language file
 *
 * @access  public
 * @param   string  the language line
 * @return  string
 */
if (!function_exists('addLangIfNotExists')) {

    function addLangIfNotExists($key) {

        $path=INCLUDE_DIR.'languages/';

        $dir = @opendir($path);
        while ($file = readdir($dir)){
            if( $file != "." && $file != ".." && $file != "default.php"){
                if(!is_dir($path.$file) )
                {
                    $humanize = $key;
                    $string = '$word[\'' . $key . '\']' . "\t\t" . '= \'' . $humanize . '\';';
                    $fo = fopen($path.'/'.$file, 'a');
                    fwrite($fo, PHP_EOL.$string);
                    fclose($fo);
                }
            }
        }
        return $key;
    }
}

/**
 *getAssignedLanguages
 *
 * Insert the received in the parameter Key into a every language file
 *
 * @access  public
 * @param   string  the language line
 * @return  string
 */
if (!function_exists('getAssignedLanguages')) {
    function getAssignedLanguages()
    {
        require(INCLUDE_DIR.'languages/language_control/languages_list.php');
        $path=INCLUDE_DIR.'languages/';
        $dir = opendir($path);
        $languagesAvailables=array();
        while ($file = readdir($dir)){
            if($file!='.' && $file!='..' && !is_dir($path.$file))
            {
                $key=explode('.',$file);
                if($language[$key[0]] !='')
                    $languagesAvailables[$key[0]]=$language[$key[0]];
            }
        }
        return $languagesAvailables;
    }
}

/**
 *getAllLanguages
 *
 * Look for all languages in the file languages_list.php
 *
 * @access  public
 * @return  array
 */
if (!function_exists('getAllLanguages')) {
    function getAllLanguages()
    {
        require(INCLUDE_DIR.'languages/language_control/languages_list.php');
        asort($language);
        return $language;
    }
}

/**
 *getDefaultLanguage
 *
 * Look for a var in  the file default.php and return its value
 *
 * @access  public
 * @return  string
 */
if (!function_exists('getDefaultLanguage')) {
    function getDefaultLanguage($remove_ext = false)
    {
        $default='';
        require(INCLUDE_DIR.'languages/default.php');
        if($remove_ext){
            $default = stristr($default, '.', TRUE);
        }
        return $default;
    }
}

/**
 *changeLaguage
 *
 * Change the var in  the file default.php
 *
 * @access  public
 * @return  bool 
 */
if (!function_exists('changeLaguage')) {
    function changeLaguage($language)
    {
        $file=INCLUDE_DIR.'languages/default.php';
        if(file_exists($file))
            unlink($file);
        else
            return FALSE;

        $fo = fopen($file,'a+');
        $string='<?php $default="'.$language.'.php" ?>';
        fwrite($fo,$string);
        fclose($fo);
        return TRUE;
    }
}

/**
 *editLanguage
 *
 * Look for the information in the language file specified on the parameter
 *
 * @param   string  the language requiered 
 * @access  public
 * @return  array 
 */
if (!function_exists('editLanguage')) {
    function editLanguage($language)
    {
        if($language!='')
        {
            $dataLanguage=array();
            require(INCLUDE_DIR.'languages/'.$language.'.php');
            foreach ($word as $key => $value) {
                $dataLanguage[$key]= $value;
            }
            return $dataLanguage;
        }
    }
}

/**
 *updateLanguage
 *
 * Create a new file with the data parameter on the file language specified on the parameter
 *
 * @param   string  the language it is been updated
 * @param   array   info that the file will contain
 * @access  public
 * @return  bool 
 */
if (!function_exists('updateLanguage')) {
    function updateLanguage($language,$data)
    {
        $file=INCLUDE_DIR.'languages/'.$language.'.php';
        if(file_exists($file))
            unlink($file);
        else
            return FALSE;

        $fo = fopen($file, 'a+');
        $info='<?php ';
        foreach ($data as $key => $value) {
            $info.=PHP_EOL.('$word[\'' . $key . '\']' . "\t\t" . '= \'' . str_replace('\'','&#39',$value) . '\';');
        }

        fwrite($fo, $info);
        fclose($fo);
        return TRUE;
    }
}

/**
 *createNewLanguage
 *
 * Create a new file language
 *
 * @param   string  the name of the file language
 * @access  public
 * @return  bool 
 */
if (!function_exists('createNewLanguage')) {
    function createNewLanguage($language)
    {
        return copy(INCLUDE_DIR.'languages/eng.php',INCLUDE_DIR.'languages/'.$language.'.php');

    }
}
