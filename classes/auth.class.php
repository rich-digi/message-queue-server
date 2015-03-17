<?php

namespace Auth;
 
class Auth extends \Slim\Middleware
{
    private $ips_read  = array('127.0.0.1');
    private $ips_write = array('127.0.0.1');
    private $ips_admin = array('127.0.0.1');
    
    public function __construct()
    {
    }
 
    public function call()
    {
		$uri = $this->app->request()->getResourceUri();
		$rip = $this->app->request->getIp();
    
    	// if (preg_match($uri, '/messages/') && in_array($rip, $ips_read))
		
		$this->next->call();
    }
}