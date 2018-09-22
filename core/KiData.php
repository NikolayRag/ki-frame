<?
// =todo 71 (db) +1: Make KiData database highlevel abstract layer
/*
Formalize database collection to have abstract access layer for it, like SQL functions.
Database access function is defined once and used later.
*/
class KiData {
	private static $isInited;



	static function init(){
		if (self::$isInited)
			return
		self::$isInited = True;
	}
}
?>