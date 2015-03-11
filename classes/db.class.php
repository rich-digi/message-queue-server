<?php

// db.class.php
// -------------------------------------------------------------------------------------------------
// DATABASE CLASS
// Class for reading from and writing to the database!
// -------------------------------------------------------------------------------------------------

if (!isset($docroot))
{
	$docroot = $_SERVER['DOCUMENT_ROOT'];
	$docroot = substr($docroot, -1) == '/' ? $docroot : $docroot.'/';
}

include_once($docroot.'classes/dbsettings.class.php');
include_once($docroot.'classes/debug.class.php');

class DB
{
	// Character set
	const CHARSET = 'utf8';

	// For emailing errors
	const OUTPUTERRORS	= FALSE;
	const EMAILERRORS	= FALSE;
	const MAILFROM		= 'MQS DB Error Handler <donotreply@slm.loc>'; 
	const MAILSUBJECT	= 'MQS DB Error Report - Database query failed'; 

	// Class variables
	public $fields;						// Fields to insert
	public $res;						// Result, representing the row of a single table
	public $protect_suspend;			// Temporarily suspend logged in checks (read or write)
	public $logged_in_session_var; 		// A variable in $_SESSION['USER'] that should be TRUE or FALSE

	protected $db_settings;				// Database connections settings object
	protected $table;					// Table
	protected $autid;					// Auto-increment id field
	
	protected $protect_read;			// Let only logged in users read from the database
	protected $protect_write;			// Let only logged in users wtite to the database
	
	private $mailto	= array('rich@apewave.com'); // For emailing errors

	// -------------------------------------------------------------------------------------------------
	// Constructor

	public function __construct($db_settings = NULL, $debugeron = FALSE)
	{
		// $db_settings can be an object containing host, user, pass, and db
		// If not passed, use the db.settings class

		$this->fields = new stdClass;

		$this->debug = new Debug($debugeron);
		
		if ($db_settings)
		{
			$this->db_settings = $db_settings;
		}
		else
		{
			$this->db_settings = new DBsettings;
		}
		if ($this->db_settings->sock) $this->db_settings->host = NULL; // Use host OR socket

		$this->debug->inspect('Attempting to connect', $this->db_settings);
		$this->mysqli = new mysqli($this->db_settings->host, $this->db_settings->user, $this->db_settings->pass, $this->db_settings->db);
		if (mysqli_connect_errno())
		{
			$this->debug->err('ERROR: '.mysqli_connect_error());
			$this->handle_errors(mysqli_connect_error());
			return FALSE;
		}
		
		if (method_exists($this->mysqli, 'set_charset'))
		{
			$this->mysqli->set_charset(self::CHARSET);
		}
		else
		{
			$this->mysqli->query('SET NAMES "' . self::CHARSET . '"');
		}
		
		$this->debug->msg('Established connection to database: ' . $this->db_settings->db);
		return TRUE;
	}

	// -------------------------------------------------------------------------------------------------
	// Run a query against the database, best for inserts and the like

	public function query($query)
	{
		$this->debug->heading(__FUNCTION__);

		if (is_string($query))
		{
			$this->debug->msg($query);
			$result = $this->mysqli->query($query);
			$this->handle_errors($query);
			return $result;
		}
		
		// Assume query is an array, and treat as a multi_query
		$this->debug->inspect(NULL, $query);
		foreach($query as &$q) $q = str_replace(';', '\;', $q);
		$query = implode(';', $query);
		$result = $this->mysqli->multi_query($query);
		$this->handle_errors($query);
		return $result;
	}

	// -------------------------------------------------------------------------------------------------
	// Runs a query and returns the result set as an array

	public function query_2_array($query, $type = MYSQL_ASSOC)
	{
		$this->debug->heading(__FUNCTION__);
		$this->debug->msg($query);

		$result = array();
		$myqres = $this->mysqli->query($query);	
		if ($myqres)
		{
			while ($row = $myqres->fetch_array($type)) $result[] = $row;
			$myqres->free();
		}
		$this->handle_errors($query);
		$this->debug->inspect('First few results...', array_slice($result, 0, 5));
		return $result;		
	}
	
	// -------------------------------------------------------------------------------------------------
	// Runs a query and returns the result as an object
	
	public function query_2_object($query)
	{
		$this->debug->heading(__FUNCTION__);
		$this->debug->msg($query);

		$result = array();
		$myqres = $this->mysqli->query($query);	   
		while ($row = $myqres->fetch_object()) $result[] = $row;
		$myqres->free();
		$this->handle_errors($query);
		// if (count($result) === 1) $result = $result[0];
		$this->debug->inspect('First few results...', array_slice($result, 0, 5));
		return $result;		
	}
	
	// -------------------------------------------------------------------------------------------------
	// Count number of rows query returned ( SELECT )

	public function count_rows($query)
	{
		$this->debug->heading(__FUNCTION__);
		$this->debug->msg($query);

		$myqres = $this->mysqli->query($query);
		$rows = $myqres->num_rows;
		$myqres->free();
		$this->handle_errors($query);
		$this->debug->msg($rows . ($rows == 1 ? ' row' : ' rows'));
		return $rows;
	}

	// -------------------------------------------------------------------------------------------------
	// Count affected rows (UPDATE / DELETE )

	public function affected_rows()
	{
		$this->debug->heading(__FUNCTION__.' = '.$mysqli->affected_rows);
		return $mysqli->affected_rows;
	}

	// ----------------------------------------------------------------------------------
	// Get the ID of the item added in the last run query

	public function get_insert_id()
	{
		$this->debug->heading(__FUNCTION__.' = '.$this->mysqli->insert_id);
		return $this->mysqli->insert_id;
	}

	// -------------------------------------------------------------------------------------------------
	// Get information about the fields in the table

	public function fields_2_array($table = NULL)
	{
		$this->debug->heading(__FUNCTION__ . '(' . $table . ')');

		if (!$table) $table = $this->$table;
		$result = array();
		$myqres = $this->mysqli->query('SHOW COLUMNS FROM ' . $table);
		$result = array();
		while ($row = $myqres->fetch_array())
		{
			$result[] = array('name' => $row['Field'], 'type' => $row['Type'], 'default' => $row['Default']);
		}
		$this->debug->inspect('Fields', $result);
		return(count($result) ? $result : FALSE);
	}

	// ---------------------------------------------------------------------------------------------
	// Get information about the fields in the table

	public function fields_2_object($table = NULL)
	{
		$this->debug->heading(__FUNCTION__ . '(' . $table . ')');

		if (!$table) $table = $this->table;
		$result = array();
		$myqres = $this->mysqli->query('SHOW COLUMNS FROM ' . $table);
		$result = array();
		while ($row = $myqres->fetch_object())
		{
			$result[] = (object) array('name' => $row->Field, 'type' => $row->Type, 'default' => $row->Default);
		}
		$this->debug->inspect('Fields', $result);
		return(count($result) ? $result : FALSE);
	}

	// ---------------------------------------------------------------------------------------------
	// SINGLE TABLE CONVENIENCE METHODS

	public function set_table($table)
	{
		$this->debug->heading(__FUNCTION__ . '(' . $table . ')');

		$this->table = $table;
		$res = $this->fields_2_object();
		$this->autid = $res[0]->name;
	}
	
	// ---------------------------------------------------------------------------------------------

	public function load($id)
	{
		$this->debug->heading(__FUNCTION__ . '(' . $id . ')');

		$this->db_read_check();
		$id = (int) $id; // Anti-hack attack
		$sql = 'SELECT * FROM ' . $this->table . ' WHERE ' . $this->autid . '=' . $id;
		$res = $this->query_2_object($sql);
		if (empty($res)) return FALSE;
		$this->res = $res[0];
		$this->{$this->autid} = $this->res->{$this->autid};
		return $res[0];
	}

	// ---------------------------------------------------------------------------------------------

	public function save($id = NULL)
	{
		$this->db_write_check();
		if (!$id)
		{
			if (isset($this->{$this->autid})) $id = $this->{$this->autid};
			if (isset($this->fields->{$this->autid})) $id = $this->fileds->{$this->autid};
		}
		$sql = 'SELECT ' . $this->autid . ' FROM ' . $this->table . '
				WHERE  ' . $this->autid . ' = ' . (int) $this->{$this->autid};
		$rows = $this->count_rows($sql); 
		return($rows ? $this->update() : $this->add()); 
	}

	// -------------------------------------------------------------------------------------------------
	// DATA SANITIZATION
	
	// -------------------------------------------------------------------------------------------------
	// Adds required slashes to strings for DB entry

	public function e($str)
	{
		return $this->mysqli->real_escape_string($str);
	}

	// -------------------------------------------------------------------------------------------------
	// Makes sure a number is a number

	public function cleannum($num)
	{
		return(is_numeric($num) ? $num : 'NULL');
	}

	// -------------------------------------------------------------------------------------------------
	// Cleans booleans

	public function cleanbool($bool)
	{
		return($bool ? 1 : 0);
	}

	// ---------------------------------------------------------------------------------------------
	// ---------------------------------------------------------------------------------------------
	// ---------------------------------------------------------------------------------------------
	// PRIVATE METHODS

	// ---------------------------------------------------------------------------------------------
	// Support for 'save' single table convenience methods

	private function add()
	{
		$this->db_write_check();
		unset($this->fields->{$this->autid}); // Safety feature
		$fields = implode(', ', array_keys((array) $this->fields));
		$values = '';
		foreach($this->fields as $value) $values .= '"' . $this->mysqli->real_escape_string($value) . '", ';
		$values = substr($values, 0, -2);
		$sql = 'INSERT INTO ' . $this->table . ' (' . $fields . ') VALUES (' . $values . ')';
		$res = $this->query($sql);
		if (!$res) return FALSE;
		$this->extract_into_vars($this->fields); // Keep object up to date
		$this->{$this->autid} = $this->db->get_insert_id();
		return($this->{$this->autid});
	}
	
	// ---------------------------------------------------------------------------------------------

	private function update()
	{
		$this->db_write_check();
		unset($this->fields->{$this->autid}); 					// Safety feature
		$this->{$this->autid} = (int) $this->{$this->autid};	// Force integer
		$assignments = '';
		foreach($this->fields as $field => $value) $assignments .= $field . '="' . $this->mysqli->real_escape_string($value) . '", ';
		$assignments = substr($assignments, 0, -2);
		$sql = 'UPDATE ' . $this->table . ' SET ' . $assignments . ' WHERE ' . $this->autid . ' = ' . $this->{$this->autid};
		$res = $this->query($sql);
		if (!$res) return FALSE;
		$this->extract_into_vars($this->fields); // Keep object up to date
		return($this->{$this->autid});
	}
	
	// ---------------------------------------------------------------------------------------------

	private function db_read_check()
	{
		if (!$this->protect_read) return;
		if (isset($_SESSION[$this->logged_in_session_var]) && $_SESSION[$this->logged_in_session_var]) return;
		if ($this->protect_suspend) return;
		exit;
	}

	// ---------------------------------------------------------------------------------------------

	private function db_write_check()
	{
		if (!$this->protect_write) return;
		if (isset($_SESSION[$this->logged_in_session_var]) && $_SESSION[$this->logged_in_session_var]) return;
		if ($this->protect_suspend) return;
		exit;
	}

	// ---------------------------------------------------------------------------------------------
	// Support for Single table convenience methods: load or nullify result vars
	
	private function extract_into_vars($result)
	{
		if (!is_object($result) && !is_array($result)) return FALSE;
		foreach($result as $name => $value) $this->res->{$name} = $value;
		return TRUE;
	}

	// ---------------------------------------------------------------------------------------------

	private function nullify_extracted_vars()
	{
		$res = $this->fields_as_object($this->table);
		foreach($res as $field) $this->res->{$field->name} = NULL;
	}
	
	// ---------------------------------------------------------------------------------------------
	// Error handling functions

	private function handle_errors($extra = '')
	{
		$error = @$this->mysqli->error;
		$this->debug->err($error);
		if ($error)
		{
			$errortext = 'There has been a SQL error<br /><strong>Error Text:</strong> ' . $error;
			$errortext .= $extra ?  '<br />'.$extra : '';
			$this->debug->err($errortext);
			if (self::EMAILERRORS) $this->mail_error($errortext);
			if (self::OUTPUTERRORS) echo('<br /><br />' . $errortext . '<br /><br />');
		}
	}
	
	// ---------------------------------------------------------------------------------------------

	private function mail_error($mail_body)
	{ 
		$headers	 =	'From: ' . self::MAILFROM . "\r\n"; 
		$headers	.=	'MIME-Version: 1.0' . "\r\n"; 
		$headers	.=	'Content-type: text/html; charset=iso-8859-1' . "\r\n"; 
		$body		 =	'IP='.$_SERVER['REMOTE_ADDR'] . '<br />' . $mail_body; 
		foreach($this->mailto as $recipient)
		{
			mail( $recipient, (self::MAILSUBJECT . ' - page: ' . (isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : '')), $body, $headers );
		}
	}

	// -------------------------------------------------------------------------------------------------

}
