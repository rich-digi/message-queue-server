<?php

// debug.class.php
// -------------------------------------------------------------------------------------------------
// Provides a consistent debugging interface for all other classes
// for onscreen debugging
//
// Author > Rich Knight
// -------------------------------------------------------------------------------------------------

class Debug
{

	var $debug;			 // Controls whether debugging is on or off
	var $debug_msg;		 // Holds debugging messages
	var $max_width;		 // Maximum number of characters in a line in debugging output
	
	// ---------------------------------------------------------------------------------------------

	function Debug($status = FALSE)
	{
		// CONSTRUCTOR
		$this->debug  	 = (int) $status;
		$this->debug_msg = array();
		$this->max_width = 150;
		$this->docroot = $_SERVER['DOCUMENT_ROOT'];
		if (substr($this->docroot, -1) == '/') $this->docroot = substr($this->docroot, 0, -1);
	}
	
	// ---------------------------------------------------------------------------------------------
	// ---------------------------------------------------------------------------------------------
	// ---------------------------------------------------------------------------------------------

	function heading($msg = '')
	{
		if (!$this->debug) return;
		$this->debug_msg[] = '<h2 class="debug">' . wordwrap($msg, $this->max_width, "<br/>\n", 1) . '</h2>';
	}

	// ---------------------------------------------------------------------------------------------

	function boldmsg($msg = '')
	{
		if (!$this->debug) return;
		if (is_array($msg))
		{	
			$this->debug_msg = array_merge($this->debug_msg, $msg); // array_filter to bold this here
		}
		else
		{
			$this->debug_msg[] = wordwrap('<b>' . $msg . '</b>', $this->max_width, "<br/>\n", 1);
		}
	}

	// ---------------------------------------------------------------------------------------------

	function msg($msg = '')
	{
		if (!$this->debug) return;
		if (is_array($msg))
		{	
			$this->debug_msg = array_merge($this->debug_msg, $msg);
		}
		else
		{
			$this->debug_msg[] = wordwrap($msg, $this->max_width, "<br/>\n", 1);
		}
	}

	// ---------------------------------------------------------------------------------------------

	function err($msg = '')
	{
		if (!$this->debug) return;
		if (is_array($msg))
		{	
			$this->debug_msg = array_merge($this->debug_msg, $msg);
		}
		else
		{
			$this->debug_msg[] = '<span class="err">' . wordwrap($msg, $this->max_width, "<br/>\n", 1) . '</span>';
		}
	}

	// ---------------------------------------------------------------------------------------------

	function xml($xml_str)
	{
		if (!$this->debug) return;
        try
        {
			// Format XML
			$xml = new DOMDocument();
			$xml->preserveWhiteSpace = FALSE;
			$xml->formatOutput = TRUE;
			$xml->loadXML($xml_str);
			$formatted_XML = $xml->saveXML();
			$formatted_XML = htmlentities($formatted_XML);
			$formatted_XML = preg_replace('/(&lt;.+?&gt;)/', '<span class="tag">$1</span>', $formatted_XML);							// Colour tags
			$formatted_XML = preg_replace('/ (\S+=)(&quot;.+?&quot;)/', ' <span class="att">$1<span>$2</span></span>', $formatted_XML); // Colour attribiutes
			$this->debug_msg[] = '<pre>' . ($formatted_XML) . "</pre>\n";
		}
		catch(Exception $e)
		{
			$this->debug_msg[] = "<pre>INVALID XML</pre>\n";
		}
	}

	// ---------------------------------------------------------------------------------------------
	// ---------------------------------------------------------------------------------------------

	function inspect($name, $var)
	{
		if (!$this->debug) return;
		$this->debug_msg[] = '<b>' . $name . '</b><pre>' . print_r($var, 1) . '</pre>';
	}

	// ---------------------------------------------------------------------------------------------

	function trace()
	{
		if (!$this->debug) return;
		$backtrace = debug_backtrace();
		array_shift($backtrace);
		foreach ($backtrace as &$step)
		{
			if (isset($step['file'])) $step['file'] = str_replace($this->docroot, '', $step['file']);
			if (isset($step['object'])) unset($step['object']);
			if (isset($step['function'])) $step['function'] = '<font color="red">' . $step['function'] . '</font>';
		}
		$trace_text = print_r(array_reverse($backtrace), 1);
		$trace_text = substr(str_replace('Array', '', $trace_text), 2, -2);
		$trace_text = preg_replace('/^    \[(\d+)\]/m', "<b class='step'>Step $1</b>", $trace_text);
		$this->debug_msg[] = '<br /><b>Trace ...</b><pre>' . $trace_text . '</pre>';
	}
	
	// ---------------------------------------------------------------------------------------------
	
	function divide()
	{
		if (!$this->debug) return;
		$this->debug_msg[] = '<hr class="debug" />';
	}

	// ---------------------------------------------------------------------------------------------
	// ---------------------------------------------------------------------------------------------
	// ---------------------------------------------------------------------------------------------

	function handle_errors($errno, $errstr, $errfile, $errline)
	{
		return FALSE;
	}

	// ---------------------------------------------------------------------------------------------
	// ---------------------------------------------------------------------------------------------
	// ---------------------------------------------------------------------------------------------

	function get()
	{
		return($this->debug_msg);
	}

	// ---------------------------------------------------------------------------------------------

	function down()
	{
		$this->debug--;
	}

	// ---------------------------------------------------------------------------------------------
	
	function up()
	{
		$this->debug++;
	}

	// ---------------------------------------------------------------------------------------------

	function flush()
	{
		$temp = $this->debug_msg;
		$this->debug_msg = array();
		return($temp);
	}

	// ---------------------------------------------------------------------------------------------

	function display($flag = FALSE)
	{
		$s = array('</h2><br/>', '<hr class="debug" /><br/>');
		$r = array('</h2>',      '<hr class="debug" />'     );
		if ($flag)
		{
			return(str_replace($s, $r, implode("<br/>\n", $this->debug_msg)));
		}
		else
		{
			echo(str_replace($s, $r, implode("<br/>\n", $this->debug_msg)));
		}
	}

	// ---------------------------------------------------------------------------------------------

}
