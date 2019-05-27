<?php
/*
Social logon wrap
*/

include(__dir__ . '/../_3rd/php-social/lib/Social/Auth/Token.php');

class KiAuthSoc {
	const 
		SessTimeout=10,
		SessToken='socAuth_token',
		SessStamp='socAuth_stamp',
		SessData='socAuth_data';


	static $socIconsA;

	private static $cbName;

	private static $isInited, $token, $factory, $typesA=[];
	static $error=null, $type=0, $id=0, $liveName='', $livePhoto='';



/*
Init fabric for social logon.

_settings: 
	List of available services.
*/
	static function init($_settings){
		if (self::$isInited)
			return;
		self::$isInited = True;


		spl_autoload_register(
		    function ($class) {
				$cClassA= explode('\\', $class);
				if ($cClassA[0]!='Social')
					return;
				
				array_shift($cClassA);

		        $baseDir = __DIR__ . '/../_3rd/php-social/lib/Social';
		        $path = $baseDir . '/' . implode('/', $cClassA) . '.php';

		        if (is_file($path)) {
		            require $path;
		        }
		    }
		);
		
		include(__dir__ . '/../_3rd/php-social/lib/Social/Type.php');
		include(__dir__ . '/../_3rd/php-social/lib/Social/Factory.php');


		self::$socIconsA = [
			\Social\Type::VK=> 'https://vk.com/images/safari_152.png',
			\Social\Type::MR=>	'',
			\Social\Type::FB=>	'',
			\Social\Type::GITHUB=>	'',
			\Social\Type::TWITTER=>	''
		];

		self::$cbName= $_settings->CB;

		self::$typesA= self::packSettings($_settings);
		self::$factory= new \Social\Factory(self::$typesA);
	}



/*
Check if social session is valid.
While logged, calls within Timeout are treated as successfull. That should remove unneccessary freezing for frequent calls.
! False-positive logon will occur within Timeout, if user was forced to be logged off at different place.
*/
	static function start(){
		self::$token= getA($_SESSION, self::SessToken);
		if (!self::$token || !(self::$token instanceof Social\Auth\Token))
			return;

		self::$type= self::$token->getType();
		self::$id= self::$token->getIdentifier();

		return self::fetch();
	}



/*
Actually fetch user data from social.
*/
	static function fetch(){
		$stamp = getA($_SESSION, self::SessStamp, 0);
		if (time()-$stamp>self::SessTimeout){

		    $api = self::$factory->createApi(self::$token);
			$user = $api->getProfile();

		    if (!$user){
		        self::$error = $api->getError();
				return;
			}


			$_SESSION[self::SessData] = [
				'liveName' => $user->firstName,
				'livePhoto' => $user->photoUrl
			];


	   		$_SESSION[self::SessStamp] = time();
		}


		$data = $_SESSION[self::SessData];

		self::$liveName = $data['liveName'];
		self::$livePhoto = $data['livePhoto'];

		return true;
	}


/*
Parse settings, excluding blank ones.
*/
	static function packSettings($_settings){
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
	static function loginURL(){
		$urlA= [];

		foreach (self::$typesA as $type=>$v){
			$auth= self::$factory->createAuth($type);
			$url= $auth->getAuthorizeUrl(self::socialURL($type));


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
	static function socialURL($_type){
		return (KiUrl::https()?'https':'http') .'://'. KiUrl::server() .'/'. self::$cbName ."?type=$_type";
	}



/*
Callback function for social logons.
*/	
	static function socCB($_type, $_args){
	    $auth = self::$factory->createAuth($_type);
	    self::$token = $auth->authenticate(
	    	$_args,
	    	self::socialURL($_type)
	    );

	    if (!self::$token) {
	        return $auth->getError();
	    }

	    $_SESSION[self::SessToken] = self::$token;

		self::$type= self::$token->getType();
		self::$id= self::$token->getIdentifier();

		self::fetch();
	}



/*
Logout for social logon
*/
	static function logout(){
   		$_SESSION[self::SessToken] = array();
   		$_SESSION[self::SessStamp] = 0;
	}


}
?>
