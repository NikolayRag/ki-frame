<?
/*
Root framework class.
It is singletone, so all it's methods are static.

Every framework usable function is accessed from KiFrame shortcut methods.

All functions are divided by function groups, main of which is routing matrix.
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
		self::hErrCB(ErrCB\errCBFile(__dir__ .'/../log/log.txt' ));


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



return;
		$dbCfg= KC::DBCFG();
		$DB = new PDO("mysql:host={$dbCfg->HOST};dbname={$dbCfg->NAME};charset=utf8", $dbCfg->USER, $dbCfg->PASS, array(PDO::ATTR_PERSISTENT=>true));
		$DB->exec("set names utf8");

		//additional error callback (to DB,table)
		self::hErrCB(ErrCB\errCBDB($DB, 'site_log_errors'));


		$USER= new KiAuth($DB, KC::SOCIAL());
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
Register URL default return code and headers, shortcut to KiRoute::bind().
*/
	static function rBind($_url, $_code=200, $_headersA=[], $_priority=1){
		KiRoute::bind($_url, $_code, $_headersA, $_priority);
	}



/*
Add context to URL, shortcut for KiRoute::bindCtx()


Notice!
If only one arument is specified, it is assumed to be $_ctx, while URL matches any.
*/
	static function rAdd($_url, $_ctx=False){
		if (func_num_args()==1){
			$_ctx= $_url;
			$_url= '.*';
		}

		return KiRoute::bindCtx($_url, $_ctx);
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
	static function hContentSet($_ctx, $_value){
		KiHandler::contentSet($_ctx, $_value);
	}
	static function hContentOrder($_order){
		KiHandler::contentOrder($_order);
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