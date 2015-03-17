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
		// $this->next->call();
		
		$this->access_denied();
		
    }
    
    private function access_denied()
    {
		$error_data = array('error' => 'Access Denied');
		$this->app->render('access-denied.tmp.html', $error_data, 401);  // Return 401 Access Denied
		exit;
    }
}