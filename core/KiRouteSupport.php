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
		if (is_callable($_src))
			return call_user_func($_src, (object)$_vars);


		if (is_file($_src)){
			ob_start(); //nest buffer
			include($_src);
			return ob_get_clean();
		}


		return $_src;
	}
}

?>