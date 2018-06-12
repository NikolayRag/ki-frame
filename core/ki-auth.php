<?php

/*
Deal with user authorization - social and explicit, and rights assignment.
Actual user data is stored in logpass (uFlex) account.
Each soc user is assigned to logpass account.
Blank logpass account is assigned implicitely to new successfull soc login, and is not valid to log in with loginn/password actually.

! No implicit check is applied to restrict interference of soc/logpass user,
if one calls authorization methods outside this class.	

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

$_url
	$KiURL passed in with GET arguments filled.
*/
	function socCB($_url){
		$socErr= $this->socUser->socCB($_url);
		if ($socErr)
			return;

		$xId= $this->assignedGet($this->socUser);
		if (!$xId) //  todo 6 (error) +0: deal with error
		 	return;

		$this->assignedUpdate($xId); //update last logon state

		$this->applyUser($this->flexUser->manageUser($xId));
	}



/*
Logout either.
*/
	function logout(){
		$this->socUser?
			$this->socUser->logout() :
			$this->flexUser->logout();
	}




/* PRIVATE */

/*
Apply data from fetched uFlex user.
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
Check if logpass user is signed.
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
Soc user init assumes normal user is not logged, and thus user data from assigned one will be fetched, including local userID (differed from social userID's).
*/
	private function initSocUser($_socialCfg){
		$this->socUser= new KiSoc($_socialCfg);

		if (!$this->socUser->start())
			return;

		$xId= $this->assignedGet($this->socUser);
		if (!$xId) //  todo 5 (error) +0: deal with error
			return;

		return $this->flexUser->manageUser($xId);
	}



/*
Fetch assigned logpass user for given social.
If none logpass user is assigned, implicit one is created and assigned.

Return user id.
*/
	private function assignedGet($_soc){
		$stmt= $this->db->prepare('SELECT id_users FROM users_social WHERE type=? AND id=?');
		$stmt->execute([$_soc->type, $_soc->id]);
		$id_assigned= getA($stmt->fetch(), 'id_users', 0);


		if (!$id_assigned){
			$userData= $_soc->fetch();
			if (!$userData) //  todo 6 (error) +0: deal with error
			 	return;

			$id_assigned= $this->assignedCreate($userData);
		}

		return $id_assigned;
	}



/*
Create implicit logpass user for given social one.
*/
	private function assignedCreate($_userData){
		$stmt= $this->db->prepare('INSERT INTO users (auto_social,RegDate,displayName,photoURL) VALUES (1,?,?,?)');
		$stmt->execute([time(),$_userData->firstName, $_userData->photoUrl]);
		$id_assigned= $this->db->lastInsertId();


		$stmt= $this->db->prepare('INSERT INTO users_social (type,id,id_users) VALUES (?,?,?)');
		$stmt->execute([$this->socUser->type, $this->socUser->id, $id_assigned]);

		return $id_assigned;
	}



/*
Update last logon state.
*/
	private function assignedUpdate($_id){
		$stmt= $this->db->prepare('UPDATE users SET LastLogin=? WHERE ID=?');
		$stmt->execute([time(), $_id]);
	}






/*
Since uFlex dont return error codes, we must assign them based on error message.
*/
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



/*
Get code for last (and only) uFlex error, if any.

Return: code id
*/
	function flexErrorGetLast(){
		$err= Null;
		foreach ($this->flexUser->log->getAllErrors() as $err);

		$cErr= Null;
		if ($err)
			foreach ($err as $cErr);

		return $this->flexErrorDeref($cErr);
	}



/*
Restore error id from text message.
*/
	private function flexErrorDeref($_err){
		if (!$_err)
			return;


		foreach (self::$flexErrA as $errId=>$cTest){
			if (preg_match($cTest, $_err))
				return $errId;
		}
	}

}
?>