<?php

// -------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------
// MQS: Message Queue Server
// -------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------

require 'vendor/autoload.php';
require_once 'classes/db.class.php';

$app = new \Slim\Slim();
$app->config('debug', TRUE);

define('EMAIL_REGEX', '(?!(?:(?:\\x22?\\x5C[\\x00-\\x7E]\\x22?)|(?:\\x22?[^\\x5C\\x22]\\x22?)){255,})(?!(?:(?:\\x22?\\x5C[\\x00-\\x7E]\\x22?)|(?:\\x22?[^\\x5C\\x22]\\x22?)){65,}@)(?:(?:[\\x21\\x23-\\x27\\x2A\\x2B\\x2D\\x2F-\\x39\\x3D\\x3F\\x5E-\\x7E]+)|(?:\\x22(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F\\x21\\x23-\\x5B\\x5D-\\x7F]|(?:\\x5C[\\x00-\\x7F]))*\\x22))(?:\\.(?:(?:[\\x21\\x23-\\x27\\x2A\\x2B\\x2D\\x2F-\\x39\\x3D\\x3F\\x5E-\\x7E]+)|(?:\\x22(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F\\x21\\x23-\\x5B\\x5D-\\x7F]|(?:\\x5C[\\x00-\\x7F]))*\\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-+[a-z0-9]+)*\\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-+[a-z0-9]+)*)|(?:\\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\\]))');

define('MESSAGES_TABLE', 'Messages');


// -------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------
// Register Routes
// -------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------

$app->get 	('/', 									'identify');
$app->put 	('/messages/:dmid', 	 				'create_message');
$app->post	('/messages/:msgid', 		 			'update_message');
$app->get 	('/messages/:dmid/count', 				'count_messages');
$app->get 	('/messages/:dmid(/:start)(/:limit)', 	'list_messages')->conditions(array('dmid' => EMAIL_REGEX));
$app->get 	('/messages/:msgid', 		 			'get_message')->conditions(array('msgid' => '\d+'));
$app->delete('/messages/:msgid', 		 			'delete_message');


// -------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------
// Initialise database connection and run the app
// -------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------

$db = new DB(NULL, TRUE);
$app->run();


// -------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------
// Implement Routes
// -------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------

function identify()
{
	$res = array('whoami' => 'I am MQS, the Message Queue Server at '.$_SERVER['HTTP_HOST']);
	reply($res);
}

// -------------------------------------------------------------------------------------------------

function create_message($dmid)
{
	global $db;
	
    $request = $app->request();
    $body = $request->getBody();
    $input = json_decode($body);
	
	// Set fields to save
	$f = array(
			'ToDMID' 	=> $dmid,
			'From' 		=> $input->From,
			'ReplyTo' 	=> $input->ReplyTo,
			'Subject'	=> $input->Subject,
			'Content'	=> $input->Content
	);
	$res = $db->save($f, MESSAGES_TABLE);
	reply($res);
}

// -------------------------------------------------------------------------------------------------

function update_message($msgid)
{
	global $db;
	$sql = '';
	$res = $db->save($f, MESSAGES_TABLE);
	reply($res);
}

// -------------------------------------------------------------------------------------------------

function count_messages($dmid)
{
	global $db;
	$sql = 'SELECT COUNT(MsgID) FROM '.MESSAGES_TABLE.' WHERE ToDMID="'.$db->e($dmid).'" AND Deleted=0'
	$res = $db->query_2_object($sql);
	reply($res[0]);
}

// -------------------------------------------------------------------------------------------------

function list_messages($dmid, $start = 0, $limit = 50)
{
	global $db;
	$sql = 'SELECT MsgID, Subject, CreatedGMT FROM '.MESSAGES_TABLE.'. 
			WHERE ToDMID="'.$db->e($dmid).'" AND Deleted=0
			ORDER BY MsgID DESC
			LIMIT '.$db->i($start).', '.$db->i($limit);
	$res = $db->query_2_object($sql);
	reply(count($res) ? $res : errobj('DMID '.$dmid.', start '.$start.', limit '.$limit.', - no matching messages'));
}

// -------------------------------------------------------------------------------------------------

function get_message($msgid)
{
	global $db;
	$res = $db->load($msgid, MESSAGES_TABLE);
	reply(count($res) ? $res : errobj('Message with MsgID '.$msgid.' does not exist'));
}

// -------------------------------------------------------------------------------------------------

function delete_message($msgid)
{
	global $db;
	$sql = 'UPDATE Messages SET Deleted=1 WHERE MsgID='.$db->i($msgid);
	$res = $db->query($sql);
	reply($res ? array('Deleted' => TRUE) : errobj('Message with MsgID '.$msgid.' does not exist'));
}


// -------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------
// Utility functions
// -------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------

function reply($reply)
{
	header('Content-Type: application/json');
	echo json_encode($reply, JSON_PRETTY_PRINT);
	exit;
}

// -------------------------------------------------------------------------------------------------

function errobj($errmsg)
{
	$err = new stdClass;
	$err->Error = TRUE;
	$err->ErrorMessage = $errmsg;
	return $err;
}
