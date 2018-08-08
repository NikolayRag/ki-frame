<?
/*
Error, exception and shutdown handler class.
Set headers, fills page content, and call user defined error callbacks.


__construct()
	Assign handlers


errCB($_CB)
	add callback used at shutdown
	$_CB
		function($errPoolA) dumping callback called at shutdown;
		$errPoolA array of all collected errors will be passed in,
			right after err500 handler.


countErrors($_countErrors=true, $_countXcption=true)
	return count of errors or exceptions

	$_countErrors
	$_countXcption
		flag indicating to count errors or exceptions respectively

*/


class KiHandler {
	private static $isInited;

	private static $errPoolA= [], $errCBA= []; //callbacks

	private static $debug, $doClean, $returnCode, $headersA=[], $contentA=[], $orderA=[];



	function __construct(){
		if (self::$isInited)
			return;
		self::$isInited= True;


		self::setCode(200);

		ob_start();

		//Suppress showing all errors implicitely
		ini_set("display_errors", "0");
		error_reporting(0);

		set_error_handler('KiHandler::hError');
		set_exception_handler('KiHandler::hException');
		register_shutdown_function('KiHandler::hShut');
	}



/*
Capture runtime error.
*/
	static function hError($_errCode, $_errMessage, $_errFile, $_errLine, $_vars) {
		self::$errPoolA[]= [
			'type'=> $_errCode,
			'message'=> $_errMessage,
			'file'=> $_errFile,
			'line'=> $_errLine,
			'etype'=> 1
		];
	}



/*
Capture runtime exception.
*/
	static function hException($_exception) {
		self::$errPoolA[]= [
			'type'=> $_exception->getCode(),
			'message'=> $_exception->getMessage(),
			'file'=> $_exception->getFile(),
			'line'=> $_exception->getLine(),
			'etype'=> 2
		];
	}



/*
Finalize output and react on errors.
Gather fatal error if any, override code to 500 and run user defined error handlers if any.
*/
	static function hShut() {
		if (self::$doClean)
			ob_get_clean();


		//finalize fatal error (only last one)
		$lastErr = error_get_last();
		if ($lastErr)
			self::$errPoolA[] = $lastErr;


		//allow final debug
		if (self::$debug){
			ini_set("display_errors", "1");
			error_reporting(E_ALL);
		}


		//Have any errors, run custom handlers.
		//Some ov content context may be substituted with contentSet.
		//Notice response code may be changed very finally!
		if (count(self::$errPoolA)){
			self::setCode(500);

			foreach (self::$errCBA as $cCB)
				call_user_func($cCB, self::$errPoolA);
		}


		self::outHeaders();
		self::outContent();
	}



/*
Add user defined error callback function.
Notice its own errors would not be handled in any way.
*/
	static function errCB($_CB){
		if (!is_callable($_CB))
			return;

		self::$errCBA[]= $_CB;

		return True;
	}



/*
Add contents
*/
	static function contentSet($_ctx, $_value){
		self::$contentA[$_ctx] = $_value;
	}



/*
Add contents
*/
	static function contentOrder($_order){
		self::$orderA = $_order;
	}



/*
Set custom header
*/
	static function setHeader($_name, $_value){
		self::$headersA[$_name] = $_value;
	}



/*
Set page ruturn code.
*/
	static function setCode($_code) {
		self::$returnCode = $_code;
	}



/*
Set page ruturn code.
*/
	static function setClean($_clean) {
		self::$doClean = $_clean;
	}



/*
Show errors caused by custom error handlers.
*/
	static function setDebug($_debug) {
		self::$debug = $_debug;
	}



	static function countErrors($_countErrors=true, $_countXcption=true) {
		$cnt= 0;

		foreach (self::$errPoolA as $cErr){
			if ($_countErrors && $cErr['etype']==1)
				$cnt++;

			if ($_countXcption && $cErr['etype']==2)
				$cnt++;
		}

		return $cnt;
	}



	private static function outHeaders() {
		foreach (self::$headersA as $hName=>$hValue)
			header("$hName: $hValue");

		http_response_code(self::$returnCode);
	}



	private static function outContent() {
		$collectOutA = [];
		foreach (self::$orderA as $cCtx)
			$collectOutA[] = self::$contentA[$cCtx];
		echo implode('', $collectOutA);
	}
}

new KiHandler(true);

?>
