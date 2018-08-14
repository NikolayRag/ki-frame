<?

/*
Over-SQL class.
Support SQL templates reusing

*/

class KiSql {
	private static $isInited, $callsCnt=0;

	private static $db, $stmt;
	private static $lastRow;
	private static $dbErr= 0, $dbErrText= '';

	private static $sqlTemplateA= [];



	function __construct($_host,$_base,$_uname,$_upass){
		if (self::$isInited)
			return;
		self::$isInited= True;


/*
INSERT INTO `site_log_errors` (type, code, `desc`, file, line, id_user, url, agent) VALUES (1,8,'Undefined variable: DB','/mnt/hgfs/Inetpub/www/yell.fm.wrk/core/KiFrame.php',140,0,'/','Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Safari/537.36
')
*/

		try {
			self::$db = new PDO("mysql:host={$_host};dbname={$_base};charset=UTF8", $_uname, $_upass, array(PDO::ATTR_PERSISTENT=>true));
		}
		catch( PDOException $Exception ) {
			self::$dbErr= $Exception->getCode();
			self::$dbErrText= $Exception->getMessage();
		}

		if (self::$db)
			self::$db->exec("set names utf8");
	}



/*
Store query string.


$_tmpl
	Template name to store.


$_sql
	Query string.
	Arguments should be marked as '?'.
*/
	static function add($_tmpl, $_sql){
		self::$sqlTemplateA[$_tmpl]= $_sql;
	}



/*
Run stored query.


$_tmpl
	Stored query name.

...
	Several arguments, respect to query parameter list.

*/
	static function apply($_tmpl){
		$sqVars= func_get_args();
//		foreach ($sqVars as $sqVal)
//		  if (!count($sqVal))
//		    return false;

		$bindVars= array();
		$searchPos= 1;
		$TSqlA= preg_replace_callback(
			'/\?/',
			static function ($_in) use ($sqVars,&$bindVars,&$searchPos) {
				$nextV= $sqVars[$searchPos++];
				if (is_array($nextV))
				  $bindVars= array_merge($bindVars,$nextV);
				else
				  $bindVars[]= $nextV;
				return str_repeat('?,',count($nextV)-1) .'?';
			},
			self::$sqlTemplateA[$_tmpl]
		);


		self::$callsCnt+= 1;
		self::$stmt= self::$db->prepare($TSqlA);
		$lastSucc= self::$stmt->execute($bindVars);
		if (!$lastSucc)
			throw new Exception( self::$stmt->errorInfo()[2] );


		return $lastSucc;
	}



/*
Get data from last query.


$_col
	Column name.
	If omited, result array is returned


$_def
	Default value for wrong column name case.

*/
	static function fetch($_col=false,$_def=false){
		self::$lastRow= self::$stmt->fetch();
		if ($_col===false)
		  return self::$lastRow;

		return getA(self::$lastRow,$_col,$_def);
	}


/*
Return last inserted ID.
*/
	static function lastInsertId(){
		return self::$db->lastInsertId();
	}
}

?>
