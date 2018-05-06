<?php

/*
Deal with user authorization - social and explicit, and rights assignment

Init macro:
	init Flex user
	or
	init Soc user
		if logged:
			if explicit user not assigned:
				create and assign implicit user
			fetch assigned user
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
	var $socUser=false, $flexUser, $mask=0, $rights;

	var $socUrlA=[];
	var $isSigned=false, $id=0, $name='', $email='', $photoUrl='';


	function __construct($_db, $_socialA){
		if ($this->initFlexUser($_db) or $this->initSocUser($_socialA)){
			$this->isSigned= true;
			return;
		}

		$this->socUrlA= $this->socUser->loginURL();
	}



	private function initFlexUser($_db){
		$this->flexUser= new \ptejada\uFlex\User();
		$this->flexUser->config->database->pdo= $_db;

		if (!$this->flexUser->start()->isSigned())
			return;


		$this->id= $this->flexUser->ID;
		$this->email= $this->flexUser->Email;

		$this->name= $this->flexUser->displayName;
		$this->photoUrl= $this->flexUser->photoURL;
		$this->mask= $this->flexUser->mask;

		return true;
	}



	private function initSocUser($_socialA){
		$this->socUser= new KiSoc($_socialA);

		if (!$this->socUser->start())
			return;



		return true;
		$this->name= $this->socUser->firstName;
		$this->photoUrl= $this->socUser->photoUrl;
	}





	function socCB($_req){
		$this->socUser->socCB($_req);
	}



	function logout(){
		if ($this->socUser){
			$this->socUser->socOut();

			return;
		}

        $this->flexUser->logout();
	}

}

?>