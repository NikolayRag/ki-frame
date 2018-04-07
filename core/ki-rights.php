<?

/*
Rights object provide high-level rights info of user himself
 and in conjunction with event data.

*/


class Rights {
	var $group, $isAssigned=0, $banA= [],

	$result= false;

	function Rights($_group, $_step=0, $_isAssigned=0){
		$this->group= $_group;
		$this->applyEvent($_step, $_isAssigned);
	}


	function applyEvent($_step, $_isAssigned){
		global $FLOWSTEP, $USER_GROUPS, $ERRRES;

		$this->isAssigned= $_isAssigned;

		foreach ($FLOWSTEP as $cStep){
			$this->banA[$cStep]= $ERRRES->OK;

			switch ($cStep){
				case $FLOWSTEP->LIST:
					$this->check($ERRRES->EGROUP,
						1 ||
						$this->group &$USER_GROUPS->VIEW ||
						$this->group &$USER_GROUPS->EDIT ||
						$this->group &$USER_GROUPS->OPERATE ||
						$this->admin()
						,$cStep);
					break;

				case $FLOWSTEP->ADD:
					$this->check($ERRRES->EGROUP,
						$this->group &$USER_GROUPS->EDIT
						,$cStep);
					break;
	

				case $FLOWSTEP->PLAN:
					$this->check($ERRRES->EOWN, $this->own()
						,$cStep) &&
					$this->check($ERRRES->EGROUP, $this->group &$USER_GROUPS->EDIT
						,$cStep) &&
					$this->check($ERRRES->ESTEP, $_step==$FLOWSTEP->PLAN
						,$cStep);
					break;

				case $FLOWSTEP->REPORT:
					$this->check($ERRRES->EOWN, $this->own()
						,$cStep) &&
					$this->check($ERRRES->EGROUP, $this->group &$USER_GROUPS->EDIT
						,$cStep) &&
					$this->check($ERRRES->ESTEP, 
						$_step==$FLOWSTEP->REPORT ||
						$_step==$FLOWSTEP->PLAN
						,$cStep);
					break;

				case $FLOWSTEP->DELETED:
					$this->check($ERRRES->EOWN, $this->own()
						,$cStep) &&
					$this->check($ERRRES->EGROUP, $this->group &$USER_GROUPS->EDIT
						,$cStep) &&
					$this->check($ERRRES->ESTEP, 
						$_step==$FLOWSTEP->REPORT ||
						$_step==$FLOWSTEP->PLAN
						,$cStep);
					break;


				case $FLOWSTEP->UNDELETE:
					$this->check($ERRRES->EOWN, $this->own()
						,$cStep) &&
					$this->check($ERRRES->EGROUP, $this->group &$USER_GROUPS->EDIT
						,$cStep) &&
					$this->check($ERRRES->ESTEP, $_step==$FLOWSTEP->DELETED
						,$cStep);
					break;




				case $FLOWSTEP->MARK:
					$this->check($ERRRES->EOWN, $this->own()
						,$cStep) &&
					$this->check($ERRRES->EGROUP, $this->group &$USER_GROUPS->OPERATE
						,$cStep) &&
						(1);
					break;
			}			
		}
	}
	//stores result of rights checking
	function check($_errorCode, $_condition, $_resObj=null){
		global $ERRRES;

		$this->error= $_condition?
			$ERRRES->OK
			: $_errorCode;
		
		if ($_resObj)
			$this->banA[$_resObj]= $this->error;

		return !$this->error;
	}



	public function ban($_action){
		return $this->banA[$_action];
	}

	public function own(){
		global $ERRRES;

		return $this->check($ERRRES->EOWN,
			$this->isAssigned
		);
	}

	public function admin(){
		global $ERRRES;

		global $USER, $USER_GROUPS;
		return $this->check($ERRRES->EGROUP,
			$USER->ID==1 || ($this->group &$USER_GROUPS->ADMIN)
		);
	}
}

?>
