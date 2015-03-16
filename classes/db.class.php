<?php

// db.class.php
// -------------------------------------------------------------------------------------------------
// DATABASE CLASS
// Class for reading from and writing to the database!
// -------------------------------------------------------------------------------------------------

include_once('dbsettings.class.php');
include_once('debug.class.php');

class DB
{
	// Character set
	const CHARSET = 'utf8';

	// For emailing errors
	const OUTPUTERRORS	= FALSE;
	const EMAILERRORS	= FALSE;
	const MAILFROM		= 'MQS DB Error Handler <donotreply@mqs.loc>'; 
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
	
	private $docroot;
	private $mailto	= array('rich@apewave.com'); // For emailing errors

	// ---------------------------------------------------------------------------------------------
	// Constructor

	public function __construct($db_settings = NULL, $debugeron = FALSE)
	{
		// $db_settings can be an object containing host, user, pass, and db
		// If not passed, use the db.settings class

		$this->docroot = isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : '';
		$this->docroot = substr($this->docroot, -1) == '/' ? $this->docroot : $this->docroot.'/';

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
			$this->mysqli->query($query);
			$this->handle_errors($query);
			return $this->affected_rows();
		}
		
		// Assume query is an array, and treat as a multi_query
		$this->debug->inspect(NULL, $query);
		foreach($query as &$q) $q = str_replace(';', '\;', $q);
		$query = implode(';', $query);
		$this->mysqli->multi_query($query);
		$this->handle_errors($query);
		return $this->affected_rows();
	}

	// ---------------------------------------------------------------------------------------------
	// Runs a query and returns the result set as an array

	public function query_2_array($query, $type = MYSQL_ASSOC)
	{
		$this->debug->heading(__FUNCTION__);
		$this->debug->msg($query);

		$result = array();
		$myqres = $this->mysqli->query($query);	
		if ($myqres)
		{
			while($row = $myqres->fetch_array($type)) $result[] = $row;
			$myqres->free();
		}
		$this->handle_errors($query);
		$this->debug->inspect('First few results...', array_slice($result, 0, 5));
		return $result;		
	}
	
	// ---------------------------------------------------------------------------------------------
	// Runs a query and returns the result as an object
	
	public function query_2_object($query)
	{
		$this->debug->heading(__FUNCTION__);
		$this->debug->msg($query);

		$result = array();
		$myqres = $this->mysqli->query($query);	   
		while($row = $myqres->fetch_object()) $result[] = $row;
		$myqres->free();
		$this->handle_errors($query);
		$this->debug->inspect('First few results...', array_slice($result, 0, 5));
		return $result;		
	}
	
	// ---------------------------------------------------------------------------------------------
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

	// ---------------------------------------------------------------------------------------------
	// Count affected rows (UPDATE / DELETE )

	public function affected_rows()
	{
		$this->debug->heading(__FUNCTION__.' = '.$this->mysqli->affected_rows);
		return $this->mysqli->affected_rows;
	}

	// ---------------------------------------------------------------------------------------------
	// Get the ID of the item added in the last run query

	public function get_insert_id()
	{
		$this->debug->heading(__FUNCTION__.' = '.$this->mysqli->insert_id);
		return $this->mysqli->insert_id;
	}

	// ---------------------------------------------------------------------------------------------
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

	public function load($id, $table = NULL)
	{
		$this->debug->heading(__FUNCTION__ . '(' . $id . ')');

		$this->db_read_check();
			
		if ($table) $this->set_table($table);
		$id = (int) $id; // Anti-hack attack
		$sql = 'SELECT * FROM ' . $this->table . ' WHERE ' . $this->autid . '=' . $id;
		$res = $this->query_2_object($sql);
		if (empty($res)) return FALSE;
		$this->res = $res[0];
		$this->{$this->autid} = $this->res->{$this->autid};
		return $res[0];
	}

	// ---------------------------------------------------------------------------------------------

	public function save($table = NULL)
	{
		$this->db_write_check();
		if ($table) $this->set_table($table);
		$id = isset($this->fields[$this->autid]) ? $this->fields[$this->autid] : 0;
		$sql = 'SELECT '.$this->autid . ' FROM '.$this->table . '
				WHERE  '.$this->autid . ' = '.(int) $id;
		$rows = $this->count_rows($sql); 
		return($rows ? $this->update() : $this->add()); 
	}

	// ---------------------------------------------------------------------------------------------
	// DATA SANITIZATION
	
	// ---------------------------------------------------------------------------------------------
	// Adds required slashes to strings for DB entry

	public function e($str)
	{
		return $this->mysqli->real_escape_string($str);
	}

	// ---------------------------------------------------------------------------------------------
	// Makes sure a number is a number

	public function i($integer)
	{
		return (int) $integer;
	}

	// ---------------------------------------------------------------------------------------------

	public function n($num)
	{
		return(is_numeric($num) ? $num : 'NULL');
	}

	// ---------------------------------------------------------------------------------------------
	// Cleans booleans

	public function b($bool)
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
		$fields = implode(', ', array_keys(array_map('self::tick', (array) $this->fields)));
		$values = '';
		foreach($this->fields as $value) $values .= '"' . $this->mysqli->real_escape_string($value) . '", ';
		$values = substr($values, 0, -2);
		$sql = 'INSERT INTO '.$this->table . ' ('.$fields.') VALUES ('.$values.')';
		$res = $this->query($sql);
		if (!$res) return FALSE;
		$this->extract_into_vars($this->fields); // Keep object up to date
		$this->{$this->autid} = $this->get_insert_id();
		return($this->{$this->autid});
	}
	
	// ---------------------------------------------------------------------------------------------

	private function update()
	{
		$this->db_write_check();
		$autid = (int) $this->fields[$this->autid];	// Force integer
		unset($this->fields[$this->autid]);
		$assignments = '';
		foreach($this->fields as $field => $value) $assignments .= self::tick($field).'="'.$this->mysqli->real_escape_string($value).'", ';
		$assignments = substr($assignments, 0, -2);
		$sql = 'UPDATE '.$this->table . ' SET '.$assignments . ' WHERE '.$this->autid.' = '.$autid;
		$res = $this->query($sql);
		if (!$res) return FALSE;
		$this->extract_into_vars($this->fields); // Keep object up to date
		return($autid);
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
		$this->res = new stdClass;
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
		if ($error)
		{
			$errortext  = '<h3>There has been a SQL error</h3><p><b>Error Text:</b> '.$error.'</p>';
			// $errortext .= $query = '' ?  '' : '<p><b>SQL String:</b></p><pre>'.$query.'</pre>';
			$s = $_SERVER;
			unset(
					$s['HTTP_CACHE_CONTROL'], $s['HTTP_CONNECTION'], $s['HTTP_PRAGMA'], $s['HTTP_ACCEPT'],
					$s['HTTP_ACCEPT_ENCODING'], $s['HTTP_HOST'], $s['SERVER_SIGNATURE'],
					$s['SERVER_SOFTWARE'], $s['SERVER_NAME'], $s['SERVER_ADDR'], $s['SERVER_PORT'],
					$s['DOCUMENT_ROOT'], $s['SERVER_ADMIN'], $s['GATEWAY_INTERFACE'], $s['REMOTE_PORT']
				);
			$errortext .= '<p><b>URL:</b> '.$s['REQUEST_URI'].'</p>';
			$errortext .= '<p><b>Referrer:</b> '.(isset($s['HTTP_REFERER']) ? $s['HTTP_REFERER'] : '').'</p>';
			$errortext .= '<br><pre>'.print_r($s, 1).'</pre>';
			$errortext .= '<p><b>Trace:</b></p>';
			
			/* Doesn't work in Slim
			$bt = debug_backtrace();
			array_shift($bt);
			foreach($bt as &$step)
			{
				$line = $step['line'];
				unset($step['line']);
				if (isset($step['file'])) 	$step['file'] = str_replace($this->docroot, '', $step['file']);
				if (isset($step['object'])) unset($step['object']);
				if (isset($step['type'])) 	unset($step['type']);
				if (isset($step['function'])) $step['function'] = '<font color="red"><b>'.$step['function'].'</b> - line '.$line.'</font>';
			}
			$trace_text = print_r($bt, 1);
			$trace_text = substr(str_replace('Array', '', $trace_text), 2, -2);
			$trace_text = preg_replace('/^    \[(\d+)\]/m', "<b color='blue'>Step $1</b>", $trace_text);
			$errortext .= '<br><pre>'.$trace_text.'</pre>';
			*/

			$this->debug->msg($errortext);
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

	// ---------------------------------------------------------------------------------------------
	
	private static function tick($f)
	{
		return '`'.$f.'`';
	}

}
