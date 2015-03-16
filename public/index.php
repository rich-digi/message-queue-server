<?php

// -------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------
//
// MQS: Message Queue Server
//
// Uses: Slim, Twig and Monolog + Ink Interface Framework (on the front-end)
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

// Add authentication Middleware
$app->add(new Auth());

// Create monolog logger and store logger in container as singleton 
// Singleton resources retrieve the same log resource definition each time
$app->container->singleton('log', function ()
{
    $log = new \Monolog\Logger('MQS');
    $log->pushHandler(new \Monolog\Handler\StreamHandler('../logs/app.log', \Monolog\Logger::DEBUG));
    return $log;
});

// Prepare view hanlers, with Twig templating engine
$app->view(new \Slim\Views\Twig());
$app->view->parserOptions = array(
    'charset' 			=> 'utf-8',
    'cache' 			=> realpath('../templates/cache'),
    'auto_reload' 		=> TRUE,
    'strict_variables' 	=> FALSE,
    'autoescape' 		=> TRUE
);
$app->view->parserExtensions = array(new \Slim\Views\TwigExtension());


// -------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------
// Register Routes
// -------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------

// (1) MQS API routes...

$app->get 	('/', 									'identify');
$app->post 	('/messages/:dmid', 	 				'create_message');
$app->put	('/messages/:msgid', 		 			'update_message');
$app->get 	('/messages/:dmid/count', 				'count_messages');
$app->get 	('/messages/:dmid(/:start)(/:limit)', 	'list_messages')->conditions(array('dmid' => EMAIL_REGEX));
$app->get 	('/messages/:msgid', 		 			'get_message')->conditions(array('msgid' => '\d+'));
$app->delete('/messages/:msgid', 		 			'delete_message');


// (2) MQS admin area routes...

$app->get('/admin', 'authenticate', function () use ($app)
{
	// $app->log->info("MQS '/admin' route");
	$app->render('index.tmp.html', array('name' => 'Rich'));
});

$app->get('/admin/create', 'authenticate', function () use ($app)
{
	$app->render('create.tmp.html', array('name' => 'Rich', 'v' => 'create'));
});

$app->get('/admin/list', function () use ($app)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $_SERVER['HTTP_HOST'].'/messages/rich@apewave.com');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$res = curl_exec($ch);
	curl_close($ch);
	$res = json_decode($res);
	$app->render('list.tmp.html', array('name' => 'Rich', 'v' => 'list', 'messages' => $res));
});

$app->get('/admin/edit/:msgid', function ($msgid) use ($app)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $_SERVER['HTTP_HOST'].'/messages/'.$msgid);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$res = curl_exec($ch);
	curl_close($ch);
	$res = json_decode($res);
	$res->MsgID = $msgid;
	$app->render('edit.tmp.html', array('name' => 'Rich', 'v' => 'edit', 'message' => $res));
});


// -------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------
// Initialise database connection and run the app
// -------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------


class ResourceNotFoundException extends Exception {}



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
    $f = (array) json_decode($body);
    $f['CreatedGMT'] = gmdate('Y-m-d H:i:s');
	$db->fields = array_filter($f);
	$db->fields['ToDMID'] = $dmid;
	$res = $db->save(MESSAGES_TABLE);
	reply($res ? array('MsgID' => $res) : errobj('Could not create message'));
}

// -------------------------------------------------------------------------------------------------

function update_message($msgid)
{
	global $db;
	$app = \Slim\Slim::getInstance();

    $request = $app->request();
    $body = $request->getBody();
    $f = (array) json_decode($body);
	$db->fields = array_filter($f);
	$db->fields['MsgID'] = $msgid;
	$res = $db->save(MESSAGES_TABLE);
	reply($res ? array('Updated' => TRUE) : errobj('Could not update message'));
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
	try
	{
		$res = $db->load($msgid, MESSAGES_TABLE);
		if ($res)
		{
			
		}
		else
		{
		
		}
	}
	catch (ResourceNotFoundException $e)
	{
		// Return 404  Not Found
		$app->response()->status(404);
	}
	catch (Exception $e)
	{
		$app->response()->status(400);
		$app->response()->header('X-Status-Reason', $e->getMessage());
	}

	reply($res ? $res : errobj('Message with MsgID '.$msgid.' does not exist'));
}

// -------------------------------------------------------------------------------------------------

function delete_message($msgid)
{
	global $db;
	$db->fields = array('MsgID' => $msgid, 'Deleted' => 1);
	$res = $db->save(MESSAGES_TABLE);
	reply($res ? array('Deleted' => TRUE) : errobj('Message with MsgID '.$msgid.' does not exist'));
}

// -------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------
// Utility functions
// -------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------

function authenticate(\Slim\Route $route)
{
    // Route middleware for simple API authentication
	$app = \Slim\Slim::getInstance();
	$uid = $app->request->headers->get('UID');
	$key = $app->request->headers->get('KEY');
    if (validateUserKey($uid, $key) === FALSE) $app->halt(401); // 401 Authorization Required
}

// -------------------------------------------------------------------------------------------------

function validateUserKey($uid, $key)
{
	// We'll flesh out authetication later...
	$res = $uid == '' && $key == '';
	// return $res;
	return TRUE;
}

// -------------------------------------------------------------------------------------------------

function reply($reply)
{
	$app = \Slim\Slim::getInstance();
	$app->response()->header('Content-Type', 'application/json');
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
