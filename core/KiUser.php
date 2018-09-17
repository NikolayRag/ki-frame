<?
include(__dir__ .'/kiUserAccount.php');
include(__dir__ .'/kiUserRights.php');


/*
User data holder
*/
class KiUser {
// -todo 70 (auth) +0: move custom fields (photo, name) to account
	var $isSigned=false, $id=0, $name='', $email='', $photo='', $mask=0, $rights;
	var $accountO;



	function __construct(){
		$this->accountO = new KiAccount();
	}



/*
Apply data from fetched uFlex user.
*/
	function apply($_userData){
		$this->isSigned= true;
		$this->id= $_userData->ID;
		$this->email= $_userData->Email;

		($this->name= $_userData->displayName) || ($this->name= $_userData->Email);
		$this->photo= $_userData->photoURL;
		$this->mask= $_userData->mask;

		$this->accountO->fetch($_userData->ID);
	}



	function reset(){
		$this->isSigned= False;

		$this->id= 0;
		$this->email= '';

		$this->name= '';
		$this->photo= '';
		$this->mask= 0;
	}



	function account($_field){
		return $this->accountO->get($_field);
	}



	function accountSet($_field){
		return $this->accountO->set($_field);
	}
}
?>