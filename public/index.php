<?php

// -------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------
//
// MQS: Message Queue Server
//
// Uses: Slim, Twig and Monolog
//
// -------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------

define('EMAIL_REGEX', '(?!(?:(?:\\x22?\\x5C[\\x00-\\x7E]\\x22?)|(?:\\x22?[^\\x5C\\x22]\\x22?)){255,})(?!(?:(?:\\x22?\\x5C[\\x00-\\x7E]\\x22?)|(?:\\x22?[^\\x5C\\x22]\\x22?)){65,}@)(?:(?:[\\x21\\x23-\\x27\\x2A\\x2B\\x2D\\x2F-\\x39\\x3D\\x3F\\x5E-\\x7E]+)|(?:\\x22(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F\\x21\\x23-\\x5B\\x5D-\\x7F]|(?:\\x5C[\\x00-\\x7F]))*\\x22))(?:\\.(?:(?:[\\x21\\x23-\\x27\\x2A\\x2B\\x2D\\x2F-\\x39\\x3D\\x3F\\x5E-\\x7E]+)|(?:\\x22(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F\\x21\\x23-\\x5B\\x5D-\\x7F]|(?:\\x5C[\\x00-\\x7F]))*\\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-+[a-z0-9]+)*\\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-+[a-z0-9]+)*)|(?:\\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\\]))');

define('MESSAGES_TABLE', 'Messages');

require '../vendor/autoload.php';
require_once '../classes/db.class.php';

$app = new \Slim\Slim();
$app->config('debug', TRUE);
$app->config('templates.path', '../templates');

// Create monolog logger and store logger in container as singleton 
// (Singleton resources retrieve the same log resource definition each time)
$app->container->singleton('log', function () {
    $log = new \Monolog\Logger('MQS');
    $log->pushHandler(new \Monolog\Handler\StreamHandler('../logs/app.log', \Monolog\Logger::DEBUG));
    return $log;
});

// Prepare view
$app->view(new \Slim\Views\Twig());
$app->view->parserOptions = array(
    'charset' => 'utf-8',
    'cache' => realpath('../templates/cache'),
    'auto_reload' => true,
    'strict_variables' => false,
    'autoescape' => true
);
$app->view->parserExtensions = array(new \Slim\Views\TwigExtension());


// -------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------
// Register Routes
// -------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------

// (1) MQS API routes...

$app->get 	('/', 									'identify');
$app->put 	('/messages/:dmid', 	 				'create_message');
$app->post	('/messages/:msgid', 		 			'update_message');
$app->get 	('/messages/:dmid/count', 				'count_messages');
$app->get 	('/messages/:dmid(/:start)(/:limit)', 	'list_messages')->conditions(array('dmid' => EMAIL_REGEX));
$app->get 	('/messages/:msgid', 		 			'get_message')->conditions(array('msgid' => '\d+'));
$app->delete('/messages/:msgid', 		 			'delete_message');


// (2) MQS admin area routes...

$app->get('/admin', function () use ($app)
{
	$app->log->info("MQS '/admin' route");
	$app->render('index.tmp.html', array('name' => 'Rich'));
});

$app->get('/admin/create', function () use ($app)
{
	$app->render('create.tmp.html', array('name' => 'Rich'));
});

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
	$app = \Slim\Slim::getInstance();
	
    $request = $app->request();
    $body = $request->getBody();
    $input = json_decode($body);

 	//$keys=array("a","b","c","d");
 	//array_fill_keys($keys, '');
    //$input = (object) array_merge(array_flip(explode('')) $obj2, (array) $input);



	print_r($input); exit;
	


	// Set fields to save
	// ARRAY interesct!!! alllowed fields;
	$f = array(
			'ToDMID' 			=> $dmid,
			'From' 				=> $input->From,
			'ReplyTo' 			=> $input->ReplyTo,
			'Template' 			=> $input->Template,
			'Creator' 			=> $input->Creator,
			'Priority' 			=> $input->Priority,
			'DeleteAfterDays' 	=> $input->DeleteAfterDays,
			'Subject'			=> $input->Subject,
			'Content'			=> $input->Content
	);
	$res = $db->save($f, MESSAGES_TABLE);
	reply($res);
}

// -------------------------------------------------------------------------------------------------

function update_message($msgid)
{
	global $db;
	$app = \Slim\Slim::getInstance();

    $request = $app->request();
    $body = $request->getBody();
    $input = json_decode($body);
	
	// Set fields to save
	// ARRAY interesct!!! alllowed fields;
	$f = array(
			'MsgID'				=> $msgid,
			'ToDMID' 			=> $input->ToDMID,
			'From' 				=> $input->From,
			'ReplyTo' 			=> $input->ReplyTo,
			'Template' 			=> $input->Template,
			'Creator' 			=> $input->Creator,
			'Priority' 			=> $input->Priority,
			'DeleteAfterDays' 	=> $input->DeleteAfterDays,
			'Subject'			=> $input->Subject,
			'Content'			=> $input->Content
	);
	$res = $db->save($f, MESSAGES_TABLE);
	reply($res);
}

// -------------------------------------------------------------------------------------------------

function count_messages($dmid)
{
	global $db;
	$sql = 'SELECT COUNT(MsgID) AS MessageCount FROM '.MESSAGES_TABLE.' WHERE ToDMID="'.$db->e($dmid).'" AND Deleted=0';
	$res = $db->query_2_object($sql);
	reply($res[0]);
}

// -------------------------------------------------------------------------------------------------

function list_messages($dmid, $start = 0, $limit = 50)
{
	global $db;
	$sql = 'SELECT MsgID, Subject, `From`, CreatedGMT FROM '.MESSAGES_TABLE.' 
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
	reply($res ? $res : errobj('Message with MsgID '.$msgid.' does not exist'));
}

// -------------------------------------------------------------------------------------------------

function delete_message($msgid)
{
	global $db;
	$f = array('MsgID' => $msgid, 'Deleted' => 1);
	$res = $db->save($f, MESSAGES_TABLE);
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
