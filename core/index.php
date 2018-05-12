<?

$__startTime= microtime(true);

include(__dir__ .'/init.php');


if ($URL->type=='api'){
	if ($DEBUG) {
		$ERRR->setClean(false);

		$ERRR->errCB(ErrCB\errCBEcho());
	}

	include(__dir__ ."/../private/api/{$URL->path[0]}.php");
	exit;
}


if ($URL->type!==''){
	header("HTTP/1.1 404 Not Found");
	exit;
}




//if (getA($fetchSite,'maintainance'))
//	header("HTTP/1.1 503 Service Unavailable");


//switch ((new KiCLIENT())->type()){
//	case $CLIENT_TYPE->BOT: //branch: crawler
//		include('faceBot.php');
//		exit;
//}


//normal flow
$ERRR->setClean(false);

if (is_file(__dir__ .'/../private/errorh.php'))
	include(__dir__ .'/../private/errorh.php');


include(__dir__ .'/../private/htmpl/face.php');
?>
