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
	//since uFlex dont return error codes, we must catch them
	static $flexErrA= [
		1=> '/New User Registration Failed/',
		2=> '/The Changes Could not be made/',
		3=> '/Account could not be activated/',
		4=> '/We don\'t have an account with this email/',
		5=> '/Password could not be changed. The request can\'t be validated/',
		6=> '/Logging with cookies failed/',
		7=> '/No Username or Password provided/',
		8=> '/Your Account has not been Activated\. Check your Email for instructions/',
		9=> '/Your account has been deactivated\. Please contact Administrator/',
		10=> '/Wrong Username or Password/',
		11=> '/Confirmation hash is invalid/',
		12=> '/Your identification could not be confirmed/',
		13=> '/Failed to save confirmation request/',
		14=> '/You need to reset your password to login/',
		15=> '/Can not register a new user, as user is already logged in\./',
		16=> '/This Email is already in use/',
		17=> '/This Username is not available/',

		18=> '/No need to update!/',

		19=> '/(.+)s did not match/',
		20=> '/(.+) is required\./',
		21=> '/The (.+) is larger than (.+) characters\./',
		22=> '/The (.+) is too short\. It should at least be (.+) characters long/',
		23=> '/The (.+) \"(.+)\" is not valid/'
	];


	private $db;

	var $socUser=false, $flexUser, $mask=0, $rights;

	var $socUrlA=[];
	var $isSigned=false, $id=0, $name='', $email='', $photo='';


	function __construct($_db, $_socialCfg, $_url){
		$this->db= $_db;

		($user= $this->initFlexUser()) || ($user= $this->initSocUser($_socialCfg));
		if (!$user){
			$this->socUrlA= $this->socUser->loginURL($_url);
			return;
		}
	
		$this->applyUser($user);
	}



/*
API cb for social logon.
*/
	function socCB($_req){
		$socErr= $this->socUser->socCB($_req);
		if ($socErr)
			return;

		$id= $this->assignedGet();
		if (!$id){
			$userData= $this->socUser->fetch();
			if (!$userData)
			 	return;

			$id= $this->assignedCreate($userData);
		}

		$this->assignedUpdate($id);


		$this->applyUser($this->flexUser->manageUser($id));
	}



/*
Logout either.
*/
	function logout(){
		$this->socUser?
			$this->socUser->logout() :
			$this->flexUser->logout();
	}



/*
Stores uFlex user data locally
*/
	private function applyUser($_user){
		$this->isSigned= true;

		$this->id= $_user->ID;
		$this->email= $_user->Email;

		($this->name= $_user->displayName) || ($this->name= $_user->Email);
		$this->photo= $_user->photoURL;
		$this->mask= $_user->mask;
	}



/*
Check if log/pass user is signed.
*/
	private function initFlexUser(){
		$this->flexUser= new \ptejada\uFlex\User();
		$this->flexUser->config->database->pdo= $this->db;

		if (!$this->flexUser->start()->isSigned())
			return;

		return $this->flexUser;
	}


/*
Check if social user is signed.
Soc user init assumes normal user is not logged, and thus assigned one will be in place.
*/
	private function initSocUser($_socialCfg){
		$this->socUser= new KiSoc($_socialCfg);

		if (!$this->socUser->start())
			return;

		$xUser= $this->assignedGet();
		if (!$xUser)
			return;

		return $this->flexUser->manageUser($xUser);
	}



/*
Fetch assigned user for given social.
If none is assigned, implicit one is created and assigned.

Return user id.
*/
	private function assignedGet(){
		$stmt= $this->db->prepare('SELECT id_users FROM users_social WHERE type=? AND id=?');
		$stmt->execute([$this->socUser->type, $this->socUser->id]);
		$id_assigned= getA($stmt->fetch(), 'id_users', 0);

		return $id_assigned;
	}



	private function assignedCreate($_userData){
		$stmt= $this->db->prepare('INSERT INTO users (auto_social,RegDate,displayName,photoURL) VALUES (1,?,?,?)');
		$stmt->execute([time(),$_userData->firstName, $_userData->photoUrl]);
		$id_assigned= $this->db->lastInsertId();


		$stmt= $this->db->prepare('INSERT INTO users_social (type,id,id_users) VALUES (?,?,?)');
		$stmt->execute([$this->socUser->type, $this->socUser->id, $id_assigned]);

		return $id_assigned;
	}



	private function assignedUpdate($_id){
		$stmt= $this->db->prepare('UPDATE users SET LastLogin=? WHERE ID=?');
		$stmt->execute([time(), $_id]);
	}



	function errorGetLast(){
		$err= Null;
		foreach ($this->flexUser->log->getAllErrors() as $err);

		$cErr= Null;
		if ($err)
			foreach ($err as $cErr);

		return $this->errorDeref($cErr);
	}



	private function errorDeref($_err){
		if (!$_err)
			return;


		foreach (self::$flexErrA as $errId=>$cTest){
			if (preg_match($cTest, $_err))
				return $errId;
		}
	}

}
?>