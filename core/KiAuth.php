<?php
// =todo 65 (feature, auth) +0: append additional users to signed user
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
		'kiAuthGetSocial' => 'SELECT id_users,id_users_auto FROM users_social WHERE type=?+0 AND id=?',
		'kiAuthAdd' => 'INSERT INTO users (RegDate) VALUES (?)',
		'kiAuthAddSocial' => 'INSERT INTO users_social (type,id,id_users,id_users_auto) VALUES (?,?,?,?)',
		'kiAuthUpdateLast' => 'UPDATE users SET LastLogin=? WHERE ID=?',

		'kiAuthSwitchSocial' => 'UPDATE users_social SET id_users=? WHERE id_users=?',
	];


	private static $isInited;

	static $user;



	static function init($_socialCfg){
		if (self::$isInited)
			return;
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

		$idUser = self::assignedGet();
		if (!$idUser['id_users']) // -todo 6 (clean, auth) +0: deal with social callback error
		 	return True;

		self::assignedUpdate($idUser['id_users']); //update last logon state


		$cUser = KiAuthPass::getData($idUser['id_users']);
		if (!$cUser)
			return;

		self::$user = new KiUser($idUser['id_users'], [
			'autoEmail' => $cUser->Email,
			'autoBind' => $idUser['id_users_auto'],
			'autoName' => KiAuthSoc::$liveName,
			'autoPhoto' => KiAuthSoc::$livePhoto,
			'autoType' => KiAuthSoc::$type,
			'autoSocialId' =>KiAuthSoc::$id
		]);

		//  todo 123 (feature, auth) +0: bind social to logged user
	}



/*
Regster new email/pass user and login.
*/
	static function passRegister($_email, $_pass, $_bind=False){
        $res = KiAuthPass::register($_email,$_pass);
        if (!$res)
        	return KiAuthPass::getError();

        return self::passLogin($_email, $_pass, $_bind);
}



/*
Log in with email/pass
*/
	static function passLogin($_email, $_pass, $_bind=False){
		if (!$_bind && self::$user->id!=0)
			return;


        $res = KiAuthPass::login($_email, $_pass);
        if (!$res)
	        return KiAuthPass::getError();


		$newUser = new KiUser(KiAuthPass::$user->ID, [
			'autoEmail' => $_email
		]);

		if (self::$user->isSigned && self::isAutoSocial())
			self::rebindUser($newUser);

		self::$user = $newUser;
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
Check if current social logged user have no binding
*/
	static function isAutoSocial(){
		return self::$user->id and self::$user->account('autoBind')==self::$user->id;
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

		$idUser = self::assignedGet();
		if (!$idUser['id_users']) //  todo 5 (clean, auth) +0: deal with social init error
			return;

		$cUser = KiAuthPass::getData($idUser['id_users']);
		if (!$cUser)
			return;


		self::$user = new KiUser($idUser['id_users'], [
			'autoEmail' => $cUser->Email,
			'autoBind' => $idUser['id_users_auto'],
			'autoName' => KiAuthSoc::$liveName,
			'autoPhoto' => KiAuthSoc::$livePhoto,
			'autoType' => KiAuthSoc::$type,
			'autoSocialId' =>KiAuthSoc::$id
		]);

		return True;
	}



/*
Replace signed autosocial user with given logpass user.
*/
	private static function rebindUser($_toUser){
		KiSql::apply('kiAuthSwitchSocial', $_toUser->id, self::$user->id);

		self::$user->copy($_toUser);
	}



/*
Fetch assigned logpass user for given social.
If none logpass user is assigned, implicit one is created and assigned.

Return [userId, autoId].
*/
	private static function assignedGet(){
		KiSql::apply('kiAuthGetSocial', KiAuthSoc::$type, KiAuthSoc::$id);
		$socialBind = KiSql::fetch();
		if (!$socialBind){
			$newId = self::assignedCreate(KiAuthSoc::$type, KiAuthSoc::$id);
			$socialBind = [
				'id_users'=>$newId,
				'id_users_auto'=>$newId
			];
		}

		return $socialBind;
	}



/*
Create implicit logpass user for given social one.
*/
	private static function assignedCreate($_type,$_id){
		KiSql::apply('kiAuthAdd', time());
		$id_assigned= KiSql::lastInsertId();

		KiSql::apply('kiAuthAddSocial', $_type, $_id, $id_assigned, $id_assigned);

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