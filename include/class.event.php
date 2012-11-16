<?php 
/*********************************************************************
    class.email.php

    Alban Seurat <alkpone@alkpone.com>
    http://www.albanseurat.com/

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/

class Event 
{
	const PRE_CREATE_TICKET = 1;
	const POST_CREATE_TICKET = 2;
	const PRE_MAIL_SEND = 3;

	private $source;
	private $eventHandler;
	private $eventType;

	public function __construct($eventType, $mixed) {
		$this->eventType = $eventType;
		$this->source = $mixed;
	}

	public function setEventHandler(EventHandler $eventHandler)
	{
		$this->eventHandler = $eventHandler;
	}

	public function stopPropagation() 
	{
		$this->eventHandler->stopPropagation();
	}

	public function getEventType()
	{
		return $this->eventType;
	}

	public function getSource()
	{
		return $this->source;
	}
}

class EventHandler {

	private $stopPropagation;
	public $priority;

	public function __construct($function, $priority)
	{
		$this->priority = $priority;
		$this->stopPropagation = false;
		if($function instanceof Closure || function_exists($function)) {
			$this->function = $function;
		}
	}

	public function apply(Event $event, &$vars) 
	{
		if($event && $this->function) {
			$event->setEventHandler($this);
			call_user_func($this->function, $event, $vars);
		}
	}

	public function stopPropagation()
	{
		$this->stopPropagation = true;
	}

	public function isPropagationStopped() 
	{
		return $this->stopPropagation;
	}
}

class EventListener 
{
	const HOOK_DIR = "hooks";

	private $eventHandlers = array();
	private static $eventListener;

	public function registerEvent($eventType, $priority, $function)
	{
		$this->eventHandlers[$eventType][] = new EventHandler($function, $priority);
		usort($this->eventHandlers[$eventType], function($a, $b) { return $a->priority - $b->priority; });
	}

	public function apply(Event $event, &$vars) 
	{
		$eventType = $event->getEventType();
		if(isset($this->eventHandlers[$eventType])) 
		{
			foreach($this->eventHandlers[$eventType] as $eventHandler) {
				$eventHandler->apply($event, $vars); 
				if($eventHandler->isPropagationStopped()) 
				{
					break;
				}
			}
		}
	}

	/* static */ public function get() 
	{
		if(!isset(self::$eventListener))
			self::$eventListener = new EventListener();
		return self::$eventListener;
	}

	/* static */ public function loadHooks()
	{
		$hookDir = INCLUDE_DIR.self::HOOK_DIR;
		if(file_exists($hookDir) && is_dir($hookDir)) 
		{
			foreach(new DirectoryIterator($hookDir) as $file) {
				if(preg_match("/^hook\.[^.]*\.php$/i", $file->getFileName())) {
					include_once($file->getRealPath());
				}
			}
		}

	}
}
