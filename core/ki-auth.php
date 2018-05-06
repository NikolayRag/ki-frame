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
	private $db;

	var $socUser=false, $flexUser, $mask=0, $rights;

	var $socUrlA=[];
	var $isSigned=false, $id=0, $name='', $email='', $photo='';


	function __construct($_db, $_socialCfg){
		$this->db= $_db;

		($user= $this->initFlexUser()) || ($user= $this->initSocUser($_socialCfg));

		if (!$user){
			$this->socUrlA= $this->socUser->loginURL();
			return;
		}


		$this->isSigned= true;

		$this->id= $user->ID;
		$this->email= $user->Email;

		($this->name= $user->displayName) || ($this->name= $user->Email);
		$this->photo= $user->photoURL;
		$this->mask= $user->mask;
	}



	private function initFlexUser(){
		$this->flexUser= new \ptejada\uFlex\User();
		$this->flexUser->config->database->pdo= $this->db;

		if (!$this->flexUser->start()->isSigned())
			return;

		return $this->flexUser;
	}


/*
Soc user init assumes normal user is not logged, and thus it is overrided.
*/
	private function initSocUser($_socialCfg){
		$this->socUser= new KiSoc($_socialCfg);

		if (!$this->socUser->start())
			return;

		$xUser= $this->getAssignedUser();
		if (!$xUser)
			return;


		return $this->flexUser->manageUser($xUser);
	}



	private function getAssignedUser($_updateLog=False){
		$stmt= $this->db->prepare('SELECT id_users FROM users_social WHERE type=? AND id=?');
		$stmt->execute([$this->socUser->type, $this->socUser->id]);
		$id_assigned= getA($stmt->fetch(), 'id_users', 0);

		if (!$id_assigned){
			if (!$this->socUser->start())
				return;

			$stmt= $this->db->prepare('INSERT INTO users (auto_social,RegDate,displayName,photoURL) VALUES (1,?,?,?)');
			$stmt->execute([time(),$this->socUser->firstName, $this->socUser->photoUrl]);
			$id_assigned= $this->db->lastInsertId();


			$stmt= $this->db->prepare('INSERT INTO users_social (type,id,id_users) VALUES (?,?,?)');
			$stmt->execute([$this->socUser->type, $this->socUser->id, $id_assigned]);
		}


		if ($_updateLog){
			$stmt= $this->db->prepare('UPDATE users SET LastLogin=? WHERE ID=?');
			$stmt->execute([time(), $id_assigned]);
		}


		return $id_assigned;
	}




	function socCB($_req){
		$this->socUser->socCB($_req);

		$this->getAssignedUser(True);
	}



	function logout(){
		$this->socUser?
			$this->socUser->logout() :
			$this->flexUser->logout();
	}

}

?>