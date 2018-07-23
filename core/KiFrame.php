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