<?php
// -todo 65 (ux, auth) +0: append logged social user to existing logpass
// -todo 66 (ux, auth) +0: append social user to logged logpass
include(__dir__ .'/KiUser.php');
include(__dir__ .'/KiAuthPass.php');
include(__dir__ .'/KiAuthSoc.php');



/*
Deal with user authorization - social and explicit, and rights assignment.
Actual user data is stored in logpass (uFlex) account.
Each soc user is assigned to logpass account.
Blank logpass account is assigned implicitely to new successfull soc login, and is not valid to log in with login/password actually.

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



// -todo 23 (ux, auth) +0: introduce entire auth cached timeout
class KiAuth {
	private static $sqlA = [
		'kiAuthGetSocial' => 'SELECT id_users FROM users_social WHERE type=?+0 AND id=?',
		'kiAuthAdd' => 'INSERT INTO users (auto_social,RegDate,displayName,photoURL) VALUES (1,?,?,?)',
		'kiAuthAddSocial' => 'INSERT INTO users_social (type,id,id_users) VALUES (?,?,?)',
		'kiAuthUpdateLast' => 'UPDATE users SET LastLogin=? WHERE ID=?'
	];


	private static $isInited;
	private static $isSocUser=false;

	static $user;



	static function init($_socialCfg){
		if (self::$isInited)
			return
		self::$isInited = True;


		KiSql::addSome(self::$sqlA);

		KiAuthSoc::init($_socialCfg);


		self::$user = new KiUser();
		($cUser= self::initFlexUser()) || ($cUser= self::initSocUser());
		if ($cUser)
			self::$user->apply($cUser);
	}



/*
Return suitable social login URL's.
*/
	static function socUrlA(){
		return KiAuthSoc::loginURL();
	}



/*
API cb for social logon.
*/
	static function socCB($_type, $_args){
		$socErr= KiAuthSoc::socCB($_type, $_args);
		if ($socErr)
			return;

		$xId= self::assignedGet();
		if (!$xId) // -todo 6 (clean, auth) +0: deal with social callback error
		 	return;

		self::assignedUpdate($xId); //update last logon state

		self::$user->apply(KiAuthPass::getData($xId));
	}



/*
Regster new email/pass user and login.
*/
	static function passRegister($_email, $_pass){
        $res = KiAuthPass::register($_email,$_pass);
        if (!$res)
        	return KiAuthPass::getError();

        return self::passLogin($_email, $_pass);
}



/*
Log in with email/pass
*/
	static function passLogin($_email, $_pass){
        $res = KiAuthPass::login($_email, $_pass);
        if (!$res)
	        return KiAuthPass::getError();

		self::$user->apply(KiAuthPass::$user);
    }



/*
Logout either.
*/
	static function logout(){
		KiAuthSoc::logout();
		KiAuthPass::logout();

		self::$user->reset();

//  todo 8 (clean, auth) -1: vary logout errors
		return;
	}



/*
Get pass restoring hash.
Return hash string OR uflex error code.
*/
	static function passRestore($_email){
        $res= KiAuthPass::resetPassword($_email);
        if (!$res)
            return KiAuthPass::getError();

        return $res->Confirmation;
	}



/*
Set new password.
*/
	static function passNew($_email, $_pass, $_hash){
        $res = KiAuthPass::newPassword($_hash,$_pass);
        if (!$res)
        	return KiAuthPass::getError();

        return self::passLogin($_email, $_pass);
	}



/*
	PRIVATE
*/



/*
Check if logpass user is signed.
*/
	private static function initFlexUser(){
		if (!KiAuthPass::start(KiSql::getPDO()))
			return;

		return KiAuthPass::$user;
	}



/*
Check if social user is signed.
Soc user init assumes normal user is not logged, and thus user data from assigned one will be fetched, including local userID (differed from social userID's).
*/
	private static function initSocUser(){
		self::$isSocUser= True;
		if (!KiAuthSoc::start())
			return;

		$xId= self::assignedGet();
		if (!$xId) //  todo 5 (clean, auth) +0: deal with social init error
			return;

		return KiAuthPass::getData($xId);
	}



/*
Fetch assigned logpass user for given social.
If none logpass user is assigned, implicit one is created and assigned.

Return user id.
*/
	private static function assignedGet(){
		KiSql::apply('kiAuthGetSocial', KiAuthSoc::$type, KiAuthSoc::$id);
		$id_assigned= KiSql::fetch('id_users', 0);


		if (!$id_assigned){
			if (!KiAuthSoc::fetch()) //  todo 55 (clean, auth) +0: deal with acces user data error
			 	return;

			$id_assigned= self::assignedCreate(KiAuthSoc::$type, KiAuthSoc::$id, KiAuthSoc::$firstName, KiAuthSoc::$photoUrl);
		}

		return $id_assigned;
	}



/*
Create implicit logpass user for given social one.
*/
	private static function assignedCreate($_type,$_id, $_name, $_photo){
		KiSql::apply('kiAuthAdd', time(), $_name, $_photo);
		$id_assigned= KiSql::lastInsertId();

		KiSql::apply('kiAuthAddSocial', $_type, $_id, $id_assigned);

		return $id_assigned;
	}



/*
Update last logon state.
*/
	private static function assignedUpdate($_id){
		KiSql::apply('kiAuthUpdateLast', time(), $_id);
	}

}
?>