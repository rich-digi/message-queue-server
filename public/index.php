<?php

// -------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------
//
// MQS: Message Queue Server
//
// Uses: Slim, Twig and Monolog + Ink Interface Framework (for built-in front-end app @/admin)
//
// -------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------

 // Set app mode to 'test' or 'live'
 // In test mode requests with user-agant: test-api.py can use an x-ip-override header to set the
 // client IP address, which is useful for authentication testing
 // This backdoor isn't available in live mode
define('MODE', 'test');

define('EMAIL_REGEX', '(?!(?:(?:\\x22?\\x5C[\\x00-\\x7E]\\x22?)|(?:\\x22?[^\\x5C\\x22]\\x22?)){255,})(?!(?:(?:\\x22?\\x5C[\\x00-\\x7E]\\x22?)|(?:\\x22?[^\\x5C\\x22]\\x22?)){65,}@)(?:(?:[\\x21\\x23-\\x27\\x2A\\x2B\\x2D\\x2F-\\x39\\x3D\\x3F\\x5E-\\x7E]+)|(?:\\x22(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F\\x21\\x23-\\x5B\\x5D-\\x7F]|(?:\\x5C[\\x00-\\x7F]))*\\x22))(?:\\.(?:(?:[\\x21\\x23-\\x27\\x2A\\x2B\\x2D\\x2F-\\x39\\x3D\\x3F\\x5E-\\x7E]+)|(?:\\x22(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F\\x21\\x23-\\x5B\\x5D-\\x7F]|(?:\\x5C[\\x00-\\x7F]))*\\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-+[a-z0-9]+)*\\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-+[a-z0-9]+)*)|(?:\\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\\]))');

define('MESSAGES_TABLE', 		'Messages');
define('MESSAGES_TEST_TABLE', 	'MessagesTest');

define('TEST_AGENT', 'test-api.py');

require '../vendor/autoload.php';
require_once '../classes/db.class.php';
require_once '../classes/auth.class.php';

$app = new \Slim\Slim();
$app->config('debug', TRUE);
$app->config('mode', MODE); // live or test
$app->config('templates.path', '../templates');

// Add authentication middleware
$app->add(new Auth\Auth());

// Create database interface and store connection in container as singleton 
$app->container->singleton('db', function() use ($app)
{
	$db = new DB(NULL, TRUE);
	$testing = $app->config('mode') == 'test' && $app->request->headers->get('user-agent') == TEST_AGENT;
	$db->set_table($testing ? MESSAGES_TEST_TABLE: MESSAGES_TABLE);
    return $db;
});

// Create logger and store in container as singleton 
$app->container->singleton('log', function()
{
    $log = new \Monolog\Logger('MQS');
    $log->pushHandler(new \Monolog\Handler\StreamHandler('../logs/app.log', \Monolog\Logger::DEBUG));
    return $log;
});

// Prepare view hanlders, with Twig templating engine
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
// ---------------------

// @ GET /
// Identify
//
$app->get('/', 'identify');

// @ GET /messages/<EMAIL>/count
// Return a count of the messages for <EMAIL>
//
$app->get('/messages/:dmid/count', 'count_messages')->conditions(array('dmid' => EMAIL_REGEX));

// @ GET /messages/<EMAIL>
// Return a list of the messages for <EMAIL>
//
// @ GET /messages/<EMAIL>/150/20
// Return a list of the next 20 messages for <EMAIL> starting at message 150
//
$app->get('/messages/:dmid(/:start)(/:limit)', 'list_messages')->conditions(array('dmid' => EMAIL_REGEX));

// @ GET /messages/<MSGID>
// Return the message with numeric message id <MSGID>
// Message IDs are returned by the list command above
//
$app->get('/messages/:msgid', 'get_message')->conditions(array('msgid' => '\d+'));

// @ POST /messages/<EMAIL>
// Create a message for <EMAIL>
//
$app->post('/messages/:dmid', 'create_message')->conditions(array('dmid' => EMAIL_REGEX));

// @ PUT /messages/<MSGID>
// Update message with message <MSGID>
//
$app->put('/messages/:msgid', 'update_message');

// @ PATCH /messages/<MSGID>/markread
// Mark message as read
//
$app->patch('/messages/:msgid/markread', 'mark_message_read');

// @ DELETE /messages/<MSGID>
// Mark message as deleted
//
$app->delete('/messages/:msgid', 'delete_message')->conditions(array('msgid' => '\d+'));;


// (2) MQS admin area routes...
// ----------------------------

$app->get('/admin', function () use ($app)
{
	// $app->log->info("MQS '/admin' route");
	$app->render('dashboard.tmp.html', array('name' => 'Rich', 'v' => 'dashboard'));
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
	$app->render('list.tmp.html', array('name' => 'Rich', 'v' => 'list', 'default_ToDMID' => $dmid, 'messages' => $res));
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


// (3) Special test route (clears test table before running test suite)
// --------------------------------------------------------------------

// @ DELETE /messages/test
// 
$app->delete('/messages/test', function () use ($app)
{
	$db = $app->db;
	$sql = array(
					'TRUNCATE '.MESSAGES_TEST_TABLE,
					'ALTER TABLE '.MESSAGES_TEST_TABLE.' AUTO_INCREMENT = 1'
				);
	$res = $db->query($sql);
	reply($res ? array('Cleared' => TRUE) : errobj('Could not clear test table: '.$db->get_error()));
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
    $req = $app->request();
    $body = $req->getBody();
    $f = (array) json_decode($body);
    if (empty($f))
    {
 		$app->response()->setStatus(500);
    	reply(errobj('Invalid JSON Payload'));
    	return;
    }
    $f['ToDMID'] = $dmid;
    $f['CreatedGMT'] = gmdate('Y-m-d H:i:s');
	$db = $app->db;
	$db->fields = array_filter($f);
	$res = $db->save();
	$app->response->setStatus(201); // 201 Created - BUT WHAT IF IT FAILS?
	$app->response->headers->set('Location', $req->getUrl().'/messages/'.$res); // The location of the newly created message
	reply($res ? array('MsgID' => $res) : errobj('Could not create message'));
}

// -------------------------------------------------------------------------------------------------

function update_message($msgid)
{
	if (!check_message_exists($msgid)) return;
	
	// Now try to do the update
	$app = \Slim\Slim::getInstance();
    $request = $app->request();
    $body = $request->getBody();
    $f = (array) json_decode($body);
    if (empty($f))
    {
 		$app->response()->setStatus(500);
    	reply(errobj('Invalid JSON Payload'));
    	return;
    }
    $f['MsgID'] = $msgid;
	$db = $app->db;
	$db->fields = array_filter($f);
	try
	{
		$res = $db->update();
		if (!$res) throw new ResourceNotFoundException();
	}
	catch (ResourceNotFoundException $e)
	{
		$app->response()->setStatus(400); // Return 400  Bad Request
	}
	catch (Exception $e)
	{
		$res = FALSE;
		$app->response()->setStatus(400);
		$app->response()->headers->set('X-Status-Reason', $e->getMessage());
	}
	reply($res ? array('Updated' => TRUE) : errobj('Could not update message', $db->get_error()));
}

// -------------------------------------------------------------------------------------------------

function mark_message_read($msgid)
{
	if (!check_message_exists($msgid)) return;

	$app = \Slim\Slim::getInstance();
	$db = $app->db;
	$db->fields = array('MsgID' => $msgid,'ReadGMT' => gmdate('Y-m-d H:i:s'));
	$res = $db->save();
	reply($res ? array('MarkedRead' => $db->fields['ReadGMT']) : errobj('Could not mark message as read'));
}

// -------------------------------------------------------------------------------------------------

function count_messages($dmid)
{
	$app = \Slim\Slim::getInstance();
	$db = $app->db;
	$sql = 'SELECT COUNT(MsgID) AS MessageCount FROM '.$db->table.' WHERE ToDMID="'.$db->e($dmid).'" AND Deleted=0';
	$res = $db->query_2_object($sql);
	$res[0]->MessageCount = (int) $res[0]->MessageCount;
	reply($res[0]);
}

// -------------------------------------------------------------------------------------------------

function list_messages($dmid, $start = 0, $limit = 50)
{
	$app = \Slim\Slim::getInstance();
	$db = $app->db;
	$all = $dmid == 'all@all.all';
	$sql = 'SELECT '.($all ? 'ToDMID, ' : '').'MsgID, Subject, `From`, CreatedGMT
			FROM '.$db->table.' 
			WHERE '.($all ? '' : 'ToDMID="'.$db->e($dmid).'" AND ').' Deleted=0
			ORDER BY MsgID DESC
			LIMIT '.$db->i($start).', '.$db->i($limit);
	try
	{
		$res = $db->query_2_object($sql);
		if (!count($res)) throw new ResourceNotFoundException();
	}
	catch (ResourceNotFoundException $e)
	{
		$app->response()->setStatus(404); // Return 404  Not Found
	}
	catch (Exception $e)
	{
		$res = FALSE;
		$app->response()->setStatus(400);
		$app->response()->headers->set('X-Status-Reason', $e->getMessage());
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
		$app->response()->setStatus(404); // Return 404  Not Found
	}
	catch (Exception $e)
	{
		$res = FALSE;
		$app->response()->setStatus(400);
		$app->response()->headers->set('X-Status-Reason', $e->getMessage());
	}
	reply($res ? $res : errobj('Message with MsgID '.$msgid.' does not exist'));
}

// -------------------------------------------------------------------------------------------------

function delete_message($msgid)
{
	if (!check_message_exists($msgid)) return;
	
	$app = \Slim\Slim::getInstance();
	$db = $app->db;
	$db->fields = array('MsgID' => $msgid, 'Deleted' => 1);
	try
	{
		$res = $db->update();
		if (!$res) throw new ResourceNotFoundException();
		// $app->response->setStatus(204); // Don't set 204 deleted if including a response body
	}
	catch (ResourceNotFoundException $e)
	{
		$app->response()->setStatus(400); // Return 404  Not Found
	}
	catch (Exception $e)
	{
		$res = FALSE;
		$app->response()->setStatus(400);
		$app->response()->headers->set('X-Status-Reason', $e->getMessage());
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
	$app->response()->headers->set('Content-Type', 'application/json; charset=utf-8');
	echo(json_encode($reply, JSON_PRETTY_PRINT));
}

// -------------------------------------------------------------------------------------------------

function errobj($errmsg, $errextra = NULL)
{
	$err = new stdClass;
	$err->Error = TRUE;
	$err->ErrorMessage = $errmsg;
	if ($errextra) $err->Details = $errextra;
	return $err;
}

// -------------------------------------------------------------------------------------------------

function check_message_exists($msgid)
{
	// TODO: Reimplement as route Middleware
	
	$app = \Slim\Slim::getInstance();
	$db = $app->db;
	try
	{
		$sql = 'SELECT MsgID FROM '.$db->table.' WHERE MsgID='.$db->i($msgid);
		$res = $db->count_rows($sql);
		if (!$res) throw new ResourceNotFoundException();
	}
	catch (ResourceNotFoundException $e)
	{
		$app->response()->setStatus(400); // Return 400  Bad Request
		reply(errobj('Message with MsgID '.$msgid.' does not exist'));
	}
	return $res;
}
