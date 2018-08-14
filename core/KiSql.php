<?

/*
Over-SQL class.
Support SQL templates reusing

*/

class KiSql {
	private static $isInited;

	private static $db;
	private static $lastSucc= 0;
	private static $lastRow;
	private static $callsCnt= 0;
	private static $dbErr= 0, $dbErrText= '';

	private static $sqlTemplate= [];



	function __construct($_host,$_base,$_uname,$_upass){
		if (self::$isInited)
			return;
		self::$isInited= True;


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
	function add($_tmpl, $_sql){
		self::$sqlTemplate[$_tmpl]= $_sql;
	}



/*
Run stored query.


$_tmpl
	Stored query name.

...
	Several arguments, respect to query parameter list.

*/
	function apply($_tmpl){
		$sqVars= func_get_args();
		foreach ($sqVars as $sqVal)
		  if (!count($sqVal))
		    return false;

		$bindVars= array();
		$searchPos= 1;
		$TSqlA= preg_replace_callback(
			'/\?/',
			function ($_in) use ($sqVars,&$bindVars,&$searchPos) {
				$nextV= $sqVars[$searchPos++];
				if (is_array($nextV))
				  $bindVars= array_merge($bindVars,$nextV);
				else
				  $bindVars[]= $nextV;
				return str_repeat('?,',count($nextV)-1) .'?';
			},
			self::$sqlTemplate[$_tmpl]
		);

		self::$stmt= self::$db->prepare($TSqlA);
		self::$lastSucc= self::$stmt->execute($bindVars);
		self::$callsCnt+= 1;

		if (!self::$lastSucc)
		  return false;
		return true;
	}



/*
Get data from last query.


$_col
	Column name.
	If omited, result array is returned


$_def
	Default value for wrong column name case.

*/
	function fetch($_col=false,$_def=false){
		self::$lastRow= self::$stmt->fetch();
		if ($_col===false)
		  return self::$lastRow;

		return getA(self::$lastRow,$_col,$_def);
	}


/*
Return last inserted ID.
*/
	function lastInsertId(){
		return self::$db->lastInsertId();
	}
}

?>
