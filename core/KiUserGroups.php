<?
/*
Manage abstract user groups.
*/
// -todo 90 (groups) +0: split KiGroups user management interface to KiUser
class KiGroups {
	private static $DBA = [
		'getGroups' => 'SELECT * FROM users_groups WHERE id_user=?',
		'setGroups' => 'REPLACE INTO users_groups (id_group,id_user) VALUES (?,?)',
		'delGroups' => 'DELETE FROM users_groups WHERE id_group=? AND id_user=?',
		'getGroupsList' => 'SELECT * from users_groups_list'
	];


	private static $isInited;

	private static $groupsA;


	private $id=0, $assignedA;



	function __construct($_id){
		self::init();


		$this->assignedA = [];

		$this->id = $_id;
		if ($_id)
			$this->fetch($_id);
	}



/*
Fetch groups assignment for user.
*/
	function fetch($_id){
		KiSql::apply('getGroups', $this->id);

		while ($cVal = KiSql::fetch())
			$this->assignedA[$cVal['id_group']] = True;
	}



/*
Get groups list for specified list.
*/
	function get($_idA=False){
		if ($_idA===False)
			return $this->get(array_keys($this->assignedA));


		if (!is_array($_idA))
			$_idA = [$_idA];

		$outA = [];
		foreach ($_idA as $cId)
			if (getA($this->assignedA, $cId))
				$outA[$cId] = self::$groupsA[$cId];


		return $outA;
	}



	function set($_groupIdA){
		if (!is_array($_groupIdA))
			$_groupIdA = [$_groupIdA];


		foreach ($_groupIdA as $cGroupId){
			KiSql::apply('setGroups', $cGroupId, $this->id);

			$this->assignedA[$cGroupId] = True;
		}
	}



	function del($_groupId){
		if (!is_array($_groupIdA))
			$_groupIdA = [$_groupIdA];


		foreach ($_groupIdA as $cGroupId){
			KiSql::apply('delGroups', $cGroupId, $this->id);

			unset($this->assignedA[$cGroupId]);
		}
	}



//  todo 86 (groups) +0: add KiGroups adding new group
	static function groupAdd(){
	}
//  todo 87 (groups) +0: add KiGroup editing group
	static function groupEdit(){
	}
//  todo 88 (groups) +0: add KiGroups removing group
	static function groupDel(){
	}
//  todo 89 (groups) +0: add KiGroups picking by group
	static function groupPick(){
	}




	static function init(){
		if (self::$isInited)
			return;
		self::$isInited = True;


		KiSql::add(self::$DBA);


		//Fetch all groups definitions
		self::$groupsA = [];

		KiSql::apply('getGroupsList');
		while ($cVal = KiSql::fetch())
			self::$groupsA[$cVal['id']] = (object)[
				'id'=>$cVal['id'],
				'name'=>$cVal['name']
			];
	}
}

?>
