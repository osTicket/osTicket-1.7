<?php


class HighPriorityPlugin extends Plugin
{
	public function postCreateTicket($event, &$vars)
	{
		global $cfg, $ost;

		$regex = "/".$this->getParameter("regex")."/i";
		if(preg_match($regex, $vars["message"]) || preg_match($regex, $vars["subject"]))
		{
			$ticket = $event->getSource();
			$ticket->setPriority(4);
		}
	}

	public function getName()
	{
		return "highpriority";
	}

	public function load() 
	{
		EventListener::get()->registerEvent(Event::POST_CREATE_TICKET, 5, array($this, "postCreateTicket"));
	}
}

