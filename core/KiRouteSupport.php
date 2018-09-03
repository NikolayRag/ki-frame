<?
/*
Context object
*/
//  todo 60 (code) +0: expand Ki_RouteCtx into normal class
class Ki_RouteCtx {
	var $name='', $codeA=[], $headersA=[], $return=0;
	var $varsA=[];



/*
Run prepared code and variables into KiHandler
*/
	function run(){
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
			if (!is_string($res))
				$res = '';

			return ob_get_clean() . $res;
		}


		if (is_file($_src)){
			ob_start(); //nest buffer

			include($_src);

			return ob_get_clean();
		}


		return $_src;
	}
}






/*
Context bind class
*/
// -todo 57 (code) +0: expand Ki_RouteBind into normal class.
class Ki_RouteBind {
	var $urlA=[], $ctxA=[], $return=0, $headersA;
	var $varsA=[];


	function __construct($_urlA, $_ctx=[]){
		$this->urlA = $_urlA;
		$this->ctxA = $_ctx;
		$this->return = 0;
		$this->headersA = [];
	}



	function match($_not404=True){
		//skip excess match type
		$is404 = !($this->urlA[0]===False);
		if ($_not404 xor $is404)
			return;


		$cUrlA = $this->urlA;
		if (!$is404)
			array_shift($cUrlA);

		$lost = False;
		$varsA = [];
		foreach ($cUrlA as $cUrl) {
			$found = False;
			if (is_callable($cUrl)){ //function binding
				$fRes = $cUrl();
				if (($fRes !== False) && ($fRes !== Null) && ($fRes !== 0)){
					$found = True;
					if (is_array($fRes))
						$varsA = array_merge($varsA, $fRes);
				}
			} else if (is_string($cUrl)){ //regex binding
				$cRegex = str_replace('/', '\/', $cUrl);
				$cRes = [];
				
				if (preg_match("/^$cRegex$/", KiUrl::uri(), $cRes)){
					$found = True;
					$varsA = array_merge($varsA, $cRes);
				}
			} else if ($cUrl){
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