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


		include(__dir__ .'/KiError.php');
		$ERRR= new KiError(true);

		include(__dir__ .'/init_errorh.php');
		//general error callback (to file)
		$ERRR->errCB(ErrCB\errCBFile(__dir__ .'/../log/log.txt' ));


		include(__dir__ .'/support/general.php');
		include(__dir__ .'/support/LooseObject.php');


		include(__dir__ .'/KiConst.php');
		include(__dir__ .'/KiRoute.php');

		include(__dir__ .'/../private/c_core.php');



		include(__dir__ .'/ki-sql.php');
		include(__dir__ .'/ki-dict.php');

		include(__dir__ .'/ki-url.php');
		include(__dir__ .'/ki-client.php');
		include(__dir__ .'/ki-auth.php');

		if (!isset($_SESSION) && !headers_sent())
			session_start();


		$dbCfg= KF::c('DBCFG');
		$DB = new PDO("mysql:host={$dbCfg->HOST};dbname={$dbCfg->NAME};charset=utf8", $dbCfg->USER, $dbCfg->PASS, array(PDO::ATTR_PERSISTENT=>true));
		$DB->exec("set names utf8");

		//additional error callback (to DB,table)
		$ERRR->errCB(ErrCB\errCBDB($DB, 'site_log_errors'));


		$URL= new KiURL();


		$USER= new KiAUTH($DB, KF::c('SOCIAL'));
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