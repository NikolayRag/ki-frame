<?php

/*
Deal with user authorization - social and explicit, and rights assignment

Init macro:
	init Flex user
	or
	init Soc user
		if user not assigned:
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
		$user= $this->initFlexUser($_db);
		if (!$user)
			$user= $this->initSocUser($_db, $_socialA);

		if (!$user){
			$this->socUrlA= $this->socUser->loginURL();
			return;
		}


		$this->isSigned= true;

		$this->id= $user->ID;
		$this->email= $user->Email;

		$this->name= $user->displayName;
		$this->photoUrl= $user->photoURL;
		$this->mask= $user->mask;
	}



	private function initFlexUser($_db){
		$this->flexUser= new \ptejada\uFlex\User();
		$this->flexUser->config->database->pdo= $_db;

		if (!$this->flexUser->start()->isSigned())
			return;

		return $this->flexUser;
	}


/*
Soc user init assumes normal user is not logged, and thus it is overrided.
*/
	private function initSocUser($_db, $_socialA){
		$this->socUser= new KiSoc($_socialA);

		if (!$this->socUser->start())
			return;


		$stmt= $_db->prepare('SELECT id_users FROM users_social WHERE type=? AND id=?');
		$stmt->execute([$this->socUser->type, $this->socUser->id]);
		$id_assigned= getA($stmt->fetch(), 'id_users', 0);

		if (!$id_assigned){
			$stmt= $_db->prepare('INSERT INTO users (displayName,photoURL) VALUES (?,?)');
			$stmt->execute([$this->socUser->firstName, $this->socUser->photoUrl]);
			$id_assigned= $_db->lastInsertId();


			$stmt= $_db->prepare('INSERT INTO users_social (type,id,id_users) VALUES (?,?,?)');
			$stmt->execute([$this->socUser->type, $this->socUser->id, $id_assigned]);
		}


		return $this->flexUser->manageUser($id_assigned);
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