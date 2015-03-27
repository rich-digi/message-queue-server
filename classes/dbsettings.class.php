<?php

include_once('location.class.php'); // Defines available locations

class DBsettings
{
	public $host;
	public $sock;
	public $db;
	public $user;
	public $pass;

	public function __construct()
	{
		$this->sock	= NULL; // We don't usually connect through a socket, but can by setting this below

		$location = Location::get_location();
		switch($location)
		{
			case 'LIVE':
				/*
				/ -----------------------------------------------------------------------------
				/ IMPORTANT: DO NOT CHANGE THE DATABASE SETTINGS BELOW, USED BY THE 'LIVE' SITE
				/ These are used to connect to the 'live' version of the database.
				/ Changing them will wreak havoc on the live system.
				/ -----------------------------------------------------------------------------
				*/
				$this->host	= 'localhost';
				$this->db	= 'mqs';
				$this->user	= 'optrago77go';
				$this->pass	= 'go77go77go77';
				break;
	
			case 'DEV':
				$this->host	= 'localhost';
				$this->db	= 'mqs_dev';
				$this->user	= 'mqs';
				$this->pass	= 'talktalkedenspirit';
				break;
	
			// ---------------------------
			// Developers' local copies...

			case 'RICHKMAC':
				$this->host	= 'localhost';
				$this->sock = 'Applications/MAMP/tmp/mysql/mysql.sock';
				$this->db	= 'mqs_mamp';
				$this->user	= 'root';
				$this->pass	= 'root88';
				break;
		}
	}

}

