<?
/*
Context object
*/
class Ki_RouteCtx {
	var $name='', $codeA=[], $headersA=[], $return=0;
	var $varsA=[];



/*
Run prepared code and variables into KiHandler
*/
	function runCtx(){
		$cContentA = [];

		//run all code
		foreach ($this->codeA as $cSrc) {
			$cCont = $this->runContent($cSrc, $this->varsA);
			if (is_string($cCont))
				$cContentA[] = $cCont;
		}

		KiHandler::setContent($this->name, implode('', $cContentA));

		foreach ($this->headersA as $hName=>$hVal)
			KiHandler::setHeader($hName, $hVal);

		if ($this->return)
			KiHandler::setReturn($this->return);
	}



/*
Solve registered code generators for specified context.
*/
	private function runContent($_src, $_vars){
		if (is_callable($_src)){
			ob_start(); //nest buffer

			$res = call_user_func($_src, (object)$_vars);

			return ob_get_clean() . (string)$res;
		}


		if (is_file($_src)){
			ob_start(); //nest buffer

			$res = include($_src);

			return ob_get_clean();
		}


		return (string)$_src;
	}
}






/*
Context bind class
*/
class Ki_RouteBind {
	static $bindA=[], $bind404A=[];


	var $urlA=[], $ctxA=[], $return=0, $headersA;
	var $varsA=[];



	private static function addBind($_cBind, $_is404){
		if ($_is404)
			self::$bind404A[] = $_cBind;
		else
			self::$bindA[] = $_cBind;
	}






// =todo 135 (routing, context, bind) +0: make lazy-bond context at Ki_RouteBind creation
	function __construct($_urlA, $_ctxA=[]){
		if (!is_array($_urlA))
			$_urlA = [$_urlA];

		if (!is_array($_ctxA))
			$_ctxA = [$_ctxA];


		//detect 404 case
		$is404 = ($_urlA[0]===404);
		if ($is404)
			array_shift($_urlA);

		$this->urlA = $_urlA;
		$this->ctxA = $_ctxA;
		$this->return = 0;
		$this->headersA = [];

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