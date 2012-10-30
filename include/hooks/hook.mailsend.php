<?php 

EventListener::get()->registerEvent(Event::PRE_MAIL_SEND, 1, function($event, &$mime) {
	global $cfg, $ost;

});
