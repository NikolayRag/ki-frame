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



	function get($_name=False){
		if (!is_string($_name))
			return $this->accountA;

		$id = array_search($_name, self::$fieldsA);
		if ($id!==False)
			return getA($this->accountA, $id, '');
	}



	function set($_data=False, $store=True){
		if (!$_data)
			return $this->accountA;

		if (!is_array($_data))
			return;


		foreach ($_data as $cName=>$cVal){
			$cId = array_search($cName, self::$fieldsA);

			if ($cId===False){
				self::$fieldsA[] = $cName;

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