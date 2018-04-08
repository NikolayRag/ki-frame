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
	var $sessionName, $cbName;


	var $socialFact, $socialA=[];

	var $socError, $socUser;


	function __construct($_settings, $_session='socAuth', $_cb='socialcb'){
		$this->sessionName= $_session;
		$this->cbName= $_cb;

		session_start();


		$this->socialFactory($_settings);

		$this->socialFillURL();
	}



	function socialFactory($_settings){
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

		$this->socialFact = new \Social\Factory($this->socialA);
	}



/*
fill auth URL's
*/
	function socialFillURL(){
		foreach ($this->socialA as $type=>$v){
			$auth= $this->socialFact->createAuth($type);
			$url= $auth->getAuthorizeUrl($this->socialURL($type));


			switch ($type){
				case \Social\Type::VK: {
					$url.= '&revoke=1';
					break;
				}
			}

			$this->socialA[$type]['url']= $url;
		}
	
	}




	function socialURL($_type){
		$protocol= getA($_SERVER, 'HTTPS')? 'https' : 'http';
		return "$protocol://{$_SERVER['SERVER_NAME']}/{$this->cbName}?type=$_type";
	}



/*
Init social logon token.
*/
	function socInit(){
		$this->socError=null;
		$this->socUser=null;

		if (!isset($_SESSION[$this->sessionName]) or !$_SESSION[$this->sessionName])
				return;


	    $api = $this->socialFact->createApi($_SESSION[$this->sessionName]);
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
	    $auth = $this->socialFact->createAuth($type);
	    $token = $auth->authenticate(
	    	$_req->all(),
	    	$this->socialURL($type)
	    );

	    if (!$token) {
	        return $auth->getError();
	    }

	    $_SESSION[$this->sessionName] = $token;
	}


/*
Logout for social logon
*/
	function socOut(){
		if (isset($_SESSION[$this->sessionName])) {
    		$_SESSION[$this->sessionName] = array();
		}
	}

}

?>