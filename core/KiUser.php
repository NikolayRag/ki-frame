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
				return $this->id and $this->account('autoBind')==$this->id;
		}
	}



// -todo 76 (clean, auth) +0: make account get/set reliable
	function account($_field=False, $_default=''){
		return $this->accountO->get($_field, $_default);
	}



	function accountState(){
		return $this->accountO->getState();
	}



	function accountSet($_field){
		return $this->accountO->set($_field);
	}



	function groupGet($_idA=False){
		return $this->groupsO->get($_idA);
	}



	function groupSet($_idA){
		return $this->groupsO->set($_idA);
	}



	function groupDel($_idA){
		return $this->groupsO->del($_idA);
	}



	function rights(){
		return $this->rightsO;
	}



/*
Copy user data to other user:
group assignments are joined,
account data is copied if not set.
*/
	function copy($_to){
		$_to->groupSet( array_keys($this->groupGet()) );

		//merge user accounts, target preferred
		foreach ($this->account() as $aId=>$aVal) {
			if ($_to->account($aId, False)===False)
				$_to->accountSet([$aId=>$aVal]);
		}
	}
}
?>
