<?
/*
Context object
*/
class KiRouteCtx {
	static $contextA=[];

	var $codeA=[];
	var $headersA=[], $return=0;
	var $varsA=[];



// =todo 137 (context) +0: allow to use unnamed contexts
	static function add($_ctx, $_src){
		if (!is_string($_ctx))
			$_ctx = (string)$_ctx;

		if (!is_array($_src))
			$_src = [$_src];


		if (!array_key_exists($_ctx, self::$contextA))
			self::$contextA[$_ctx] = new self();
		$cCtx = self::$contextA[$_ctx];


		foreach ($_src as $cSrc) {
			if (array_search($cSrc, $cCtx->codeA) !== False)
				continue;

			$cCtx->codeA[] = $cSrc;
		}
	}



/*
Fetch ordered and filtered context names.

Array may be supplied to reorder output contexts.
If specified, only listed in array will be run at all.
*/
	static function getOrder($_orderA){
		$ctxA = array_keys(self::$contextA);

		if (!count($_orderA))
			return $ctxA;


		$collectA = [];
		foreach ($_orderA as $cCtx){
			if (!is_string($cCtx)) //type check
				continue;

			$fA = array_filter($ctxA, function ($v) use ($cCtx) {return fnmatch($cCtx, $v);});
			$collectA = array_merge($collectA, $fA);
		}

		return array_values( array_unique($collectA) );
	}



/*
Run prepared code and variables into KiHandler
*/
	function run($_name=False){
		$cContentA = [];

		//run all code
		foreach ($this->codeA as $cSrc) {
			$cCont = $this->runContent($cSrc, $this->varsA);
			if (is_string($cCont))
				$cContentA[] = $cCont;
		}

		KiHandler::setContent($_name, implode('', $cContentA));

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
?>