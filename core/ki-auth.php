<?php

/*
Deal with user authorization - social and explicit, and rights assignment

Social logon data is fetched as
*/


spl_autoload_register(
    function ($class) {
        $baseDir = __DIR__ . '/../_3rd/php-social/lib';
        $path = $baseDir . '/' . str_replace('\\', '/', $class) . '.php';

        if (is_file($path)) {
            require $path;
        }
    }
);

include(__dir__ .'/../_3rd/uflex/autoload.php');
include(__dir__ .'/ki-rights.php');
include(__dir__ .'/ki-authSoc.php');



class KiAUTH {
	var $sessionName, $cbName;

	var $socUser, $flexUser, $rights;

	var $isSigned=false, $ID=0, $email='';

	var $name='';

	var $logoutURL='';


	function __construct($_db, $_settings, $_session='socAuth', $_cb='logoncb'){
		$this->sessionName= $_session;
		$this->cbName= $_cb;

		session_start();



		$this->flexUser= new \ptejada\uFlex\User();
		$this->flexUser->config->database->pdo= $_db;
		$this->flexUser->start();

		$this->rights= new Rights($this->flexUser->isSigned()? $this->flexUser->GroupID :0);


		$this->socUser= new KiSoc($_settings, $this->cbName);

		$this->logoutURL= $this->socUser->socialURL(0);


        $this->isSigned= $this->flexUser->isSigned() || ($this->socUser->user?true:false);
		
		if ($this->socUser->user)
			$this->name= $this->socUser->user->firstName;
	}


	function react($_req){
		$this->socUser->socReact($_req);
	}


	function logout(){
		$this->socUser->socOut();
	}

}

?>