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

	
	//callbacks
	private static
		$errPoolA= [],
		$errCBA= [];

	private static
		$debug,
		$doClean=True,
		$returnCode,
		$headersA=[],
		$contentA=[];



	function __construct(){
		if (self::$isInited)
			return;
		self::$isInited= True;


		self::setReturn(200);

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

meta:
- optionally clean all explicit output
- get fatal error if any
- optionally allow user-defined error handlers to output errors (debug)
- override code to 500 and run user-defined error handlers if any
	It is allowed to define headers and change content context data finally at this point!
- 
*/
	static function hShut() {
		//finalize fatal error (only last one)
		$lastErr = error_get_last();
		if ($lastErr)
			self::$errPoolA[] = $lastErr;


		//allow final debug
		if (self::$debug){
			ini_set("display_errors", "1");
			error_reporting(E_ALL);
		}

		if (self::$doClean)
			ob_get_clean();
//  todo 29 (unsure, flow) +0: option to remove explicitely specified headers


		//Have any errors, run custom handlers.
		//Some of content context may be substituted with setContent.
		//Notice response code may be changed very finally!
		if (count(self::$errPoolA)){
			self::setReturn(500);

			foreach (self::$errCBA as $cCB)
				try {
					call_user_func($cCB, self::$errPoolA);
				} catch (Exception $e){
					if (self::$debug)
						echo $e->getMessage() .'<br>';
				}
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
	static function setContent($_ctx, $_value){
		self::$contentA[$_ctx] = $_value;
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
	static function setReturn($_code) {
		self::$returnCode = $_code;
	}



/*
Show errors caused by custom error handlers.
Clean explicit output.
*/
	static function setDebug($_debug, $_clean) {
		self::$debug = $_debug;
		self::$doClean = $_clean;
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
		foreach (self::$contentA as $cContent)
			echo $cContent;
	}
}

new KiHandler(true);

?>
