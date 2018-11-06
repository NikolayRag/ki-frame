<?php
// =todo 65 (feature, auth) +0: append social user and logpass
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
	private static $DBA = [
		'kiAuthGetSocial' => 'SELECT id_users,auto FROM users_social WHERE type=?+0 AND id=?',
		'kiAuthAdd' => 'INSERT INTO users (RegDate) VALUES (?)',
		'kiAuthAddSocial' => 'INSERT INTO users_social (type,id,id_users) VALUES (?,?,?)',
		'kiAuthUpdateLast' => 'UPDATE users SET LastLogin=? WHERE ID=?'
	];


	private static $isInited;

	static $user;



	static function init($_socialCfg){
		if (self::$isInited)
			return
		self::$isInited = True;


		KiSql::add(self::$DBA);

		KiAuthSoc::init($_socialCfg);


		self::initFlexUser() || self::initSocUser();
		if (!self::$user)
			self::$user = new KiUser();
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
			return $socErr;

		$socBind = self::assignedGet();
		if (!$socBind) // -todo 6 (clean, auth) +0: deal with social callback error
		 	return True;

		$xId = $socBind['id_users'];
		self::assignedUpdate($xId); //update last logon state


		$cUser = KiAuthPass::getData($xId);

		self::$user = new KiUser($xId, [
			'autoEmail' => $cUser->Email,
			'autoSocial' => $socBind['auto'],
			'autoName' => KiAuthSoc::$liveName,
			'autoPhoto' => KiAuthSoc::$livePhoto,
			'autoType' => KiAuthSoc::$type,
			'autoId' =>KiAuthSoc::$id
		]);
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

		self::$user = new KiUser(KiAuthPass::$user->ID, [
			'autoEmail' => $_email
		]);
    }



/*
Logout either.
*/
	static function logout(){
		KiAuthSoc::logout();
		KiAuthPass::logout();

		self::$user = new KiUser();

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
		$cDB = KiSql::getPDO();
		if (!$cDB)
			return;

		$cUser = KiAuthPass::start($cDB);
		if (!$cUser)
			return;

		self::$user = new KiUser($cUser->ID, [
			'autoEmail' => $cUser->Email
		]);

		return True;
	}



/*
Check if social user is signed.
Soc user init assumes normal user is not logged, and thus user data from assigned one will be fetched, including local userID (differed from social userID's).
*/
	private static function initSocUser(){
		if (!KiAuthSoc::start())
			return;

		$socBind = self::assignedGet();
		if (!$socBind) //  todo 5 (clean, auth) +0: deal with social init error
			return;

		$xId = $socBind['id_users'];
		$cUser = KiAuthPass::getData($xId);
		if (!$cUser)
			return;


		self::$user = new KiUser($xId, [
			'autoEmail' => $cUser->Email,
			'autoSocial' => $socBind['auto'],
			'autoName' => KiAuthSoc::$liveName,
			'autoPhoto' => KiAuthSoc::$livePhoto,
			'autoType' => KiAuthSoc::$type,
			'autoId' =>KiAuthSoc::$id
		]);

		return True;
	}



/*
Fetch assigned logpass user for given social.
If none logpass user is assigned, implicit one is created and assigned.

Return user id.
*/
	private static function assignedGet(){
		KiSql::apply('kiAuthGetSocial', KiAuthSoc::$type, KiAuthSoc::$id);
		$socialBind = KiSql::fetch();


		if (!$socialBind)
			$socialBind = [
				'id_users' => self::assignedCreate(KiAuthSoc::$type, KiAuthSoc::$id),
				'autoSocial' => 1
			];

		return $socialBind;
	}



/*
Create implicit logpass user for given social one.
*/
	private static function assignedCreate($_type,$_id){
		KiSql::apply('kiAuthAdd', time());
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