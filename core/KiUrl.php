<?

/*
URL parser.
Detect and organize request elements: path, GET and POST variables, server name, http scheme.
*/

class KiUrl {
	const GET=1, POST=2, PUT=3, DELETE=4;

	static private $isInited;
	static private $vMethod, $vPath, $vArgs, $vServer, $isHttps;



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
Get path array.
*/
	static function path($_asStr=False){
		self::init();

		if ($_asStr)
			return self::$vPath;

		return array_slice(
			explode("/", self::$vPath), 1
		);
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


		$urlA = explode("?", "/${_SERVER["REQUEST_URI"]}", 2);
		self::$vPath = preg_replace('[/+]', '/', $urlA[0]);


		self::$vArgs = new LooseObject();

		foreach ($_POST as $pName=>$pVal)
			self::$vArgs->$pName = $pVal;

		//Fill vArgs
		if (isset($urlA[1]))
		  foreach(explode("&",$urlA[1]) as $x){
			$xSpl = explode("=",$x);
			$get = isset($xSpl[1])? urldecode($xSpl[1]) :False;

			$_REQUEST[$xSpl[0]] =
			$_GET[$xSpl[0]] =
			self::$vArgs->$xSpl[0] =
				$get;
		  }
	}



/*
Substitute current URL path and parameters with different values for use as "current" URI and arguments for all further KiUrl operations.
New values for GET will later be returned by ->path(True) call, while both substituted values for GET and POST will form ->args() return.
Notice: none of system variables are altered, like $_REQUEST, $_GET, $_SERVER["REQUEST_URI"] etc.


$_newPath
	New path string.


//  todo 163 (url) +0: provide alias() with '*' argument instead of True
$_newArgs
	New arguments collection.
	If value provided is True, all current arguments are reused. Every explicitely passed name=>value pair have higher priority. Use False named value to unset parameter.
*/
	static function alias($_newPath, $_newArgs=True){
		if (!is_string($_newPath))
			$_newPath = '/';

		if ($_newPath[0]!='/')
			$_newPath = "/$_newPath";
			
		self::$vPath = $_newPath;


		//args

		if (!is_array($_newArgs))
			$_newArgs = [$_newArgs];


		$newArgsA = [];
		foreach ($_newArgs as $v)
			if ($v===True){ //reuse all current
				foreach (self::$vArgs->all() as $n=>$v)
					$newArgsA[$n] = $v;

				break;
			}

		foreach ($_newArgs as $n=>$v){
			if ($v===False)
				unset($newArgsA[$n]);

			else if ($v!==True)
				$newArgsA[$n] = $v;
		}

		self::$vArgs = new LooseObject($newArgsA);
	}
}


?>
