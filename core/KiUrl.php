<?

/*
URL parser.
Detect and organize request elements: path, GET and POST variables, server name, http scheme.
*/

class KiUrl {
	const GET=1, POST=2;

	static private $isInited;
	static private $vMethod, $vUri, $vPath, $vArgs, $vServer, $isHttps;



/*
Get request method.
*/
	static function method(){
		self::init();

		return self::$vMethod;
	}



/*
Get request string, always started with '/'.
*/
	static function uri(){
		self::init();

		return self::$vUri;
	}



/*
Get path array.
*/
	static function path(){
		self::init();

		return self::$vPath;
	}



/*
Get variables array, including both GET and POST.
*/
	static function args($_arg=False){
		self::init();

		if (!$_arg)
			return self::$vArgs;

		return self::$vArgs->$_arg;
	}



/*
Get requested server name.
*/
	static function server(){
		self::init();

		return self::$vServer;
	}



/*
Get HTTPS flag.
*/
	static function https(){
		self::init();

		return self::$isHttps;
	}



	static private function init(){
		if (self::$isInited)
			return;
		self::$isInited = True;


		switch ($_SERVER['REQUEST_METHOD']) {
			case 'GET':
				self::$vMethod = self::GET; break;
			case 'POST':
				self::$vMethod = self::POST; break;
		}


		self::$isHttps =
			getA($_SERVER, 'HTTPS') ||
			getA(json_decode(getA($_SERVER, 'HTTP_CF_VISITOR')),'scheme')=='https'; //cloudflare https flag
	

		self::$vServer = $_SERVER['SERVER_NAME'];


		self::$vArgs = new LooseObject();

		foreach ($_POST as $pName=>$pVal)
			self::$vArgs->$pName = $pVal;

		self::$vUri = preg_replace('[/+]', '/', "/${_SERVER["REQUEST_URI"]}");
		$uriA = explode("?", self::$vUri);

		//Fill vArgs
		if (isset($uriA[1]))
		  foreach(explode("&",$uriA[1]) as $x){
			$xSpl = explode("=",$x);
			$get = isset($xSpl[1])? urldecode($xSpl[1]) :False;

			$_REQUEST[$xSpl[0]] =
			$_GET[$xSpl[0]] =
			self::$vArgs->$xSpl[0] =
				$get;
		  }


		self::$vPath = array_slice(
			explode("/", $uriA[0]), 1
		);

	}
}


?>
