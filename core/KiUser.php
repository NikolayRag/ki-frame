<?
include(__dir__ .'/kiUserAccount.php');
include(__dir__ .'/kiUserGroups.php');
include(__dir__ .'/kiUserRights.php');



/*
User data holder
*/
class KiUser {
	private $id=0;
	private $accountO, $rightsO, $groupsO;



/*
Initialize user.
Account fields, groups assignment and rights are fetched and applied.
*/
	function __construct($_id=0, $_fields=[]){
		$this->id = $_id;

		$this->apply($_fields);
	}



/*
Apply account data from named array.
*/
	function apply($_fields){
		$this->accountO = new KiAccount($this->id);
		$this->groupsO = new KiGroups($this->id);
		$this->rightsO = new KiRights($this);

		$this->accountO->set($_fields, False);
	}



	function __get($_name){
		switch ($_name){
			case 'isSigned':
				return $this->id && ($this->id == KF::user()->id);

			case 'id':
				return $this->id;

			case 'isAuto':
				return $this->account('autoSocial');
		}
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
