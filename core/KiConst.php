<?
/*
Сonstants supplying class.
Used as a singletone.
*/

class KiConst {
	private static
		$pool=[],
		$ctx=[];



/*
Add new variable into pool.

$_name
	Variable name to add

$_value
	Value to assign

$_ctx
	Assign context to variable, used later with dump($_ctx)
*/
	static function add($_name, $_value, $_ctx=0){
		//check name
		if (!$_name || !is_string($_name))
			return false;


		self::$pool[$_name] =
		self::$ctx[$_ctx][$_name] = 
			$_value;
	

		return true;
	}



/*
Return value of variable.

$_name
	Variable name to return
*/
	static function get($_name){
		return getA(self::$pool, $_name, false);
	}



/*
Return copy of pool array in form of ['name']=>value

$_ctx
	If context is other than FALSE, only variables of that context are returned.
*/
	static function dump($_ctx=false){
		if (!$_ctx)
			return self::$pool;

		return getA(self::$ctx, $_ctx, []);
	}
}

?>
