<?php
// =todo 38 (sql, check) +0: check KiAuth for use with KiSql

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



include(__dir__ .'/ki-rights.php');
include(__dir__ .'/KiAuthPass.php');
include(__dir__ .'/KiAuthSoc.php');



KiSql::add('kiAuthGetSocial', 'SELECT id_users FROM users_social WHERE type=? AND id=?');
KiSql::add('kiAuthAdd', 'INSERT INTO users (auto_social,RegDate,displayName,photoURL) VALUES (1,?,?,?)');
KiSql::add('kiAuthAddSocial', 'INSERT INTO users_social (type,id,id_users) VALUES (?,?,?)');
KiSql::add('kiAuthUpdateLast', 'UPDATE users SET LastLogin=? WHERE ID=?');



// -todo 23 (ux, auth) +0: introduce entire auth cached timeout
class KiAuth {
	private static $isInited;

	private $isSocUser=false, $mask=0, $rights;

// -todo 40 (auth) +0: add Ki_User class
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

		return KiAuthSoc::loginURL();
	}



/*
API cb for social logon.
*/
	function socCB(){
		$socErr= KiAuthSoc::socCB();
		if ($socErr)
			return;

		$xId= $this->assignedGet();
		if (!$xId) // -todo 6 (auth, catch) +0: deal with social callback error
		 	return;

		$this->assignedUpdate($xId); //update last logon state

		$this->applyUser(KiAuthPass::getData($xId));
	}



/*
Regster new email/pass user and login.
*/
	function passRegister($_email, $_pass){
        $res = KiAuthPass::register([
            'Email'=>$_email,
            'Password'=>$_pass
        ]);
        if (!$res)
        	return KiAuthPass::flexErrorGetLast();

        return $this->passLogin($_email, $_pass);
}



/*
Log in with email/pass
*/
	function passLogin($_email, $_pass){
        KiAuthPass::login($_email, $_pass);

        return KiAuthPass::flexErrorGetLast();
    }



/*
Logout either.
*/
	function logout(){
		$this->isSocUser?
			KiAuthSoc::logout() :
			KiAuthPass::logout();

//  todo 8 (auth, api) -1: vary logout errors
		return;
	}



/*
Get pass restoring hash.
Return hash string OR uflex error code.
*/
	function passRestore($_email){
        $res= KiAuthPass::resetPassword($_email);
        if (!$res)
            return KiAuthPass::flexErrorGetLast();

        return $res->Confirmation;
	}



/*
Set new password.
*/
	function passNew($_email, $_pass, $_hash){
        $res = KiAuthPass::newPassword($_hash,$_pass);
        if (!$res)
        	return KiAuthPass::flexErrorGetLast();

        return $this->passLogin($_email, $_pass);
	}



/*
	PRIVATE
*/



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
		if (!KiAuthPass::start(KiSql::getPDO()))
			return;

		return KiAuthPass::$user;
	}



/*
Check if social user is signed.
Soc user init assumes normal user is not logged, and thus user data from assigned one will be fetched, including local userID (differed from social userID's).
*/
	private function initSocUser($_socialCfg){
		$this->isSocUser= True;
		if (!KiAuthSoc::start($_socialCfg))
			return;

		$xId= $this->assignedGet();
		if (!$xId) //  todo 5 (auth, catc) +0: deal with social init error
			return;

		return KiAuthPass::getData($xId);
	}



/*
Fetch assigned logpass user for given social.
If none logpass user is assigned, implicit one is created and assigned.

Return user id.
*/
	private function assignedGet(){
		$stmt= KiSql::apply('kiAuthGetSocial', KiAuthSoc::$type, KiAuthSoc::$id);
		$id_assigned= KiSql::fetch('id_users', 0);


		if (!$id_assigned){
			$userData= KiAuthSoc::fetch();
			if (!$userData) //  todo 55 (auth, catch) +0: deal with acces user data error
			 	return;

			$id_assigned= $this->assignedCreate(KiAuthSoc::$type, KiAuthSoc::$id, KiAuthSoc::$firstName, KiAuthSoc::$photoURL);
		}

		return $id_assigned;
	}

//  todo 7 (ux, socal) -1: add function to update user data from social


/*
Create implicit logpass user for given social one.
*/
	private function assignedCreate($_type,$_id, $_name, $_photo){
		$stmt= KiSql::apply('kiAuthAdd', time(), $_name, $_photo);
		$id_assigned= KiSql::lastInsertId();


		$stmt= KiSql::apply('kiAuthAddSocial', $_type, $_id);

		return $id_assigned;
	}



/*
Update last logon state.
*/
	private function assignedUpdate($_id){
		$stmt= KiSql::apply('kiAuthUpdateLast', time(), $_id);
	}

}
?>