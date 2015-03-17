<?php

// -------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------
//
// MQS: Message Queue Server
//
// Uses: Slim, Twig and Monolog + Ink Interface Framework (for built in front-end test app at /admin)
//
// -------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------

define('EMAIL_REGEX', '(?!(?:(?:\\x22?\\x5C[\\x00-\\x7E]\\x22?)|(?:\\x22?[^\\x5C\\x22]\\x22?)){255,})(?!(?:(?:\\x22?\\x5C[\\x00-\\x7E]\\x22?)|(?:\\x22?[^\\x5C\\x22]\\x22?)){65,}@)(?:(?:[\\x21\\x23-\\x27\\x2A\\x2B\\x2D\\x2F-\\x39\\x3D\\x3F\\x5E-\\x7E]+)|(?:\\x22(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F\\x21\\x23-\\x5B\\x5D-\\x7F]|(?:\\x5C[\\x00-\\x7F]))*\\x22))(?:\\.(?:(?:[\\x21\\x23-\\x27\\x2A\\x2B\\x2D\\x2F-\\x39\\x3D\\x3F\\x5E-\\x7E]+)|(?:\\x22(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F\\x21\\x23-\\x5B\\x5D-\\x7F]|(?:\\x5C[\\x00-\\x7F]))*\\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-+[a-z0-9]+)*\\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-+[a-z0-9]+)*)|(?:\\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\\]))');

define('MESSAGES_TABLE', 'Messages');

require '../vendor/autoload.php';
require_once '../classes/db.class.php';
require_once '../classes/auth.class.php';

$app = new \Slim\Slim();
$app->config('debug', TRUE);
$app->config('templates.path', '../templates');

// Add authentication middleware
$app->add(new Auth\Auth());

// Create database interface and store connection in container as singleton 
$app->container->singleton('db', function ()
{
	$db = new DB(NULL, TRUE);
	$db->set_table(MESSAGES_TABLE);
    return $db;
});

// Create logger and store in container as singleton 
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

// Error handling
class ResourceNotFoundException extends Exception {}


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

$app->get('/admin', function () use ($app)
{
	// $app->log->info("MQS '/admin' route");
	$app->render('index.tmp.html', array('name' => 'Rich'));
});

$app->get('/admin/create', function () use ($app)
{
	$app->render('create.tmp.html', array('name' => 'Rich', 'v' => 'create'));
});

$app->get('/admin/list(/:dmid)', function ($dmid = 'all@all.all') use ($app)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $_SERVER['HTTP_HOST'].'/messages/'.$dmid);
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
// Run the app
// -------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------

$app->run();


// -------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------
// Implement API Routes
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
	$app = \Slim\Slim::getInstance();
	
    $request = $app->request();
    $body = $request->getBody();
    $f = (array) json_decode($body);
    $f['CreatedGMT'] = gmdate('Y-m-d H:i:s');

	$db = $app->db;
	$db->fields = array_filter($f);
	$db->fields['ToDMID'] = $dmid;
	$res = $db->save();
	reply($res ? array('MsgID' => $res) : errobj('Could not create message'));
}

// -------------------------------------------------------------------------------------------------

function update_message($msgid)
{
	$app = \Slim\Slim::getInstance();

    $request = $app->request();
    $body = $request->getBody();
    $f = (array) json_decode($body);

	$db = $app->db;
	$db->fields = array_filter($f);
	$db->fields['MsgID'] = $msgid;
	$res = $db->save();
	reply($res ? array('Updated' => TRUE) : errobj('Could not update message'));
}

// -------------------------------------------------------------------------------------------------

function count_messages($dmid)
{
	$app = \Slim\Slim::getInstance();
	$db = $app->db;
	$sql = 'SELECT COUNT(MsgID) AS MessageCount FROM '.MESSAGES_TABLE.' WHERE ToDMID="'.$db->e($dmid).'" AND Deleted=0';
	$res = $db->query_2_object($sql);
	reply($res[0]);
}

// -------------------------------------------------------------------------------------------------

function list_messages($dmid, $start = 0, $limit = 50)
{
	$app = \Slim\Slim::getInstance();
	$db = $app->db;
	$sql = 'SELECT MsgID, Subject, `From`, CreatedGMT FROM '.MESSAGES_TABLE.' 
			WHERE '.($dmid != 'all@all.all' ? 'ToDMID="'.$db->e($dmid).'" AND ' : '').' Deleted=0
			ORDER BY MsgID DESC
			LIMIT '.$db->i($start).', '.$db->i($limit);
	try
	{
		$res = $db->query_2_object($sql);
		if (!count($res)) throw new ResourceNotFoundException();
	}
	catch (ResourceNotFoundException $e)
	{
		$app->response()->status(404); // Return 404  Not Found
	}
	catch (Exception $e)
	{
		$app->response()->status(400);
		$app->response()->header('X-Status-Reason', $e->getMessage());
	}
	reply(count($res) ? $res : errobj('DMID '.$dmid.', start '.$start.', limit '.$limit.', - no matching messages'));
}

// -------------------------------------------------------------------------------------------------

function get_message($msgid)
{
	$app = \Slim\Slim::getInstance();
	$db = $app->db;
	try
	{
		$res = $db->load($msgid);
		if (!$res) throw new ResourceNotFoundException();
	}
	catch (ResourceNotFoundException $e)
	{
		$app->response()->status(404); // Return 404  Not Found
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
	$app = \Slim\Slim::getInstance();
	$db = $app->db;
	$db->fields = array('MsgID' => $msgid, 'Deleted' => 1);
	try
	{
		$res = $res = $db->save();
		if (!$res) throw new ResourceNotFoundException();
	}
	catch (ResourceNotFoundException $e)
	{
		$app->response()->status(404); // Return 404  Not Found
	}
	catch (Exception $e)
	{
		$app->response()->status(400);
		$app->response()->header('X-Status-Reason', $e->getMessage());
	}
	reply($res ? array('Deleted' => TRUE) : errobj('Message with MsgID '.$msgid.' does not exist'));
}

// -------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------
// Utility functions
// -------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------

function reply($reply)
{
	$app = \Slim\Slim::getInstance();
	$app->response()->header('Content-Type', 'application/json');
	echo(json_encode($reply, JSON_PRETTY_PRINT));
}

// -------------------------------------------------------------------------------------------------

function errobj($errmsg)
{
	$err = new stdClass;
	$err->Error = TRUE;
	$err->ErrorMessage = $errmsg;
	return $err;
}
