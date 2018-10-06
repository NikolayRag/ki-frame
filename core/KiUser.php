<?
include(__dir__ .'/kiUserAccount.php');
include(__dir__ .'/kiUserGroups.php');
include(__dir__ .'/kiUserRights.php');


/*
User data holder
*/
class KiUser {
//  todo 7 (ux, socal) -1: add function to update user data from social
	var $isSigned=false, $id=0, $liveEmail='', $livePhoto='', $liveName='';
	private $accountO, $rightsO, $groupsO;



//  todo 84 (account) +0: support applied ID at KiUser creation
	function __construct(){
		$this->reset();
	}



/*
Apply data from fetched uFlex user.
*/
	function apply($_id, $_liveEmail='', $_livePhoto='', $_liveName=''){
		$this->isSigned = true;
		$this->id = $_id;
		$this->liveEmail = $_liveEmail;
		$this->livePhoto = $_livePhoto;
		$this->liveName = $_liveName;

		$this->accountO->fetch($_id);
		$this->groupsO->fetch($_id);
	}



	function reset(){
		$this->isSigned= False;
		$this->id= 0;
		$this->liveEmail = '';
		$this->livePhoto = '';
		$this->liveName = '';

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
