<?
/*
User Account holder
*/
// -todo 91 (account) +0: split KiAccount to account-managing and user-assignment
class KiAccount {
	private static $DBA = [
		'getAccountFields' => 'SELECT * from users_account_fields',
		'getAccount' => 'SELECT * from users_account WHERE id_user=?',
		'setAccount' => 'REPLACE INTO users_account (id_user,id_field,value) VALUES (?,?,?)'
	];


	private static $isInited;

	private static $fieldsA;


	private $id=0, $accountA, $state;



	function __construct($_id){
		self::init();


		$this->accountA = [];
		foreach (self::$fieldsA as $fId=>$fName)
			$this->accountA[$fId] = '';


		$this->id = $_id;

		if ($_id)
			$this->fetch($_id);
	}



	function fetch($_id){
		KiSql::apply('getAccount', $this->id);
		while ($cFieldsA = KiSql::fetch()){
			$this->state = True;

			$this->accountA[$cFieldsA['id_field']] = $cFieldsA['value'];
		}
	}



	static function init(){
		if (self::$isInited)
			return;
		self::$isInited = True;


		KiSql::add(self::$DBA);


		KiSql::apply('getAccountFields');

		self::$fieldsA = [];
		while ($cRow= KiSql::fetch())
			self::$fieldsA[$cRow['id']] = $cRow['name'];
	}



//  todo 128 (clean, account) +0: decide about named or id'd account get/set 
	function get($_id=False, $_default=''){
		if ($_id===False)
			return $this->accountA;

		if (is_string($_id))
			$_id = array_search($_id, self::$fieldsA);

		if ($_id!==False)
			return getA($this->accountA, $_id, $_default);
	}



	function set($_data=False, $store=True){
		if (!$_data)
			return $this->accountA;

		if (!is_array($_data))
			return;


		foreach ($_data as $cId=>$cVal){
			$n = $cId;

			if (is_string($cId))
				$cId = array_search($cId, self::$fieldsA);


			if ($cId===False){
				self::$fieldsA[] = $n;

				end(self::$fieldsA);
				$cId = key(self::$fieldsA);
			}


			$this->accountA[$cId] = $cVal;

			if ($store and $this->id)
				KiSql::apply('setAccount', $this->id, $cId, $cVal);
		}


		$this->state = True;
	}



	function getState(){
		return $this->state;
	}
}
?>