<?
namespace ErrCB;

/*
Error callbacks class.
Creates and returns function suitable for KiError->errCB()
*/


/*
errCBFile($_fn)
	Return function that stores error array info specified file.
		$_fn
			File name to store errors to.



*/
	function errCBFile($_fn){
		if (!$_fn)
			return false;

		return function($_errPool) use($_fn) {
			$fn= fopen($_fn,'a');
			fWrite($fn, "\n" .date('y-M-d h:m:s') ."\n");
			foreach ($_errPool as $cKey => $cVal) {
				$isX= array_key_exists('etype', $cVal) && $cVal['etype']==2;
				fWrite($fn, ($isX?'X':'E')." ${cVal['type']}, ${cVal['message']}, ${cVal['file']}, ${cVal['line']}\n");
			}
			fClose($fn);
		};
	}


/*
errCBDB($_db, $_table)
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
	function errCBDB($_db, $_table){
		if (!$_db)
			return false;

		return function($_errPool) use($_db, $_table) {
			global $USER;
			foreach ($_errPool as $cKey => $cVal) {
				$eType= (array_key_exists('etype', $cVal) && $cVal['etype']==2)?2:1;
				if (!$cKey){
					$stmt= $_db->prepare("INSERT INTO $_table (type, code, `desc`, file, line, id_user, url, agent) VALUES (?,?,?,?,?,?,?,?)");
					$stmt->execute([$eType, $cVal['type'], $cVal['message'], $cVal['file'], $cVal['line'], $USER->id, $_SERVER["REQUEST_URI"], getA($_SERVER,'HTTP_USER_AGENT','fake')]);
				} else {
					$stmt= $_db->prepare("SELECT max(id) maxid FROM $_table");
					$stmt->execute();
					$maxid= $stmt->fetch();
	
					$stmt= $_db->prepare("INSERT INTO $_table (type, code, `desc`, file, line, id, n) VALUES (?,?,?,?,?,?,?)");
					$stmt->execute([$eType, $cVal['type'], $cVal['message'], $cVal['file'], $cVal['line'], $maxid[0], $cKey]);
				}
			}
		};
	}


	function errCBEcho(){
		return function($_errPool){
			foreach($_errPool as $cErr)
				print_r($cErr);
		};
	}

?>
