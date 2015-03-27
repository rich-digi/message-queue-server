<?php

class Location
{
	var $location;

	CONST LIVE_SERVER = 'mqs.dmbizclub.com';
	CONST DEV_SERVER  = 'mqs.dev.dmbizclub.com';
	CONST RICH_K_DEV  = 'mqs.loc';
	
	public static function get_location()
	{
		$location = NULL;
		if (isset($_SERVER['HTTP_HOST']))
		{
			$host = strtolower($_SERVER['HTTP_HOST']);
		}
		else
		{
			exec('uname -n', $output);
			$host = strtolower($output[0]);
		}
		
		if (strpos($host, self::LIVE_SERVER) !== FALSE) $location = 'LIVE';
		if (strpos($host, self::DEV_SERVER)  !== FALSE) $location = 'DEV';
		if (strpos($host, self::RICH_K_DEV ) !== FALSE) $location = 'RICHKMAC';
		
		return $location;
	}
}

