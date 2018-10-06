<?
include(__dir__ .'/kiUserAccount.php');
include(__dir__ .'/kiUserGroups.php');
include(__dir__ .'/kiUserRights.php');


/*
User data holder
*/
class KiUser {
// -todo 70 (auth) +0: move custom fields (photo, name) to account
// -todo 92 (auth) +0: add implicit social (photo, name) fields in addition
//  todo 7 (ux, socal) -1: add function to update user data from social
	var $isSigned=false, $id=0, $name='', $email='', $photo='';
	var $accountO, $rightsO, $groupsO;



//  todo 84 (account) +0: support applied ID at KiUser creation
	function __construct(){
		$this->reset();
	}



/*
Apply data from fetched uFlex user.
*/
	function apply($_userData){
		$this->isSigned= true;
		$this->id= $_userData->ID;

// -todo 83 (account) +0: move email (protected), display name and photo url to account
		$this->email= $_userData->Email;

		($this->name= $_userData->displayName) || ($this->name= $_userData->Email);
		$this->photo= $_userData->photoURL;

		$this->accountO->fetch($_userData->ID);
		$this->groupsO->fetch($_userData->ID);
	}



	function reset(){
		$this->isSigned= False;

		$this->id= 0;
		$this->email= '';

		$this->name= '';
		$this->photo= '';

		$this->accountO = new KiAccount();
		$this->groupsO = new KiGroups();
		$this->rightsO = new KiRights($this);
	}



// -todo 76 (clean, auth) +0: make account get/set reliable
	function account($_field){
		return $this->accountO->get($_field);
	}



	function accountState(){
		return $this->accountO->getState();
	}



	function accountSet($_field){
		return $this->accountO->set($_field);
	}



	function groups($_idA){
		return $this->groupsO->get($_idA);
	}



	function rights(){
		return $this->rightsO;
	}
}
?>
