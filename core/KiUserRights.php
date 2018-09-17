<?

/*
KiRights singletone provide high-level user rights check.
*/

// =todo 64 (auth) +0: add KiRights class

// =todo 75 (auth) +0: fetch and hold groups
// -todo 74 (auth) +0: allow to define right function
class KiRights {
	private static $isInited;



	private $groupsA;



	static function init($_socialCfg){
		if (self::$isInited)
			return
		self::$isInited = True;
	}
}

?>
