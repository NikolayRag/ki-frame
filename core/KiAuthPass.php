<?
/*
Authorize by log/pass.
*/
class KiAuthPass {
	private static $isInited;

	static $user;



	static function start($_db){
		if (self::$isInited)
			return;
		self::$isInited = True;


		self::init();


		self::$user= new \ptejada\uFlex\User();
		self::$user->config->database->pdo= $_db;

		if (!self::$user->start()->isSigned())
			return;

		return self::$user;
	}



	static function register($_email, $_pass){
		//prepare username, coz uFlex refuse blank usernames
        $stmt= KiSql::apply('kiAuthGetCount');
        $arr= KiSql::fetch();

		return self::$user->register([
			'Username'=> "u_{$arr[0]}",
			'Email'=>$_email,
			'Password'=>$_pass
		]);
	}



	static function login($_email, $_pass){
		self::$user->login($_email, $_pass, true);
	}



	static function logout(){
		self::$user->logout();
	}



	static function resetPassword($_email){
		return self::$user->resetPassword($_email);
	}



	static function newPassword($_hash,$_pass){
		return self::$user->newPassword($_hash,['Password'=>$_pass]);
	}



	static function getData($_id){
		return self::$user->manageUser($_id);
	}






/*
	Private
*/



	private static function init(){
		KiSql::add('kiAuthGetCount', 'SELECT count(*) FROM Users');


		//include(__dir__ .'/../_3rd/uflex/autoload.php');

		include(__dir__ .'/../_3rd/uflex/src/collection.php');
		include(__dir__ .'/../_3rd/uflex/src/linkedcollection.php');
		include(__dir__ .'/../_3rd/uflex/src/cookie.php');
		include(__dir__ .'/../_3rd/uflex/src/db.php');
		include(__dir__ .'/../_3rd/uflex/src/db_table.php');
		include(__dir__ .'/../_3rd/uflex/src/hash.php');
		include(__dir__ .'/../_3rd/uflex/src/log.php');
		include(__dir__ .'/../_3rd/uflex/src/userbase.php');
		include(__dir__ .'/../_3rd/uflex/src/user.php');
		include(__dir__ .'/../_3rd/uflex/src/session.php');
	}









/*
Error code dereferencing.

Since uFlex dont return error codes, this will assign them based on error message.
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
	private static function getError(){
		$err= Null;
		foreach (self::$user->log->getAllErrors() as $err);

		$cErr= Null;
		if ($err)
			foreach ($err as $cErr);

		return self::$flexErrorDeref($cErr);
	}



/*
Restore error id from text message.
*/
	private static function flexErrorDeref($_err){
		if (!$_err)
			return;


		foreach (self::$flexErrA as $errId=>$cTest){
			if (preg_match($cTest, $_err))
				return $errId;
		}
	}
}
?>