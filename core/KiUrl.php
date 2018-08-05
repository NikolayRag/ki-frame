<?

/*
URL parser.
Detects GET "?" parameters, path breadrolls,
and uses path[0] as identifier for requested mode.

__construct($_modeA, $_modeDefault, $_modeWrong)
	$_modeA
		'name'=>value pair array of modes for detection

	$_modeDefault
		default value for mode set if no path supplied

	$_modeWrong
		mode value if no suitable mode found in $_modeA provided

*/

class KiUrl {
	static private $isInited;
	static $vPath=[], $vArgs, $vServer, $isHttps;



	static function path(){
		self::init();

		return $vPath;
	}



	static function args(){
		self::init();

		return $vArgs;
	}



	static function server(){
		self::init();

		return $vServer;
	}



	static function https(){
		self::init();

		return $isHttps;
	}



	static private function init(){
		if (self::$isInited)
			return;
		self::$isInited = True;


		self::$isHttps =
			getA($_SERVER, 'HTTPS') ||
			getA(json_decode(getA($_SERVER, 'HTTP_CF_VISITOR')),'scheme')=='https'; 
	

		self::$vServer = $_SERVER['SERVER_NAME'];


		self::$vArgs = new LooseObject();

		foreach ($_POST as $pName=>$pVal)
			self::$vArgs->$pName = $pVal;

		$uriA = explode("?", $_SERVER["REQUEST_URI"]);


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
			explode("/", preg_replace('[/+]', '/', $uriA[0])), 1
		);

	}
}


?>
