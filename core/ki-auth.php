<?php

/*
Deal with user authorization - social and explicit, and rights assignment

Init macro:
	explicit check
		logged: stop
		
		social check
			not logged: stop
			explicit user not assigned:
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
		$this->flexUser= new \ptejada\uFlex\User();
		$this->flexUser->config->database->pdo= $_db;


		if ($this->initFlexUser($_db)){
			$this->isSigned= true;
			return;
		}


		$this->socUser= new KiSoc($_socialA);

		if (!$this->initSocUser()){
			$this->socUrlA= $this->socUser->urlA;
			return;
		}

///
		
		$this->isSigned= true;

	}



	private function initFlexUser(){
		if (!$this->flexUser->start()->isSigned())
			return;


		$this->id= $this->flexUser->ID;
		$this->name= $this->flexUser->displayName;
		$this->email= $this->flexUser->Email;
		$this->photoUrl= $this->flexUser->photoURL;
		$this->mask= $this->flexUser->mask;

		return true;
	}



	private function initSocUser(){
		if (!$this->socUser->start())
			return;

		$this->email= $this->socUser->user->email;
		$this->name= $this->socUser->user->firstName;
		$this->photoUrl= $this->socUser->user->photoUrl;

		return true;
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