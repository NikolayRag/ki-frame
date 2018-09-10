<?

/*
URL parser.
Detect and organize request elements: path, GET and POST variables, server name, http scheme.
*/

class KiUrl {
	const GET=1, POST=2, PUT=3, DELETE=4;

	static private $isInited;
	static private $vMethod, $vUri, $vPath, $vArgs, $vServer, $isHttps;



/*
Get request method.

asStr
	if non-false specified, return method string instead of constant.
*/
	static function method($_asStr=False){
		self::init();

		if ($_asStr)
			return self::$vMethod;
			
		switch (self::$vMethod){
			case 'GET':
				return self::GET;
			case 'POST':
				return self::POST;
			case 'PUT':
				return self::PUT;
			case 'DELETE':
				return self::DELETE;
		}
	}



/*
Get request string, always started with '/'.
*/
	static function url(){
		self::init();

		return self::$vUri;
	}



/*
Get path array.
*/
	static function path($_asStr=False){
		self::init();

		if ($_asStr)
			return '/' . implode('/', self::$vPath);

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


		self::$vMethod = $_SERVER['REQUEST_METHOD'];


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
