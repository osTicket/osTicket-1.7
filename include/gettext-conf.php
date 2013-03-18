<?php
//Multilanguage Support
//To add additional languages add a folder with your language code to 'include/locale' (for example 'de-de'), create the folder 'LC_MESSAGES' inside it and create your
//'messages.po' file inside 'LC_MESSAGES'. With the example of de-de the full path to 'messages.po' should look like 'include/locale/de-de/LC_MESSAGES/messages.po'.
//In case you don't know your language code (or to be more precise: the one your browser prefers), open the php page: 'testlang.php'
if(extension_loaded('gettext')==1)
{
	require_once(INCLUDE_DIR.'locale/lang.php');
	$language=getDefaultLanguage(); //if you want to use just one static language replace the call to getDefaultLanguage() with your language code (for example 'de-de')
	putenv('LC_ALL=' . $language);
	setlocale(LC_ALL, $language . '.UTF-8');
	bindtextdomain('messages', INCLUDE_DIR.'locale');
	textdomain('messages');
}
else
{
	function _($text){return $text;} //fallback definition: in case the gettext extension wasn't loaded osticket should at least work in english
}
?>