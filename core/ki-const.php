<?
/*
class for supplying constants.
Used as a static.

Methods:
	add('name', value, pool=true);
		Add new variable into pool.
		If [pool]==false, new global variable named [name] is only created

	dump(names=false);
		return copy of pool array in form of ['name']=>value
		if [names] array or string is given, only specified values will return.


public fields:
	global [name]
		stored global.
*/


class KiCONST {
	static $pool= [];


	static function add($_name, $_value, $_pool=true){
		//check name
		if (!$_name || !is_string($_name) || is_int($_name))
			return false;

		//only fill pool
		if ($_pool){
			self::$pool[$_name]= $_value;
		}
	
		//add local field
//		$this->{$name}= $_value;

		//add global
		global ${$_name};
		${$_name}= $_value;

		return true;
	}



	static function dump($_names=false){
		if (!$_names)
			return self::$pool;


		//return named
		$retPool= [];
		foreach ($_names as $cName) {
			$retPool[$cName] = self::$pool[$cName];
		}

		return $retPool;
	}
}

?>
