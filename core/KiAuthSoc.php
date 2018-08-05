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



class KiAuthSoc {
	static $socIconsA= [
		\Social\Type::VK=> 'https://vk.com/images/safari_152.png',
		\Social\Type::MR=>	'',
		\Social\Type::FB=>	'',
		\Social\Type::GITHUB=>	'',
		\Social\Type::TWITTER=>	''
	];

	private static $sessionToken='socAuth_token', $sessionStamp='socAuth_stamp', $sessionTimeout=10, $cbName;

	private $token, $factory, $typesA=[];
	var $error=null, $type=0, $id=0, $firstName='', $photoUrl='';



/*
Init fabric for social logon.

_settings: 
	List of available services.
*/
	function __construct($_settings){
		self::$cbName= getA($_settings,'CB');

		$this->typesA= $this->packSettings($_settings);
		$this->factory= new \Social\Factory($this->typesA);
	}



/*
Check if social session is valid.
While logged, calls within Timeout are treated as successfull. That should remove unneccessary freezing for frequent calls.
! False-positive logon will occur within Timeout, if user was forced to be logged off at different place.
*/
	function start(){
		$this->token= getA($_SESSION, self::$sessionToken);
		if (!$this->token)
			return;

		$this->type= $this->token->getType();
		$this->id= $this->token->getIdentifier();

		$stamp= getA($_SESSION, self::$sessionStamp, 0);
		if (time()-$stamp>self::$sessionTimeout){
			if (!$this->fetch())
				return;

	   		$_SESSION[self::$sessionStamp]= time();
		}

		return True;
	}



/*
Actually fetch user data from social.
*/
	function fetch(){
	    $api= $this->factory->createApi($this->token);
		$user= $api->getProfile();

	    if (!$user){
	        $this->error= $api->getError();
			return;
		}

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

			$urlA[$type]= [
				'url'=>	$url,
				'icon'=>	self::$socIconsA[$type]
			];
		}
	
		return $urlA;
	}



/*
Form auth URL for given type.
*/
	function socialURL($_type){
		return (KiUrl::https()?'https':'http') .'://'. KiUrl::server() .'/'. self::$cbName ."?type=$_type";
	}



/*
Callback function for social logons.
*/	
	function socCB(){
	    $type = KiUrl::args()->type;
	    $auth = $this->factory->createAuth($type);
	    $this->token = $auth->authenticate(
	    	KiUrl::args()->all(),
	    	$this->socialURL($type)
	    );

	    if (!$this->token) {
	        return $auth->getError();
	    }

	    $_SESSION[self::$sessionToken] = $this->token;

		$this->type= $this->token->getType();
		$this->id= $this->token->getIdentifier();
	}



/*
Logout for social logon
*/
	function logout(){
   		$_SESSION[self::$sessionToken] = array();
   		$_SESSION[self::$sessionStamp] = 0;
	}

}

?>
