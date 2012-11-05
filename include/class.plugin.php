<?php 
/*********************************************************************
    class.plugin.php

    Alban Seurat <alkpone@alkpone.com>
    http://www.albanseurat.com/

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/

require(INCLUDE_DIR . "class.event.php");

abstract class Plugin 
{
	const PLUGINS_DIR = "plugins/";

	private $variables;

	// temporary way to customize plugin, use a real frontend with mysql backing asap
	public function __construct() 
	{
		$this->variables = parse_ini_file(INCLUDE_DIR.self::PLUGINS_DIR.$this->getName().".ini");
	}

	public function getParameter($name) 
	{
		return $this->variables[$name];
	}

	public abstract function getName();

	public abstract function load();

	/* static */ public function loadPlugins()
	{
		global $ost, $cfg;
		$hookDir = INCLUDE_DIR.self::PLUGINS_DIR;
		if(file_exists($hookDir) && is_dir($hookDir)) 
		{
			foreach(new DirectoryIterator($hookDir) as $file) 
			{
				try 
				{
					if(preg_match("/^plugin\.([^.]*)\.php$/i", $file->getFileName(), $matches)) 
					{
						include_once($file->getRealPath());
						$className = $matches[1]."Plugin";
						$plugin = new $className();
						$plugin->load();
					}
				}
				catch(Exception $e) 
				{
					$ost->logError("LoadPlugins", $e->getMessage());
				}
			}
		}
	}
}