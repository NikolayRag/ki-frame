<?
/*
Error, exception and shutdown handler class.
Handles runtime and fatal errors.

__construct($_doClean=true)
	Assign handlers

	$_doClean
		remove all output buffered at shutdown.

errCB($_CB)
	add callback used at shutdown
	$_CB
		function($_errPool) dumping callback called at shutdown;
		$errPool array of all collected errors will be passed in,
			right after err500 handler.

okCB($_CB)
	add callback used at shutdown in any case
	$_CB
		function($_errPool) dumping callback called at shutdown;
		$errPool array of all collected errors will be passed in,
			right after err500 handler.

setClean($_doClean)
	set buffer behavior at shutdown.

	$_doClean
		remove all output buffered at shutdown.


countErrors($_countErrors=true, $_countXcption=true)
	return count of errors or exceptions

	$_countErrors
	$_countXcption
		flag indicating to count errors or exceptions respectively

*/


class KiERR {

	var $errPool= [];

	var $CB= [], $okCB= []; //callbacks
	var $doClean;

	function __construct($_doClean=true){
		$this->doClean= $_doClean;

		ob_start();

		ini_set("display_errors", "0");
		error_reporting(0);

		set_error_handler(array($this, 'hError'));
		set_exception_handler(array($this, 'hException'));
		register_shutdown_function(array($this, 'hShut'));
	}



	function setClean($_doClean){
		$this->doClean= $_doClean;
	}



	function errCB($_CB){
		if (!is_callable($_CB))
			return;

		$this->CB[]= $_CB;

		return True;
	}



	function okCB($_CB){
		if (!is_callable($_CB))
			return;

		$this->okCB[]= $_CB;

		return True;
	}



	function hError($_errCode, $_errMessage, $_errFile, $_errLine, $_vars) {
		$this->errPool[]= [
			'type'=> $_errCode,
			'message'=> $_errMessage,
			'file'=> $_errFile,
			'line'=> $_errLine,
			'etype'=> 1
		];
	}



	function hException($_exception) {
		$this->errPool[]= [
			'type'=> $_exception->getCode(),
			'message'=> $_exception->getMessage(),
			'file'=> $_exception->getFile(),
			'line'=> $_exception->getLine(),
			'etype'=> 2
		];
	}



	function hShut() {
		$lastErr= error_get_last();
		if ($lastErr)
			$this->errPool[]= $lastErr;


		$bufferSoFar= ob_get_clean();

		//no errors case
		//flush buffer and call okCB callbacks
		if (!count($this->errPool)){
			echo $bufferSoFar;

			foreach ($this->okCB as $cCB){
				call_user_func($cCB);
			}

			return;
		}


		//have errors case
		header("HTTP/1.0 500");

		if (!$this->doClean){
			echo $bufferSoFar;
		}
//  todo 13 (api, add) +0: 500 page

		foreach ($this->CB as $cCB){
			call_user_func($cCB, $this->errPool);
		}

	}


	function countErrors($_countErrors=true, $_countXcption=true) {
		$cnt= 0;

		foreach ($this->errPool as $cErr){
			if ($_countErrors && $cErr['etype']==1)
				$cnt++;

			if ($_countXcption && $cErr['etype']==2)
				$cnt++;
		}

		return $cnt;
	}
}

?>
