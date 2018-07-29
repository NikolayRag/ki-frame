<?
/*
Сonstants supplying class.
Used as a singletone.



Methods:
	add(name, value, context=0)
		Add new variable into pool.
		'context' assigns context to variable, used later with dump(context)

	get(name)
		Return value of variable 'name'

	dump(context=false)
		Return copy of pool array in form of ['name']=>value
		If context is other than FALSE, only variables of that context are returned.

*/

class KiConst {
	private static
		$pool=[],
		$ctx=[];



	static function add($_name, $_value, $_ctx=0){
		//check name
		if (!$_name || !is_string($_name) || is_int($_name))
			return false;


		self::$pool[$_name]= $_value;
		self::$ctx[$_ctx][$_name]= self::$pool[$_name];
	

		return true;
	}



	static function get($_name){
		getA(self::$pool, $_name, false);
	}



	static function dump($_ctx=false){
		if (!$_ctx)
			return self::$pool;

		return getA(self::$ctx, $_ctx, []);
	}
}

?>
