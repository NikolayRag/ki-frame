<?
/*
User Account holder
*/
class KiAccount {
	private static $isInited;

	private static $fieldsA;


	private $id=0, $accountA;



	function __construct(){
		self::init();


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



	static function init(){
		if (self::$isInited)
			return
		self::$isInited = True;


		KiSql::add('getAccountFields', 'SELECT * from users_account_fields');
		KiSql::add('getAccount', 'SELECT * from users_account WHERE id_user=?');
		KiSql::add('setAccount', 'REPLACE INTO users_account (id_user,id_field,value) VALUES (?,?,?)');

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