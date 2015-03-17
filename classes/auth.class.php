<?php

namespace Auth;
 
class Auth extends \Slim\Middleware
{
    private $can_read  = array('127.0.0.1');
    private $can_write = array('127.0.0.1');
    private $can_admin = array('127.0.0.1');
    
    private $rob; // Request object
    
    public function __construct()
    {
    	$this->rob = new \stdClass;
    }
 
    public function call()
    {
		$this->rob->uri 	= $this->app->request()->getResourceUri();
		$this->rob->method 	= $this->app->request->getMethod();
		$this->rob->ip 		= $this->app->request->getIp();
    
    	$access = $this->check_route_access($this->rob);
		if ($access) {
			$this->next->call();
		} else {
			$this->access_denied($this->rob->ip);
		}
    }
    
    private function check_route_access($rob)
    {
    	$uri 	= $rob->uri;
    	$method = $rob->method;
    	$ip 	= $rob->ip;
    	
    	if (preg_match('~^/$~', $uri)) return TRUE;
    	if (preg_match('~^/admin/.*$~', $uri) && in_array($ip, $this->can_admin)) return TRUE;
    	if (preg_match('~^/messages(/.*)$~', $uri, $matches))
    	{
			if (in_array($ip, $this->can_write)) return TRUE;
			$tail = $matches[1];
			switch($method)
			{
				case 'GET':
					if (in_array($ip, $this->can_read)) return TRUE;
					break;
				case 'PUT':
					if (preg_match('~^/\d+/markread$~', $uri) && in_array($ip, $this->can_read)) return TRUE;
					break;
				case 'DELETE':
					if (in_array($ip, $this->can_read)) return TRUE;
					break;
			}
		}
    	return FALSE;
    }
    
    private function access_denied($ip)
    {
		$this->app->render('access-denied.tmp.html', array('ip' => $ip), 401);  // Return 401 Access Denied
		exit;
    }
}