<?
/*
Error callbacks class.
Creates and returns function suitable for KiHandler->errCB()
*/


class KiError {

/*
	Return function that stores error array info specified file.
		$_fn
			File name to store errors to.
*/
	static function errCBFile($_fn){
		if (!$_fn)
			return false;

		return function($_errPool) use($_fn) {
			$fn= fopen($_fn,'a');
			if (!$fn)
				return;

			fWrite($fn, "\n" .date('y-M-d h:m:s') ."\n");
			foreach ($_errPool as $cKey => $cVal) {
				$errType= ['','E','X'][getA($cVal, 'etype')];
				fWrite($fn, "$errType ${cVal['type']}, ${cVal['message']}, ${cVal['file']}, ${cVal['line']}\n");
			}

			fClose($fn);
		};
	}



/*
Return function that stores error array info specified DB.

$_db
	PDO database store errors to.

$_table
	Table name to store errors to.
	Have fields:
		code
			error code
		`desc`
			error text
		file
			file error happens at
		line
			line error happens at
		id
			unique block id, same for all errors at one runtime instance
		n
			sequental number of error at one runtime instance
*/
	static function errCBDB($_table){
		\KiSql::add('errcbdbNew', "INSERT INTO $_table (type, code, `desc`, file, line, id_user, url, agent) VALUES (?,?,?,?,?,?,?,?)");
		\KiSql::add('errcbdbAdd',  "INSERT INTO $_table (type, code, `desc`, file, line, id, n) VALUES (?,?,?,?,?,?,?)");

		return function($_errPool) {
			$maxId = 0;

			foreach ($_errPool as $cKey => $cVal) {
				$eType= (array_key_exists('etype', $cVal) && $cVal['etype']==2)?2:1;
				if (!$cKey){
					\KiSql::apply('errcbdbNew', $eType, $cVal['type'], $cVal['message'], $cVal['file'], $cVal['line'], /*$USER->id*/ 0, $_SERVER["REQUEST_URI"], getA($_SERVER,'HTTP_USER_AGENT','fake'));
					$maxId= \KiSql::lastInsertId();
				} else {
					\KiSql::apply('errcbdbAdd', $eType, $cVal['type'], $cVal['message'], $cVal['file'], $cVal['line'], $maxId, $cKey);
				}
			}
		};
	}



	static function errCBEcho(){
		return function($_errPool){
			foreach($_errPool as $cErr)
				print_r($cErr);
		};
	}
}

?>
