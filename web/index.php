<?
$__startTime= microtime(true);

include(__dir__ .'/../core/init.php');


if ($URL->type=='api'){
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
$ERRR->okCB(ErrCB\okCBProfile($__startTime));

$ERRR->errCB(ErrCB\errCBDisplayBadge());

//debug error callback
if ($DEBUG)
	$ERRR->errCB(ErrCB\errCBDisplayVerbose());



include(__dir__ .'/../private/htmpl/face.php');

?>
