<?
/*
Context object
*/
class KiRouteCtx {
	static $contextA=[];

	var $codeA=[];
	var $varsA=[];




/*
Add code to named context. 
*/
	static function add($_ctx, $_src){
		if (!is_string($_ctx))
			$_ctx = (string)$_ctx;


		if (!array_key_exists($_ctx, self::$contextA))
			self::$contextA[$_ctx] = new self();

		$cCtx = self::$contextA[$_ctx];
		$cCtx->bindCode($_src);

		return $cCtx;
	}



/*
Fetch named contexts array in glob-specified order.

Glob patterns array may be supplied to filter and reorder output contexts.
Explicitely specified context is returned only once, at first match.
*/
	static function get($_orderA){
		if (!is_array($_orderA))
			$_orderA = [];

		if (!count($_orderA))
			return array_merge([], self::$contextA);


		$ctxA = array_keys(self::$contextA);

		$unglobA = [];
		foreach ($_orderA as $cCtx){
			if (!is_string($cCtx)) //type check
				continue;

			$fA = array_filter($ctxA, function ($v) use ($cCtx) {
				return fnmatch($cCtx, $v);
			});
			$unglobA = array_merge($unglobA, $fA);
		}


		$outCtxA = [];
		foreach (array_values( array_unique($unglobA) ) as $cName)
			$outCtxA[$cName] = self::$contextA[$cName];


		return $outCtxA;
	}


	function __construct($_src=False){
		$this->codeA = [];

		if ($_src)
			$this->bindCode($_src);
	}


/*
Bind provided code array.
*/
	function bindCode($_src){
		if (!is_array($_src))
			$_src = [$_src];

		foreach ($_src as $cSrc)
			if (!in_array($cSrc, $this->codeA))
				$this->codeA[] = $cSrc;
	}



/*
Run prepared code and variables into KiHandler
*/
	function run($_name=False){
		$cContentA = [];

		//run all code
		foreach ($this->codeA as $cSrc) {
			$cCont = $this->runContent($cSrc);
			if (is_string($cCont))
				$cContentA[] = $cCont;
		}

		KiHandler::setContent($_name, implode('', $cContentA));
	}



/*
Solve registered code generators for specified context.
*/
	private function runContent($_src){
		if (is_callable($_src)){
			ob_start(); //nest buffer

			$res = call_user_func($_src, (object)$this->varsA);

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