<?
/*
Root framework class.
It is singletone, so all it's methods are static.

Every framework usable function is accessed from KiFrame shortcut methods.

All functions are divided by function groups, main of which are:
	- routing matrix, URL, error handling
	- authorization, social logon, right
	- support, constants, db, dictionary, etc.


There're 3 areas of actual code generation:
1. Direct echo(), print_r(), and so on, used up from index.php root file. This output will be wiped out by default, till hSetDebug(?,True) is specified.
2. Code, files and generator functions provided for routing with r*()
3. Direct code generation and routing context redefinitions, run at custom error handlers, if any. This can override all previously generated code.
*/
class KiFrame {
	private static $isInited, $startTime;



	function __construct(){
		if (self::$isInited)
			return;
		self::$isInited= True;


		self::$startTime= microtime(true);


		include(__dir__ .'/KiHandler.php');

		include(__dir__ .'/init_errorh.php');
		//general error callback (to file)
		KiHandler::errCB(ErrCB\errCBFile(__dir__ .'/../log/log.txt' ));


		if (!isset($_SESSION) && !headers_sent())
			session_start();


		include(__dir__ .'/support/general.php');
		include(__dir__ .'/support/LooseObject.php');


		include(__dir__ .'/KiConst.php');
		include(__dir__ .'/KiRoute.php');


		include(__dir__ .'/ki-sql.php');
		include(__dir__ .'/ki-dict.php');

		include(__dir__ .'/KiUrl.php');
		include(__dir__ .'/KiAgent.php');
		include(__dir__ .'/KiAuth.php');
	}






//=====================================================================//
//============================= CONSTANTS =============================//
//=====================================================================//



/*
Return all constant.
If ctx specified, only ctx context variables are returned.
*/
	static function cDump($_ctx=false){
		return KiConst::___dump($_ctx);
	}






//===================================================================//
//============================= ROUTING =============================//
//===================================================================//



/*
Register routing context, shortcut for KiRoute::context()

Notice!
If only one argument given, it will be $_src, not $_ctx.
Context is assumed then to be ''.
This is useful for light one-page setups.
*/
	static function rReg($_ctx, $_src=false){
		if (func_num_args()==1){
			$_src= $_ctx;
			$_ctx= '';
		}

		return KiRoute::context($_ctx, $_src);
	}



/*
Add context to URL, shortcut for KiRoute::bind()
*/
	static function rBind($_url, $_ctx, $_code=0, $_headersA=[]){
		return KiRoute::bind($_url, $_ctx, $_code, $_headersA);
	}



/*
Order contexts, shortcut for KiRoute::order()
*/
	static function rOut($_ctxA=False){
		return KiRoute::order($_ctxA);
	}



/*
Finalize definition and , shortcut for KiRoute::render()
*/
	static function end(){
		return KiRoute::render();
	}






//===================================================================//
//============================= HANDLER =============================//
//===================================================================//



	static function hErrCB($_CB){
		KiHandler::errCB($_CB);
	}
	static function hSetHeader($_name, $_value){
		KiHandler::setHeader($_name, $_value);
	}
	static function hSetReturn($_code){
		KiHandler::setReturn($_code);
	}
	static function hSetDebug($_debug, $_clean){
		KiHandler::setDebug($_debug, $_clean);
	}
	static function hCountErrors($_countErrors=true, $_countXcption=true){
		KiHandler::countErrors($_countErrors=true, $_countXcption=true);
	}






//===============================================================//
//============================= URL =============================//
//===============================================================//



	static function uPath(){
		return KiUrl::path();
	}



	static function uArgs(){
		return KiUrl::args();
	}



	static function uServer(){
		return KiUrl::server();
	}



	static function uHttps(){
		return KiUrl::https();
	}






//=================================================================//
//============================= AGENT =============================//
//=================================================================//



	static function aBrowser() {
		return KiAgent::browser();
	}



	static function aKnown() {
		return KiAgent::isKnown();
	}



	static function aDefault() {
		return KiAgent::isDefault();
	}



	static function aBot() {
		return KiAgent::isBot();
	}






//===================================================================//
//============================= SUPPORT =============================//
//===================================================================//



/*
Return seconds since very start.
*/
	static function lifetime($_digits=3){
		$mult= pow(10, $_digits);
		return round((microtime(true) -self::$startTime)*$mult)/$mult;
	}

}



class_alias('KiFrame', 'KF');

new KiFrame();
?>