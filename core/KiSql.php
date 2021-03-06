<?

/*
Over-SQL class.
Support SQL templates reusing

*/

class KiSql {
	const MsgError = 'No DB was connected';


	private static $isInited, $callsCnt=0;

	private static $db, $stmt;
	private static $lastRow;
	private static $dbErr= 0, $dbErrText= '';

	private static $sqlTemplateA= [];



	static function init($_host,$_base,$_uname,$_upass){
		if (self::$isInited)
			return;
		self::$isInited= True;


		try {
			if ($_host and $_base)
				self::$db = new PDO("mysql:host={$_host};dbname={$_base};charset=UTF8", $_uname, $_upass, array(PDO::ATTR_PERSISTENT=>true));
		}
		catch( PDOException $Exception ) {
			self::$dbErr= $Exception->getCode();
			self::$dbErrText= $Exception->getMessage();

			self::$db = Null;
		}

		if (self::$db)
			self::$db->exec("set names utf8");
	}



/*
Store array of named queries.


$_tmplA
	[Name=>Template] array to store.
	Arguments to bind are marked as '?'.


$_bindA
	Early binding array: '%' markers within templates are replaced with provided value sequentally or '' if insufficient values.

// -todo 96 (sql, fix) +0: make early binding safe
	NOTICE! Early binding is not safe.
*/
	static function add($_tmplA, $_bindA=False){
		foreach ($_tmplA as $cName => $cSql){
			$cSqlBond = [];
			foreach (explode('%',$cSql) as $i=>$cPart){
				$cSqlBond[] = $cPart;
				$cSqlBond[] = getA($_bindA, $i, '%');
			}
			array_pop($cSqlBond); //last subst is exceed

			self::$sqlTemplateA[$cName] = implode('', $cSqlBond);
		}
	}


// -todo 97 (sql) +0: switch to prepared statement extended objects


/*
Run stored query.


$_tmpl
	Stored query name.

...
	Several arguments, respect to query parameter list.

*/
	static function apply($_tmpl){
		self::$stmt = Null;


		if (!self::$db)
			return;


		$sqVars= func_get_args();
		foreach ($sqVars as $sqVal)
		  if (is_array($sqVal) && !count($sqVal))
		    return;

		$bindVars= array();
		$searchPos= 1; //skip first arg
		//replace '?' within specified template based on provided variable: arrays expands into multiple '?,?,...'
		$TSqlA= preg_replace_callback(
			'/\?/',
			static function ($_in) use ($sqVars,&$bindVars,&$searchPos) {
				$nextV= $sqVars[$searchPos++];
				if (!is_array($nextV))
					$nextV = [$nextV];

				$bindVars= array_merge($bindVars,$nextV);

				return implode(array_fill(0, count($nextV), '?'), ',');
			},
			self::$sqlTemplateA[$_tmpl]
		);

		self::$callsCnt+= 1;
		$cStmt = self::$db->prepare($TSqlA);
		if (!$cStmt->execute($bindVars)){
			throw new Exception( $cStmt->errorInfo()[2] );
			return;
		}


		self::$stmt = $cStmt;
		return True;
	}



/*
Get data from last query.


$_col
	Column name.
	If omited, result array is returned
*/
	static function fetch($_col=false){
		$defRet = $_col===False? [] : Null;


		if (!self::$db)
			return $defRet;

		if (self::$stmt===Null)
			return $defRet;


		self::$lastRow= self::$stmt->fetch();
		if ($_col===false)
		  return self::$lastRow;

		return getA(self::$lastRow,$_col,Null);
	}


/*
Return last inserted ID.
*/
	static function lastInsertId(){
		if (!self::$db)
			return;

		return self::$db->lastInsertId();
	}



/*
Return underlayind PDO.
*/
	static function getPDO(){
		return self::$db;
	}
}

?>
