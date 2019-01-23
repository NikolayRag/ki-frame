<?
/*
Context bind class
*/
class KiRouteBind {
	static $bindA=[], $bind404A=[];


	var $urlA=[], $ctxA=[], $return=0, $headersA;
	var $varsA=[];



	private static function addBind($_cBind, $_is404){
		if ($_is404)
			self::$bind404A[] = $_cBind;
		else
			self::$bindA[] = $_cBind;
	}



/*
Detect all matching URL bindings.
*/
	static function matchUrl($_non404=True){
		$cBindA = $_non404? self::$bindA : self::$bind404A;

		//collect detected url's
		$bondA = [];
		foreach ($cBindA as $cBind)
			if ($cBind->match())
				$bondA[] = $cBind;

		return $bondA;
	}



// =todo 108 (bind, context) +0: allow to bind context, function, file, or code inlined.
	function __construct($_urlA, $_ctxA=[], $_code=0, $_headersA=[]){
		if (!is_array($_urlA))
			$_urlA = [$_urlA];

		if (!is_array($_ctxA))
			$_ctxA = [$_ctxA];

		if (!is_array($_headersA))
			$_headersA = [$_headersA];


		//detect 404 case
		$is404 = ($_urlA[0]===404);
		if ($is404)
			array_shift($_urlA);


		$this->urlA = $_urlA;
		$this->ctxA = $_ctxA;
		$this->return = $_code;
		$this->headersA = $_headersA;


		self::addBind($this, $is404);
	}



	function match(){
		$lost = False;
		$varsA = [];
		foreach ($this->urlA as $cUrl) {
			$found = False;
			if (is_callable($cUrl)){ //function binding
				$fRes = $cUrl();
				if (($fRes !== False) && ($fRes !== Null) && ($fRes !== 0)){
					$found = True;
					if (is_array($fRes))
						$varsA = array_merge($varsA, $fRes);
				}
			} else if (is_string($cUrl) and ($cUrl[0]=='/' or $cUrl[0]=='?')){ //regex binding
				if ($cUrl[0]=='/'){ //path
					$cRegex = str_replace('/', '\/', $cUrl);
					$cRes = [];
					
					if (preg_match("/^$cRegex$/", KiUrl::path(True), $cRes)){
						$found = True;
						$varsA = array_merge($varsA, $cRes);
					}
				}
				if ($cUrl[0]=='?'){ //arg
					foreach (KiUrl::args()->all() as $cName => $cVal) {
						$cRes = [];
						
						if (preg_match("/^\\$cUrl$/", "?$cName=$cVal", $cRes)){
							$found = True;
							$varsA = array_merge($varsA, $cRes);
						}

					}
				}
			} else if (!!$cUrl){
				$found = True;
			}


			$lost = $lost || !$found;
			if ($lost)
				break;
		}


		if (!$lost){
			foreach ($varsA as $key=>$val)
			    if (is_int($key)) 
			        unset($varsA[$key]);

			$this->varsA = $varsA;
		}

		return !$lost;
	}
}

?>