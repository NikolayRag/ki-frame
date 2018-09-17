<?
/*
Сonstants supplying class.
Used as a singletone.

Constant created is available as KC->name()
*/

class KiConst {
	private static
		$pool=[],
		$ctx=[];




/*
Set and Get constants by statically calling arbitrary named function.

Argument is a value to assign.
Second argument can be supplied to specify context, used to get back all constants with it later.

If arguments are omited, value is returned.
*/
	static function __callStatic($_name, $_argsA){
		if (!count($_argsA))
			return getA(self::$pool, $_name, false);


		$value = $_argsA[0];
		if (!$_name || !is_string($_name))
			return false;


		$ctx = getA($_argsA, 1, 0);

		self::$pool[$_name] =
		self::$ctx[$ctx][$_name] = 
			$value;
	

		return true;
	}




/*
Return copy of pool array in form of ['name']=>value

$_ctx
	If context is other than FALSE, only variables of that context are returned.
*/
	static function ___dump($_ctx=false){
		if (!$_ctx)
			return self::$pool;

		return getA(self::$ctx, $_ctx, []);
	}
}


class_alias('KiConst', 'KC');

?>
