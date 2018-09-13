<?
/*
User data holder
*/
// -todo 64 (auth) +0: add KiRights class
class KiUser {
// -todo 70 (auth) +0: move custom fields to account
	var $isSigned=false, $id=0, $name='', $email='', $photo='', $mask=0, $rights;
	var $account;



	function __construct(){
		$this->account = new KiAccount();
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

		$this->account->fetch($_userData->ID);
	}



	function reset(){
		$this->isSigned= False;

		$this->id= 0;
		$this->email= '';

		$this->name= '';
		$this->photo= '';
		$this->mask= 0;
	}
}



/*
User Account holder
*/
class KiAccount {
	private static $isInited;

	private static $fieldsA;
	private $id=0, $accountA;



	function __construct(){
		$this->init();


		$this->accountA = [];
		foreach (self::$fieldsA as $fName=>$fVal)
			$this->accountA[$fName] = '';

	}



	function fetch($_id=0){
		$this->id = $_id;

		if (!$this->id)
			return;


		KiSql::apply('getAccount', $this->id);
		while ($cFieldsA = KiSql::fetch()){
			$fName = array_search($cFieldsA['id_field'], self::$fieldsA);
			$this->accountA[$fName] = $cFieldsA['value'];
		}
	}



	function init(){
		if (self::$isInited)
			return
		self::$isInited = True;


		KiSql::add('getAccountFields', 'SELECT * from users_account_fields');
		KiSql::add('getAccount', 'SELECT * from users_account WHERE id_user=?');
		KiSql::add('setAccount', 'REPLACE INTO users_account (id_user,id_field,value) VALUES (?,?,?)');

		KiSql::apply('getAccountFields');

		while ($cRow= KiSql::fetch())
			self::$fieldsA[$cRow['name']] = $cRow['id'];
	}



	function get($_name=False){
		if (!is_string($_name))
			return $this->accountA;

		return getA($this->accountA, $_name, '');
	}

	function set($_data=False){
		if (!$_data)
			return $this->accountA;

		if (!is_array($_data))
			return;

		foreach (self::$fieldsA as $fName=>$cId){
			$this->accountA[$fName] = $_data[$fName];
			if ($this->id)
				KiSql::apply('setAccount', $this->id, $cId, $_data[$fName]);
		}
	}
}
?>