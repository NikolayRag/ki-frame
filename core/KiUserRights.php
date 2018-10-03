<?
/*
KiRights singletone provide high-level user rights check.

Rights are defined first as functions, and then checked later by named reference.
*/
class KiRights {
	private static $isInited;

	private static $definitionsA = [];

	private $__this_user;


	function __construct($_user=Null){
		self::init();

		$this->__this_user = $_user;
	}
	


/*
Define named check function for later use.
Right is checked then, running bond function, with user itsels as argument.
*/
	static function define($_name, $_f){
		if (is_callable($_f))
			self::$definitionsA[$_name] = $_f;
	}



	function __get($_name){
		if (!array_key_exists($_name, self::$definitionsA))
			return;

		$cFn = self::$definitionsA[$_name];
		return $cFn($this->__this_user);
	}



	static function init(){
		if (self::$isInited)
			return
		self::$isInited = True;
	}
}

?>
