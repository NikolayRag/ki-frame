<?php
/*
Social logon wrap
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



class KiSoc {
	private $sessionName='socAuth', $cbName;

	private $factory, $typesA=[];
	var $error=null, $user=null;



	function __construct($_settings, $_cb='logoncb'){
		$this->cbName= $_cb;

		$this->typesA= $this->packSettings($_settings);
		$this->factory= new \Social\Factory($this->typesA);
	}



	function start(){
        if (!isset($_SESSION) && !headers_sent()) {
			session_start();
		}

		if (!getA($_SESSION, $this->sessionName))
			return;

	    $api = $this->factory->createApi($_SESSION[$this->sessionName]);
	    $this->user = $api->getProfile();

	    if (!$this->user) {
	        $this->error = $api->getError();
	        return;
	    }

	    return true;
	}



	function packSettings($_settings){
		$typesA= [];

		if ($_settings->VKID){
		    $typesA[\Social\Type::VK] = [
		        'app_id' => $_settings->VKID,
		        'secret_key' => $_settings->VKKEY,
		        'scope' => $_settings->VKSCOPE
		    ];
		}
		if ($_settings->MRID){
		    $typesA[\Social\Type::MR] = [
		        'app_id' => $_settings->MRID,
		        'secret_key' => $_settings->MRKEY,
		        'scope' => $_settings->MRSCOPE
		    ];
		}
		if ($_settings->FBID){
		    $typesA[\Social\Type::FB] = [
		        'app_id' => $_settings->FBID,
	        	'secret_key' => $_settings->FBKEY,
		        'scope' => $_settings->FBSCOPE
		    ];
		}
		if ($_settings->GITID){
		    $typesA[\Social\Type::GITHUB] = [
		        'app_id' => $_settings->GITID,
		        'secret_key' => $_settings->GITKEY,
		        'scope' => $_settings->GITSCOPE
		    ];
		}
		if ($_settings->TWID){
		    $typesA[\Social\Type::TWITTER] = [
		        'app_id' => $_settings->TWID,
		        'secret_key' => $_settings->TWKEY
		    ];
		}

		return $typesA;
	}



/*
fill auth URL's
*/
	function loginURL(){
		$urlA= [];

		foreach ($this->typesA as $type=>$v){
			$auth= $this->factory->createAuth($type);
			$url= $auth->getAuthorizeUrl($this->socialURL($type));


			switch ($type){
				case \Social\Type::VK: {
					$url.= '&revoke=1';
					break;
				}
			}

			$urlA[$type]['url']= $url;
		}
	
		return $urlA;
	}




	function socialURL($_type){
		$protocol= getA($_SERVER, 'HTTPS')? 'https' : 'http';
		return "$protocol://{$_SERVER['SERVER_NAME']}/{$this->cbName}?type=$_type";
	}



/*
Callback function for social logons.

$_req
	$_REQUEST passed in.
*/	
	function socCB($_req){
	    $type = $_req->type;
	    $auth = $this->factory->createAuth($type);
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
