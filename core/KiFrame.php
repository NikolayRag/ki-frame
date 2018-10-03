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
1. Direct echo(), print_r(), and so on, used up from index.php root file. This output will be wiped out by default, till debug(?,True) is specified.
2. Code, files and generator functions provided for routing with r*()
3. Direct code generation and routing context redefinitions, run at custom error handlers, if any. This can override all previously generated code.
*/
class KiFrame {
	const ERROR_SQL_TABLE = 'site_log_errors';
	const ERROR_FILE = '/../log/log.txt';

	private static $isInited, $isEnded, $startTime;



	static function init(){
		if (self::$isInited)
			return;
		self::$isInited= True;


		self::$startTime= microtime(true);


		include(__dir__ .'/KiHandler.php');

		include(__dir__ .'/init_errorh.php');
		//general error callback (to file)
		KiHandler::errCB(ErrCB\errCBFile(__dir__ .self::ERROR_FILE));

		include(__dir__ .'/support/general.php');
		include(__dir__ .'/support/LooseObject.php');


		include(__dir__ .'/KiConst.php');
		include(__dir__ .'/KiRoute.php');


		include(__dir__ .'/KiSql.php');
		include(__dir__ .'/ki-dict.php');

		include(__dir__ .'/KiUrl.php');
		include(__dir__ .'/KiAuth.php');

		include(__dir__ .'/KiAgent.php');


		if (!isset($_SESSION) && !headers_sent())
			session_start();
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
Register routing context code, shortcut for KiRoute::context()

Notice!
If only one argument given, it will be $_src, not $_ctx.
Context is assumed then to be ''.
This is useful for light one-page setups.
*/
	static function rCode($_ctx, $_src=false){
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
Finalize definition and render routing, shortcut for KiRoute::render()

_ctxOrder
	Optional context reorder.
*/
	static function end($_ctxOrder=False){
		if (self::$isEnded)
			return;
		self::$isEnded = True;


// -todo 77 (clean, ux) +0: allow no-db case
		$dbCfg= new LooseObject(KC::DBCFG());
		KiSql::init($dbCfg->HOST, $dbCfg->NAME, $dbCfg->USER, $dbCfg->PASS);

		//additional error callback (to DB table)
		KiHandler::errCB(ErrCB\errCBDB(self::ERROR_SQL_TABLE));

// -todo 78 (clean, ux) +0: allow no-auth case
		KiAuth::init(new LooseObject(KC::SOCIAL()));


		return KiRoute::render($_ctxOrder);
	}



/*
Access bond matched variables, shortcut to KiRoute::contextData().
*/
	static function rData(){
		return KiRoute::contextData();
	}



//=================================================================//
//============================= LOGON =============================//
//=================================================================//



	static function user(){
		return KiAuth::$user;
	}



/*
Define named function for checking rights later.
*/
	static function right($_name, $_fn){
		return KiRights::define($_name, $_fn);
	}



/*
Get available social login URL's. Shortcut for KiAuth socUrlA().
*/
	static function lUrls(){
		return KiAuth::socUrlA();
	}
/*
Callback for social login. Shortcut for KiAuth socCB().
*/
	static function lSocCB(){
		if (KiUrl::args()->type)
			return KiAuth::socCB(KiUrl::args()->type, KiUrl::args()->all());
	}
/*
Register new user with login/pass. Shortcut for KiAuth passRegister().
*/
	static function lReg($_email, $_pass){
		return KiAuth::passRegister($_email, $_pass);
	}
/*
Login user with login/pass. Shortcut for KiAuth passLogin().
*/
	static function lIn($_email, $_pass){
		return KiAuth::passLogin($_email, $_pass);
	}
/*
Log out logged use. Shortcut for KiAuth logout().
*/
	static function lOut(){
		return KiAuth::logout();
	}
/*
Request password reset link for registered email. Shortcut for KiAuth passRestore().
*/
	static function lRestore($_email){
		return KiAuth::passRestore($_email);
	}
/*
Set new password for registered email, using provided key. Shortcut for KiAuth passNew().
*/
	static function lPass($_email, $_pass, $_hash){
		return KiAuth::passNew($_email, $_pass, $_hash);
	}






//===================================================================//
//============================= HANDLER =============================//
//===================================================================//



	static function debug($_debug, $_clean){
		KiHandler::setDebug($_debug, $_clean);
	}






//===============================================================//
//============================= URL =============================//
//===============================================================//



	static function uMethod($_asStr=False){
		return KiUrl::method($_asStr);
	}



	static function uUrl(){
		return KiUrl::url();
	}



	static function uPath($_asStr=False){
		return KiUrl::path($_asStr);
	}



	static function uArgs($_arg=False){
		return KiUrl::args($_arg);
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



/*
Sent email.
*/
	static function sendMail(
		$_smtp, $_user, $_pass, $_email, $_from, $_subj, $_body, $_port=465
	){
		sendMail($_smtp, $_user, $_pass, $_email, $_from, $_subj, $_body, $_port);
	}
}



class_alias('KiFrame', 'KF');

KiFrame::init();
?>