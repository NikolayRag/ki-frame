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
		foreach (self::$fieldsA as $fName=>$fVal)
			$this->accountA[$fName] = '';


		$this->id = $_id;

		if ($_id)
			$this->fetch($_id);
	}



	function fetch($_id){
		KiSql::apply('getAccount', $this->id);
		while ($cFieldsA = KiSql::fetch()){
			$this->state = True;

			$fName = array_search($cFieldsA['id_field'], self::$fieldsA);
			$this->accountA[$fName] = $cFieldsA['value'];
		}
	}



	static function init(){
		if (self::$isInited)
			return
		self::$isInited = True;


		KiSql::add(self::$DBA);


		KiSql::apply('getAccountFields');

		self::$fieldsA = [];
		while ($cRow= KiSql::fetch())
			self::$fieldsA[$cRow['name']] = $cRow['id'];
	}



	function get($_name=False){
		if (!is_string($_name))
			return $this->accountA;

		return getA($this->accountA, $_name, '');
	}



	function set($_data=False, $store=True){
		if (!$_data)
			return $this->accountA;

		if (!is_array($_data))
			return;


		foreach ($_data as $n=>$v){
			$this->accountA[$n] = $v;

			if ($store and $this->id and array_key_exists($n, self::$fieldsA))
				KiSql::apply('setAccount', $this->id, self::$fieldsA[$n], $v);
		}


		$this->state = True;
	}



	function getState(){
		return $this->state;
	}
}
?>