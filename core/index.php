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
	http_response_code(404);
//  todo 11 (api, add) +0: add 404 template
	echo '404';
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
?>
