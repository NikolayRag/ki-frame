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
include(__dir__ .'/KiAuthSoc.php');



KiSql::add('kisqlGetCount', 'SELECT count(*) FROM Users');
KiSql::add('kisqlGetSocial', 'SELECT id_users FROM users_social WHERE type=? AND id=?');
KiSql::add('kisqlAdd', 'INSERT INTO users (auto_social,RegDate,displayName,photoURL) VALUES (1,?,?,?)');
KiSql::add('kisqlAddSocial', 'INSERT INTO users_social (type,id,id_users) VALUES (?,?,?)');
KiSql::add('kisqlUpdateLast', 'UPDATE users SET LastLogin=? WHERE ID=?');



//  todo 23 (ux, auth) +0: introduce entire auth cached timeout
class KiAuth {
	private $socUser=false, $flexUser, $mask=0, $rights;

	var $isSigned=false, $id=0, $name='', $email='', $photo='';


	function __construct($_socialCfg){
		($user= $this->initFlexUser()) || ($user= $this->initSocUser($_socialCfg));
		if (!$user)
			return;
	
		$this->applyUser($user);
	}



/*
Return suitable social login URL's.
*/
	function socUrlA(){
		if (!$isSigned)
			return [];

		return $this->socUser->loginURL();
	}



/*
API cb for social logon.
*/
	function socCB(){
		$socErr= $this->socUser->socCB();
		if ($socErr)
			return;

		$xId= $this->assignedGet($this->socUser);
		if (!$xId) //  todo 6 (error) +0: deal with error
		 	return;

		$this->assignedUpdate($xId); //update last logon state

		$this->applyUser($this->flexUser->manageUser($xId));
	}



/*
Regster new email/pass user and login.
*/
	function passRegister($_email, $_pass){
		//prepare username, coz uFlex refuse blank usernames
        $stmt= KiSql::apply('kisqlGetCount');
        $arr= KiSql::fetch();

        $res = $this->flexUser->register([
            'Username'=> "u_{$arr[0]}",
            'Email'=>$_email,
            'Password'=>$_pass
        ]);
        if (!$res)
        	return $this->flexErrorGetLast();

        return $this->passLogin($_email, $_pass);
}



/*
Log in with email/pass
*/
	function passLogin($_email, $_pass){
        $this->flexUser->login($_email, $_pass, true);

        return $this->flexErrorGetLast();
    }



/*
Logout either.
*/
	function logout(){
		$this->socUser?
			$this->socUser->logout() :
			$this->flexUser->logout();

//  todo 8 (api) -1: vary errors
		return;
	}



/*
Send pass restoring link to email.
*/
	function passRestore($_email){
//  todo 9 (api, clean) -1: make mail be loaded only at actual need
		require (__dir__ .'/../_3rd/PHPMailer/PHPMailerAutoload.php');
		global $MAILCFG;

        $res= $this->flexUser->resetPassword($_email);
        if (!$res)
            return $this->flexErrorGetLast();


//  todo 10 (api, clean, dict) +0: use dict messages
        $srv= $_SERVER['SERVER_NAME'];
        $mailMessage= "<html><body>Добрый день,<br><br>Для вашего аккаунта на $srv запрошено восстановление пароля.<br>Пройдите по <a href=http://{$srv}/?=reset&hash={$res->Confirmation}&email=$_email>ЭТОЙ ССЫЛКЕ</a>, чтобы установить новый пароль.<br><br>Если вы не запрашивали изменение пароля, то просто проигнорируйте это письмо.<br><br><a href=http://$srv>$srv</a></body></html>";

        $mail = new PHPMailer;
        $mail->IsSMTP();
        $mail->CharSet = 'UTF-8';

        $mail->Host       = $MAILCFG->SMTP;
        $mail->SMTPAuth   = true;
        $mail->SMTPSecure = "ssl";
        $mail->Port       = 465;
        $mail->Username   = $MAILCFG->USER;
        $mail->Password   = $MAILCFG->PASS;

        $mail->setFrom($MAILCFG->USER, 'Красные Кости');
        $mail->addAddress($_email);
        $mail->Subject = 'Восстановление пароля';
        $mail->msgHTML($mailMessage);

        if (! $mail->send())
            return 'Ошибка восстановления';
	}



/*
Set new password.
*/
	function passNew($_email, $_pass, $_hash){
        $res = $this->flexUser->newPassword($_hash,['Password'=>$_pass]);
        if (!$res)
        	return $this->flexErrorGetLast();

        return $this->passLogin($_email, $_pass);
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
		$this->flexUser->config->database->pdo= KiSql::getPDO();

		if (!$this->flexUser->start()->isSigned())
			return;

		return $this->flexUser;
	}



/*
Check if social user is signed.
Soc user init assumes normal user is not logged, and thus user data from assigned one will be fetched, including local userID (differed from social userID's).
*/
	private function initSocUser($_socialCfg){
		$this->socUser= new KiAuthSoc($_socialCfg);

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
		$stmt= KiSql::apply('kisqlGetSocial', $_soc->type, $_soc->id);
		$id_assigned= KiSql::fetch('id_users', 0);

//  todo 7 (ux, socal, unsure) -1: probably update user data from social

		if (!$id_assigned){
			$userData= $_soc->fetch();
			if (!$userData) //  todo 6 (error) +0: deal with error
			 	return;

			$id_assigned= $this->assignedCreate($_soc);
		}

		return $id_assigned;
	}



/*
Create implicit logpass user for given social one.
*/
	private function assignedCreate($_userData){
		$stmt= KiSql::apply('kisqlAdd', time(), $_userData->firstName, $_userData->photoUrl);
		$id_assigned= KiSql::lastInsertId();


		$stmt= KiSql::apply('kisqlAddSocial', $this->socUser->type, $this->socUser->id, $id_assigned);

		return $id_assigned;
	}



/*
Update last logon state.
*/
	private function assignedUpdate($_id){
		$stmt= KiSql::apply('kisqlUpdateLast', time(), $_id);
	}






/*
Since uFlex dont return error codes, we must assign them based on error message.
Suitable for uFlex v1.0.7.
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
	private function flexErrorGetLast(){
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