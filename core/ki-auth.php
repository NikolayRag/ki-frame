<?php

/*
Deal with user authorization - social and explicit, and rights assignment

*/


spl_autoload_register(
    function ($class) {
        $baseDir = __DIR__ . '/../_3rd/php-social/lib';
        $path = $baseDir . '/' . str_replace('\\', '/', $class) . '.php';

        if (is_file($path)) {
            require $path;
        }
    }
);



class KiAUTH {
	var $sName='socAuth';


	var $socialFac, $socialA=[];

	var $socError, $socUser;


	function __construct($_settings){
		session_start();

		if ($_settings->VKID){
		    $this->socialA[\Social\Type::VK] = [
		        'app_id' => $_settings->VKID,
		        'secret_key' => $_settings->VKKEY,
		        'scope' => $_settings->VKSCOPE
		    ];
		}
		if ($_settings->MRID){
		    $this->socialA[\Social\Type::MR] = [
		        'app_id' => $_settings->MRID,
		        'secret_key' => $_settings->MRKEY,
		        'scope' => $_settings->MRSCOPE
		    ];
		}
		if ($_settings->FBID){
		    $this->socialA[\Social\Type::FB] = [
		        'app_id' => $_settings->FBID,
	        	'secret_key' => $_settings->FBKEY,
		        'scope' => $_settings->FBSCOPE
		    ];
		}
		if ($_settings->GITID){
		    $this->socialA[\Social\Type::GITHUB] = [
		        'app_id' => $_settings->GITID,
		        'secret_key' => $_settings->GITKEY,
		        'scope' => $_settings->GITSCOPE
		    ];
		}
		if ($_settings->TWID){
		    $this->socialA[\Social\Type::TWITTER] = [
		        'app_id' => $_settings->TWID,
		        'secret_key' => $_settings->TWKEY
		    ];
		}

		$this->socialFac = new \Social\Factory($this->socialA);



		//fill auth URL's
		foreach ($this->socialA as $type=>$v){
			$auth= $this->socialFac->createAuth($type);
			$url= $auth->getAuthorizeUrl($this->socialOutURL($type));


			switch ($type){
				case \Social\Type::VK: {
					$url.= '&revoke=1';
					break;
				}
			}

			$this->socialA[$type]['url']= $url;
		}
	
	}




	function socialOutURL($_type){
		return "http://{$_SERVER['SERVER_NAME']}/socialcb?type=$_type";
	}



/*
Init social logon token.
*/
	function socInit(){
		$this->socError=null;
		$this->socUser=null;

		if (!isset($_SESSION[$this->sName]) or !$_SESSION[$this->sName])
				return;


	    $api = $this->socialFac->createApi($_SESSION[$this->sName]);
	    $this->socUser = $api->getProfile();

	    if (!$this->socUser) {
	        $this->socError = $api->getError();
	    }
	}



/*
Callback function for social logons.

$_req
	$_REQUEST passed in.
*/	
	function socReact($_req){
	    $type = $_req->type;
	    $auth = $this->socialFac->createAuth($type);
	    $token = $auth->authenticate(
	    	$_req->all(),
	    	$this->socialOutURL($type)
	    );

	    if (!$token) {
	        return $auth->getError();
	    }

	    $_SESSION[$this->sName] = $token;
	}


/*
Logout for social logon
*/
	function socOut(){
		if (isset($_SESSION[$this->sName])) {
    		$_SESSION[$this->sName] = array();
		}
	}

}

?>