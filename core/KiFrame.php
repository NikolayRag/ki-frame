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

	private static $dictO;



	static function init(){
		if (self::$isInited)
			return;
		self::$isInited= True;


		self::$startTime= microtime(true);

//initial handlers
		include(__dir__ .'/KiHandler.php');
		include(__dir__ .'/KiError.php');
		//general error callback (to file)
		KiHandler::errCB(KiError::errCBFile(__dir__ .self::ERROR_FILE));

//support
		include(__dir__ .'/support/general.php');
		include(__dir__ .'/support/LooseObject.php');
		include(__dir__ .'/KiConst.php');
		include(__dir__ .'/KiUrl.php');
		include(__dir__ .'/KiSql.php');

//core
		include(__dir__ .'/KiRoute.php');
		include(__dir__ .'/KiAuth.php');

//extentions
		include(__dir__ .'/KiAgent.php');
		include(__dir__ .'/KiDict.php');


		self::$dictO = new KiDict();

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
Assign named context to some code-generating routines.
Several routines may be assigned with same context, that will come out they result will be placed right one after another.
Order for multiple same-context code is the same as they were declared.


$_ctx
	String for context to be named.
	Can be omited to make unnamed context object,
	 used when explicitely bond.


$_src
	Array or one of three: function, filename, string.

	Function is called to generate content, provided with matched bindings variables.
	If existing .php filename is given instead of function, it's imported.
	Otherwise, provided string is embedded as is.

	Function provided to context() return response data.
	Anything other than string returned treated as error and ignored in output.
*/
// =todo 137 (context) +0: allow to use unnamed contexts
	static function rCode($_ctx, $_src=False){
		if ($_src===False)
			return new KiRouteCtx($_ctx); //subst src

		return KiRouteCtx::add($_ctx, $_src);
	}



/*
Add context and headers to URL and set return code.
Different contexts may be bond to one URL, as well as one context may be bond to number of URLs.
All contexts for all matching URLs will be used without concurrency.

If nothing is bound at all, the only implicit assignment is '/' URL to '' context (root to default).
If nothing is bound to '' (404 case), it will implicitely be assigned to blank page with 404 return code. If '' url is bound, 404 return code must be then set explicitely.


$_url
	Match function, URL match regex, or static value.
	Array accepted, where ALL elements must match.

	If function supplied returns non-strict False, then there's no match. Any other return value triggers match.
	If array is returned, it's passed as 'variables' argument to bond context functions, if any.
	
	String regexp passed for match URI, starting either with unescaped '/' or '?'. Unescaped '/' are also allowed anywhere in regex.
	If started with '/', URL path is matched. Path string to match is everything after server name, starting with '/', and without arguments.
	If started with '?', arguments are matched. Any successfull match counts. WRONG useage: matching several arguments at once will fail constantly, like '?a=1&b=1'. Use several matches instead: [.., '?a=1', '?b=1']. 
	Named capture (?P<name>value) is allowed to scan variables. Captured value is passed then within named array into bond context functions, if any. Matching several different named variables within one binding will pass all of them as arguments. When regex wide mask matches several URL arguments, only first match defines variable=>value pair.
	Tricky regex matches like "/(?!foo$).*" (all but '/foo') are fully allowed.

	Variables matched are accessed at runtime by ::contextData()


	If first (or only) value specified is 404, match is used in case of no 'normal' matches found.
	Notice, that if there any wide mask bound match, like '.*' or True, it could become impossible to catch 'not found' case at all. 'Not found' binding for this case can be matched by using patterns like "^(?!.foo$)".


$_ctx
	Context added to specified URL.
	May be string or array of contexts.


$_code
	Default HTTP return code.
	Return code have priority over any other defined one.


$_headersA
	Default custom return headers array.
*/
	static function rBind($_url, $_ctx, $_code=0, $_headersA=[]){
		return new KiRouteBind($_url, $_ctx, $_code, $_headersA);
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


		$dbCfg= new LooseObject(KC::DBCFG());
		KiSql::init($dbCfg->HOST, $dbCfg->NAME, $dbCfg->USER, $dbCfg->PASS);

		//additional error callback (to DB table)
		KiHandler::errCB(KiError::errCBDB(self::ERROR_SQL_TABLE));

// -todo 78 (clean, ux) +0: allow social-only and no-auth case
		KiAuth::init(new LooseObject(KC::SOCIAL()));


		return KiRoute::render($_ctxOrder);
	}



/*
Get matched bindings variables at context runtime.
*/
	static function runtime(){
		return KiRouteCtx::runtime();
	}


//=================================================================//
//============================= LOGON =============================//
//=================================================================//



/*
Current active user, logged or not.
*/
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

$_bind
	Set to bind logpass to current autosocial user.
*/
	static function lReg($_email, $_pass, $_bind=False){
		return KiAuth::passRegister($_email, $_pass, $_bind);
	}
/*
Login user with login/pass. Shortcut for KiAuth passLogin().

$_bind
	Set to bind logpass to current autosocial user.
*/
	static function lIn($_email, $_pass, $_bind=False){
		return KiAuth::passLogin($_email, $_pass, $_bind);
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



	static function uAlias($_path,$_args=True){
		return KiUrl::alias($_path,$_args);
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



/*
Global dictionary shortcut.
*/
	static function dict(){
		return self::$dictO;
	}
}



class_alias('KiFrame', 'KF');

KiFrame::init();
?>