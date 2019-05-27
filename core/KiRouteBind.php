<?
/*
Context bind class
*/
class KiRouteBind {
	const UrlTrue=1, UrlPath=2, UrlArgs=3, UrlFN=4;


	static $bindA=[], $bind404A=[];


	var $urlA=[], $ctxA=[], $return=0, $headersA;
	var $varsA=[];



/*
Hold provided binding to normal or 404 array
*/
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

		if (!count($bondA))
			return;
		
		return $bondA;
	}



	function __construct($_urlA, $_ctxA=False, $_code=0, $_headersA=[]){
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



/*
Match all bond URL's arrays.
URL's can be any of:
 - match function, returning boolean or additional arguments array,
 - '/'-started path regex string, allowing for named sub-regex match,
 - '?'-started both GET and POST arguments regex string, allowing for named sub-regex match,
 - or implicit boolean value (cast to).

Named arguments array is overrided if names repeat while bindings match. 


Return bindings array.
*/
	function match(){
		$varsA = [];

		foreach ($this->urlA as $cUrl) {
			switch (self::URLType($cUrl)){
				case (self::UrlFN): //return variables array
					$fRes = $cUrl();
					if (!$fRes)
						return;

					if (is_array($fRes))
						$varsA = array_merge($varsA, $fRes);

					break;


				case self::UrlPath: //return variables are regex matches
// -todo 138 (check, bind) +1: check for exploit
					$cRegex = str_replace('/', '\/', $cUrl);

					$cRes = [];
					if (!preg_match("/^$cRegex$/", KiUrl::path(True), $cRes))
						return;
					$varsA = array_merge($varsA, self::cleanIdx($cRes));

					break;


				case self::UrlArgs:
					$found = False;

					foreach (KiUrl::args()->all() as $cName => $cVal) {
						$cRes = [];
						if (!preg_match("/^\\$cUrl$/", "?$cName=$cVal", $cRes))
							continue;
						$varsA = array_merge($varsA, self::cleanIdx($cRes));

						$found = True;
					}

					if (!$found)
						return;
					break;


				case self::UrlTrue:
					break;


				default:
					return;
			}
		}

		$this->varsA = $varsA;

		return True;
	}



	private static function URLType($_url){
		if (is_callable($_url))
			return self::UrlFN;

		if (is_string($_url) and $_url[0]=='/')
			return self::UrlPath;

		if (is_string($_url) and $_url[0]=='?')
			return self::UrlArgs;

		if (!!$_url)
			return self::UrlTrue; //should be specified as constant to avoid switch() mismatch
	}



	private static function cleanIdx($_varsA){
		foreach ($_varsA as $key=>$val) //leave only named associations
		    if (is_int($key)) 
		        unset($_varsA[$key]);

		return $_varsA;
	}
}

?>