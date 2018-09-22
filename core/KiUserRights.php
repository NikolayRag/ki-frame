<?

/*
KiRights singletone provide high-level user rights check.
*/

// =todo 64 (auth) +0: add KiRights class

// -todo 74 (ux, auth) +0: allow to define right function
class KiRights {
	private static $isInited;



	static function init(){
		if (self::$isInited)
			return
		self::$isInited = True;
	}
}

?>
