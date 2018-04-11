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
	private $cbName;
	private $socUser, $flexUser, $rights;

	var $logoutURL='', $socUrlA;
	var $isSigned=false, $ID=0, $name='', $email='', $photoUrl='';


	function __construct($_db, $_settings, $_cb='logoncb'){
		$this->cbName= $_cb;

		session_start();



		$this->flexUser= new \ptejada\uFlex\User();
		$this->flexUser->config->database->pdo= $_db;
		$this->flexUser->start();
		if ($this->flexUser->isSigned()){
			$this->flexUser->login(); //update from DB
			$this->name= $this->flexUser->displayName;
			$this->email= $this->flexUser->Email;
			$this->photoUrl= $this->flexUser->photoURL;
		}

		$this->rights= new Rights($this->flexUser->isSigned()? $this->flexUser->GroupID :0);


		$this->socUser= new KiSoc($_settings, $this->cbName);
		$this->socUrlA= $this->socUser->urlA;

		$this->logoutURL= $this->socUser->socialURL(0);


        $this->isSigned= $this->flexUser->isSigned() || (bool)$this->socUser->user;
		
		if ($this->socUser->user){
			$this->name= $this->socUser->user->firstName;
			$this->email= $this->socUser->user->email;
			$this->photoUrl= $this->socUser->user->photoUrl;
		}

		if (!$this->name)
			$this->name= $this->email;
	}


	function react($_req){
		$this->socUser->socReact($_req);
	}


	function logout(){
		$this->socUser->socOut();
	}

}

?>