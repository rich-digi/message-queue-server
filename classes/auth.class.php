<?php

namespace Auth;
 
// Error handling
class InvalidConfigException extends \Exception {}

class Auth extends \Slim\Middleware
{
    private $acl; // Access Control List
    private $rob; // Request Object
    
    const API_REGEX 	= '~^/messages(/.*)$~';
    const ADMIN_REGEX 	= '~^/admin(/.*)?$~';
    
    public function __construct()
    {
		$this->rob = new \stdClass;
    }
 
    public function call()
    {
    	try
    	{
			$this->acl = yaml_parse_file('../config/acl.yaml');
			if (!$this->acl) throw new \Exception('Invalid YAML: config/acl.yaml');
		}
		catch (\Exception $e)
		{
			$this->app->response->setStatus(500); // Server error
			$this->app->response->setBody($e->getMessage());
			return;
		}
		try
		{
			$test = isset($this->acl['can_read']) && isset($this->acl['can_write']) && isset($this->acl['can_admin']);
			if (!$test)
			{
				throw new InvalidConfigException('Invalid config: one of more access lists is missing: config/acl.yaml
												  - Expected can_read, can_write and can_admin');
			}
    	}
		catch (InvalidConfigException $e)
		{
			$this->app->response->setStatus(500); // Server error
			$this->app->response->setBody($e->getMessage());
			return;
		}

		$this->rob->uri 	= $this->app->request()->getResourceUri();
		$this->rob->method 	= $this->app->request->getMethod();
		$this->rob->ip 		= $this->app->request->getIp();
		if (!$this->check_route_access($this->rob))
		{
			$this->access_denied($this->rob);
		}
		else
		{
			$this->next->call();
		}
    }
    
    private function check_route_access($rob)
    {
    	$uri 	= $rob->uri;
    	$method = $rob->method;
    	$ip 	= $rob->ip;
    	
    	extract($this->acl); // Should set $can_read, $can_write and $can_admin
    	if (preg_match('~^/$~', $uri)) return TRUE;
    	if (preg_match(self::ADMIN_REGEX, $uri) && in_array($ip, $can_admin)) return TRUE;
    	if (preg_match(self::API_REGEX, $uri, $matches))
    	{
			if (in_array($ip, $can_write)) return TRUE;
			$tail = $matches[1];
			switch($method)
			{
				case 'GET':
					if (in_array($ip, $can_read)) return TRUE;
					break;
				case 'PUT':
					if (preg_match('~^/\d+/markread$~', $uri) && in_array($ip, $can_read)) return TRUE;
					break;
				case 'DELETE':
					if (in_array($ip, $can_read)) return TRUE;
					break;
			}
		}
    	return FALSE;
    }
    
    private function access_denied($rob)
    {
		$app = $this->app;
		$app->response->setStatus(401); // Return 401 Access Denied
		if (!preg_match(self::ADMIN_REGEX, $rob->uri))
		{
			$app->response->headers->set('Content-Type', 'application/json');
			$app->response->setBody(json_encode(array(
							'Message' => '401 Access Denied',
							'Details' => 'Your IP ('.$rob->ip.') can\'t access this resource'
			), JSON_PRETTY_PRINT));
		}
		else
		{
			$app->response->headers->set('Content-Type', 'text/html');
			$app->response->setBody($app->view->fetch('access-denied.tmp.html', array('ip' => $rob->ip)));
		}
		return;
    }
}