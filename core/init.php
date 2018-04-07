<?
include(__dir__ .'/ki-error.php');
$ERRR= new KiERR(true);

include(__dir__ .'/init_errorh.php');
//general error callback (to file)
$ERRR->errCB(ErrCB\errCBFile(__dir__ .'/../log/log.txt' ));


include(__dir__ .'/support.php');
include(__dir__ .'/support-loose.php');


include(__dir__ .'/ki-const.php');

include(__dir__ .'/../private/c_core.php');
include(__dir__ .'/../private/c.php');

if ($DEBUG) {
	$ERRR->setClean(false);

	$ERRR->errCB(ErrCB\errCBEcho());
}



include(__dir__ .'/ki-sql.php');
include(__dir__ .'/ki-dict.php');

include(__dir__ .'/ki-url.php');
include(__dir__ .'/ki-client.php');
include(__dir__ .'/ki-auth.php');




$DB = new PDO("mysql:host={$DBCFG->HOST};dbname={$DBCFG->NAME};charset=utf8", $DBCFG->USER, $DBCFG->PASS, array(PDO::ATTR_PERSISTENT=>true));
$DB->exec("set names utf8");

//additional error callback (to DB,table)
$ERRR->errCB(ErrCB\errCBDB($DB, 'site_log_errors'));


$URL= new KiURL($URI_ALLOW);


$SOC= new KiAUTH($SOCIAL);
$SOC->socInit();

include(__dir__ .'/../_3rd/uflex/autoload.php');

$USER= new \ptejada\uFlex\User();
$USER->config->database->pdo= $DB;
$USER->start();

include(__dir__ .'/ki-rights.php');
//user-wide reusable rights object
$RIGHTS= new Rights($USER->isSigned()? $USER->GroupID :0);


include(__dir__ .'/../private/errorh.php');

?>