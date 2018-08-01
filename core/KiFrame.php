<?
class KiFrame {
	private static $isInited, $startTime;



	function __construct(){
		if (self::$isInited)
			return;
		self::$isInited= True;


		self::$startTime= microtime(true);


		include(__dir__ .'/KiError.php');
		$ERRR= new KiError(true);

		include(__dir__ .'/init_errorh.php');
		//general error callback (to file)
		$ERRR->errCB(ErrCB\errCBFile(__dir__ .'/../log/log.txt' ));


		include(__dir__ .'/support/general.php');
		include(__dir__ .'/support/LooseObject.php');


		include(__dir__ .'/KiConst.php');

		include(__dir__ .'/../private/c_core.php');
		include(__dir__ .'/../private/c.php');



		include(__dir__ .'/ki-sql.php');
		include(__dir__ .'/ki-dict.php');

		include(__dir__ .'/ki-url.php');
		include(__dir__ .'/ki-client.php');
		include(__dir__ .'/ki-auth.php');

		if (!isset($_SESSION) && !headers_sent())
			session_start();
/*
		$DB = new PDO("mysql:host={$DBCFG->HOST};dbname={$DBCFG->NAME};charset=utf8", $DBCFG->USER, $DBCFG->PASS, array(PDO::ATTR_PERSISTENT=>true));
		$DB->exec("set names utf8");

		//additional error callback (to DB,table)
		$ERRR->errCB(ErrCB\errCBDB($DB, 'site_log_errors'));


		$URL= new KiURL($URI_ALLOW);


		$USER= new KiAUTH($DB, $SOCIAL, $URL);
*/
	}






//=====================================================================//
//============================= CONSTANTS =============================//
//=====================================================================//



/*
Set constant variable to given value and context.
Get constant variable.
*/
	static function c($_name, $_val=false, $_ctx=0){
		if (func_num_args()>1)
			return KiConst::add($_name, $_val, $_ctx);
		else
			return KiConst::get($_name);
	}



/*
Return all constant.
If ctx specified, only ctx context variables are returned.
*/
	static function cDump($_ctx=false){
		return KiConst::dump($_ctx);
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
Bind context to URL.

Different contexts may be bond to one URL, as well as one context may be bond to number of URLs.


$_ctx
	Context assigned to specified URL.


$_url
	URL string to match.
	If url is True (default), it is assumed to be any URL at all.
	If url is False, route matches 404.

*/
	static function r($_ctx, $_url=True){
		return KiRoute::bind($_ctx, $_url);
	}



/*
Order contexts, shortcut for KiRoute::order()
*/
	static function rOut($_ctxA=False){
		return KiRoute::order($_ctxA);
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



new KiFrame();
class_alias('KiFrame', 'KF');
?>