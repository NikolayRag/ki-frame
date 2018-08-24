<?
/*
Routing matrix.
The idea is that 'some request' results in set of 'some contexts', independently defined.

Virtually, there're following levels of complexity in managing content generation:
- Minimal. Only default context defined for site root by context(code).
- Basic. Define different contexts by context(context, code), bind them to patticular URLs by bind(url, context), and define output context order by order([context array]). Also define '' (not found) context.
- Full. Same as Basic, but supply regex or URL checkers for bind() to handle absolutely custom context match. Define custom headers and return codes.
- Debug. Use explicitely generated output code. Overwrite any generated code and headers by custom error handlers, if any exists.
*/



/*
Context support class
*/
class Ki_RouteContext {
	var $ctx=[], $code=0, $headersA;
	var $vars = [];


	function __construct($_ctx=[]){
		$this->ctx = $_ctx;
		$this->code = 0;
		$this->headersA = [];
	}
}



class KiRoute {
	private static $contextA=[], $contextOrder=[], $bindSrcA=[], $bindA=[];



/*
Assign context to some code-generating routines.
Several routines may be assigned with same context, that will come out they result will be placed right one after another.
Order for multiple same-context code is the same as they were declared.


$_ctx
	String or number for context to be named.


$_src
	One of three: function, filename, string.

	Function is called to generate content.
	If existing .php filename is given instead of function, it's imported.
	Otherwise, provided string is embedded as is.

	Function provided to context() return response data.
	Anything other than string returned treated as error and ignored in output.
*/
	static function context($_ctx, $_src){
		if (!array_key_exists($_ctx, self::$contextA))
			self::$contextA[$_ctx] = [];

		if (array_search($_src, self::$contextA[$_ctx]) !== False)
			return;

		self::$contextA[$_ctx][] = $_src;
	}



/*
Add context and headers to URL and set return code.
Different contexts may be bond to one URL, as well as one context may be bond to number of URLs.
All contexts for all matching URLs will be used without concurrency.

If nothing is bound at all, the only implicit assignment is '/' URL to '' context (root to default).
If nothing is bound to '' (404 case), it will implicitely be assigned to blank page with 404 return code. If '' url is bound, 404 return code must be then set explicitely.


$_url
	Match function or URL match regex.
	Array accepted, where ALL elements must match.

	If function supplied returns False, None or 0, then there's no match. Any other return triggers set match.
	If array is returned, it's used as 'variables' argument for context functions.
	
	URL passed for regex match, always starting with root '/'.
	Regex may have '/' unescaped slashes.
	Named capture (?P<name>value) is allowed to scan variables.
	Tricky regex matches like "^(?!.foo$)" (all but '/foo') are fully allowed.

	If first (or only) value specified is False, match is used in case of no 'normal' matches found.
	Notice, that if there any wide mask bound match, like '.*', it could become impossible to catch 'not found' case at all. 'Not found' binding for this case can be matched by using patterns like "^(?!.foo$)".



$_ctx
	Context added to specified URL.


$_code
	Default HTTP return code.
	Return code have priority over any other defined one.


$_headers
	Default custom return headers array.
*/
	static function bind($_url, $_ctx, $_code=0, $_headersA=[]){
		$cKey = array_search($_url, self::$bindSrcA);
		if ($cKey === False){
			$cKey = count(self::$bindSrcA);
			self::$bindSrcA[] = $_url;
		}


		if (!array_key_exists($cKey, self::$bindA))
			self::$bindA[$cKey] = new Ki_RouteContext();

		self::$bindA[$cKey]->ctx[] = $_ctx;


		if ($_code)
			self::$bindA[$cKey]->code = $_code;
		
		foreach ($_headersA as $hName=>$hVal)
			self::$bindA[$cKey]->headersA[$hName] = $hVal;
	}
// -todo 31 (check) +0: check urls for duplicates



/*
Define context order for corresponding matches, when several contents match some URL.

If particular context don't exist, it is ignored.
All matching contexts will be used if no order specified.


$_ctxA
	Array of contexts.
	Default context may be refered as ''.

	If omited, only return current order.
*/
	static function order($_ctxA=False){
		if ($_ctxA)
			self::$contextOrder = $_ctxA;

		return self::$contextOrder;
	}





/*
Finalize: actually run matching route collection.
This is called once for entire http request.
*/
	static function render(){
		$orderCtx = self::orderSnapshot();
		$matches = self::matchUrl();
		if (!count($matches))
			$matches = self::matchUrl(False);

		
		//implicit bindings
		if (!count($matches)){
			//bound '/' to all binding
			if (KiUrl::uri()=='/')
				$matches = [new Ki_RouteContext($orderCtx)];
			
			//'not found'
			else
				KiHandler::setReturn(404);
		}


		$runA = self::orderRun($matches, $orderCtx);

		foreach ($runA as $cCtx=>$cSupport){
			$cContentA = [];

			foreach (self::$contextA[$cCtx] as $cSrc) {
				$cCont = self::runContent($cSrc);
				if (is_string($cCont))
					$cContentA[] = $cCont;
			}


			KiHandler::setContent($cCtx, implode('', $cContentA));

			foreach ($cSupport->headersA as $hName=>$hVal)
				KiHandler::setHeader($hName, $hVal);

			if ($cSupport->code)
				KiHandler::setReturn($cSupport->code);
		}
	}



/*
Fetch ordered and filtered context.
*/
	static private function orderSnapshot(){
		$ctxA = array_keys(self::$contextA);

		if (count(self::$contextOrder))
			$ctxA = array_values(array_intersect(self::$contextOrder, $ctxA));

		return array_unique($ctxA);
	}



/*
Detect all matching URL bindings.
*/
	static private function matchUrl($_not404=True){
		$bondA = [];

		//collect detected url's
		foreach (self::$bindA as $cKey=>$cBind){
			//get hashed array
			$cUrlA = self::$bindSrcA[$cKey];
			if (!is_array($cUrlA))
				$cUrlA = [$cUrlA];

			//skip excess match type
			$is404 = !($cUrlA[0]===False);
			if ($_not404 xor $is404)
				continue;


			$lost = False;
			$varsA = [];
			foreach ($cUrlA as $cUrl) {
				if ($cUrl===False) //skip no-match marker
					continue;

				$found = False;
				if (is_callable($cUrl)){ //function binding
					$fRes = $cUrl();
					if (($fRes !== False) && ($fRes !== Null) && ($fRes !== 0)){
						$found = True;
						if (is_array($fRes))
							$varsA = array_merge($varsA, $fRes);
					}
	// =todo 34 (ux, routing) +0: match url variables
				} else {
					$cRegex = str_replace('/', '\/', $cUrl);
					$cRes = [];
					
					if (preg_match("/^$cRegex$/", KiUrl::uri(), $cRes)){
						$found = True;
						$varsA = array_merge($varsA, $cRes);
					}
				}

				$lost = $lost || !$found;
			}


			if (!$lost){
				foreach ($varsA as $key=>$val)
				    if (is_int($key)) 
				        unset($varsA[$key]);

				$cBind->vars = $varsA;
				$bondA[] = $cBind;
			}
		}

		return $bondA;
	}



/*
Collect all URL contexts in specified order
*/
	static private function orderRun($_urlA, $_order){
		$fContextA = [];
		//filter contexts out
		foreach ($_urlA as $cBind){ //all actual bindings
			foreach ($cBind->ctx as $cCtx) {
				if (array_search($cCtx, $_order) === False)
					continue;

				if (!array_key_exists($cCtx, $fContextA))
					$fContextA[$cCtx] = new Ki_RouteContext();

				foreach ($cBind->headersA as $cHead=>$cVal)
					$fContextA[$cCtx]->headersA[$cHead] = $cVal;

				if ($cBind->code)
					$fContextA[$cCtx]->code = $cBind->code;
			}
		}



		$outContextA = [];

 		//sort context with previously specified order
		foreach ($_order as $cCtx)
			if (array_key_exists($cCtx, $fContextA))
				$outContextA[$cCtx] = $fContextA[$cCtx];


		return $outContextA;
	}



/*
Solve registered code generators for specified context.
*/
	static private function runContent($_src){
		if (is_callable($_src))
			return call_user_func($_src);


		if (is_file($_src)){
			ob_start(); //nest buffer
			include($_src);
			return ob_get_clean();
		}


		return $_src;
	}
}
?>