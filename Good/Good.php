<?php

class Good
{
	private $installed;
	private $path = dir(__FILE__);
	private $modules;
	
	function __construct($config = 'config/Good.php')
	{
		if (!file_exists($this->path . $config))
		{
			$this->installed = false;
		}
		else
		{
			require $this->path . $config;
			$this->installed = $GoodInstalled;
		}
		
		if (!$this->installed)
		{
			require 'notInstalled.html';
			die;
		}
		
		require $this->path . 'modules.php';
		
		$this->modules = $modules;
	}
	
	function module($moduleId)
	{
		if (isset($this->modules[$module]))
		{
			$config = $this->modules[$moduleId]['config'];	
			require $this->path . 'objects/' . $this->modules[$moduleId]['moduleName'] .'.php';
			
			return $moduleObject;
		}
		else
		{
			require 'uninstalledModule.html';
			die();
		}
	}
		
}

?>