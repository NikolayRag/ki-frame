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
	static $socIconsA= [
		\Social\Type::VK=> 'https://vk.com/images/safari_152.png',
		\Social\Type::MR=>	'',
		\Social\Type::FB=>	'',
		\Social\Type::GITHUB=>	'',
		\Social\Type::TWITTER=>	''
	];

	private $sessionName='socAuth', $cbName;

	private $factory, $typesA=[];
	var $error=null, $type=0, $id=0, $firstName='', $photoUrl='';



/*
Init fabric for social logon.

_settings: 
	List of available services.
*/
	function __construct($_settings){
		$this->cbName= getA($_settings,'CB');

		$this->typesA= $this->packSettings($_settings);
		$this->factory= new \Social\Factory($this->typesA);
	}



/*
Check if logged in any available service.
*/
	function start(){
        if (!isset($_SESSION) && !headers_sent()) {
			session_start();
		}

		if (!getA($_SESSION, $this->sessionName))
			return;

	    $api= $this->factory->createApi($_SESSION[$this->sessionName]);
		$user= $api->getProfile();

	    if (!$user){
	        $this->error= $api->getError();
			return;
		}

		$this->type= $_SESSION[$this->sessionName]->getType();
		$this->id= $user->id;
		$this->firstName= $user->firstName;
		$this->photoUrl= $user->photoUrl;

		return true;
	}


/*
Parse settings, excluding blank ones.
*/
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
Fill authorisation URL's list for available services.
*/
	function loginURL($_url){
		$urlA= [];

		foreach ($this->typesA as $type=>$v){
			$auth= $this->factory->createAuth($type);
			$url= $auth->getAuthorizeUrl($this->socialURL($type, $_url));


			switch ($type){
				case \Social\Type::VK: {
					$url.= '&revoke=1';
					break;
				}
			}

			$urlA[$type]= [
				'url'=>	$url,
				'icon'=>	KiSoc::$socIconsA[$type]
			];
		}
	
		return $urlA;
	}



/*
Form auth URL for given type.
*/
	function socialURL($_type, $_url){
		return ($_url->https?'https':'http') ."://{$_url->server}/{$this->cbName}?type=$_type";
	}



/*
Callback function for social logons.

$_req
	$_REQUEST passed in.
*/	
	function socCB($_url){
	    $type = $_url->args->type;
	    $auth = $this->factory->createAuth($type);
	    $token = $auth->authenticate(
	    	$_url->args->all(),
	    	$this->socialURL($type, $_url)
	    );

	    if (!$token) {
	        return $auth->getError();
	    }

	    $_SESSION[$this->sessionName] = $token;

		$this->type= $token->getType();
		$this->id= $token->getIdentifier();
	}



/*
Logout for social logon
*/
	function logout(){
		if (isset($_SESSION[$this->sessionName])) {
    		$_SESSION[$this->sessionName] = array();
		}
	}

}

?>
