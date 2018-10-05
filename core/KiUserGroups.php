<?
/*
Manage abstract user groups.
Grouping is not used by itself within KiFrame.
*/
// -todo 90 (groups) +0: split KiGroups to group-managing and user-managing
class KiGroups {
	private static $DBA = [
		'getGroupsClasses' => 'SELECT * from users_groups_classes',
		'getGroupsUser' => 'SELECT * from users_groups_assign WHERE id_user=?',
		'getGroups' => 'SELECT * from users_groups WHERE id IN (?)'
	];


	private static $isInited;

	private static $classesA, $groupsA;


	private $id=0, $assignedA;



	function __construct(){
		self::init();


		$this->assignedA = [];
	}



/*
Fetch groups assignment for user.
Fetch all groups definitions needed.
*/
	function fetch($_id=0){
		$this->id = $_id;

		if (!$this->id)
			return;


		KiSql::apply('getGroupsUser', $this->id);
		$assignedA = [];
		while ($cVal = KiSql::fetch())
			$assignedA[] = $cVal['id_group'];

	
		$reqGroupsA = array_diff($assignedA, array_keys(self::$groupsA));

		KiSql::apply('getGroups', $reqGroupsA);
		while ($cVal = KiSql::fetch())
			self::$groupsA[$cVal['id']] = (object)['id'=>$cVal['id'], 'name'=>$cVal['name'], 'class'=>self::$classesA[$cVal['id_class']]];


		$this->assignedA = [];
		foreach ($assignedA as $cId)
			$this->assignedA[] = self::$groupsA[$cId];
	}



/*
Get specified groups.
Get all groups if none specfied.
*/
	function get($_idA=[]){
		if ($_idA==[])
			return $this->assignedA;


		if (!is_array($_idA))
			$_idA = [$_idA];

		$outA = [];
		foreach ($this->assignedA as $cGrp)
			if (array_search($cGrp->id, $_idA)!==False)
				$outA[] = $cGrp->id;


		return $outA;
	}



//  todo 85 (groups) +0: add KiGroups->set()
	function set(){
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
			return
		self::$isInited = True;


		KiSql::add(self::$DBA);


		KiSql::apply('getGroupsClasses');
		
		self::$groupsA = []; //fill groups as requested

		self::$classesA = [];
		while ($cRow= KiSql::fetch())
			self::$classesA[$cRow['id']] = (object)['id'=>$cRow['id'], 'class'=>$cRow['class']];
	}
}

?>
