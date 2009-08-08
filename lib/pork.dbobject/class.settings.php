<?php

/**
 * Simple Settings wrapper Class. 
 * Comes with static load function so that you can load multiple settings files just one time.
 * Settings::Load()->Get("Database");
 * 
 * @package Pork 
 * @author Jelle Ursem
 * @copyright Jelle Ursem 2009
 * @version 1.0
 * @access public
 */
class Settings {
  
  private $settingsfile, $settings;

  
	/**
	  * Constructor.
	  * Loads the settings by parsing the ini file passed in the constructor parameter.
	  * @param string $settingsfile
	  */
	function __construct($settingsfile) 
	  {
		$this->settingsfile = $settingsfile;
		$this->settings = parse_ini_file($this->settingsfile, true);
	
	  }


	/**
	 * Settings::Load
	 *
	 * Singleton functionality that creates one instance per loaded settings file so that ini parsing needs to happen only once.
	 * @param string $settingsfile file to read for settings
	 */
	public static function Load($settingsfile= './settings/settings.ini')
	{
		static $instances = array();
		if(!array_key_exists($settingsfile, $instances)) {
			$instances[$settingsfile] = new Settings($settingsfile);
		}
		return($instances[$settingsfile]);
	}
  
	/**
	 * Settings::Get
	 * 
	 * Gets an array of parameters for a key of settings file or gets one specific setting under a key.
	 * @param string $param 
	 */
	function Get($section, $subsection = false)
	{
		if($this->settings === false) die("error reading settings file.");
		return ($subsection) ? $this->settings[$section][$subsection] : $this->settings[$section];
	}
  

  
}



?>