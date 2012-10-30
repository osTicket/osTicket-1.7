<?php 

EventListener::get()->registerEvent(Event::PRE_CREATE_TICKET, 99, function($event, &$vars) {
	global $cfg, $ost;

});
