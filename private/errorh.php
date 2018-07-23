<?
namespace ErrCB;

/*
Error callbacks class.
Creates and returns function suitable for KiError->errCB()
*/

/*
errCBDisplayBadge()
	Return function that displays "error" message overlayed.


errCBDisplayVerbose()
	Return function that displays all errors verbose.


okCBProfile($_startTime)
	Return function that adds profile info  to html code.

		$_startTime
			Start time in microseconds for compare time difference to.


errCBApiError($_message)
	Return function that throws JSON-encoded message at error.

*/

	function errCBDisplayBadge(){
		return function($_errPool){
			echo '<meta charset="utf-8">
			<div class="notifyjs-corner" style="top: 0px; left: 0px;">
				<div class="notifyjs-bootstrap-base notifyjs-bootstrap-error">
					<span data-notify-text="">Что-то пошло не так!</span>
				</div>
			</div>';
		};
	}


	function errCBDisplayVerbose(){
		return function($_errPool){
			echo "<pre>";
			foreach($_errPool as $cErr)
				print_r($cErr);
			echo "</pre>";
		};
	}


	function okCBProfile($_startTime){
		return function() use($_startTime){
			echo '<!--'.round((microtime(true) -$_startTime)*1000)/1000 .'s-->';
		};
	}



//api error handlers

	function errCBApiError($_message){
		return function($_errPool) use ($_message){
			echo json_encode($_message);

//			foreach($_errPool as $cErr)
//				print_r($cErr);
		};
	}



$ERRR->okCB(okCBProfile($__startTime));
$ERRR->errCB(errCBDisplayBadge());
//debug error callback
if ($DEBUG)
	$ERRR->errCB(errCBDisplayVerbose());


?>
