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



class KiRoute {
	private static $contextA=[], $bindA=[], $contextOrder=[];



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
	One of:

	- Match function name.

	- Regex to match URL against. URL always starts with root '/'.
	Named capture (?P<name>value) is allowed to scan variables.
	Tricky regex matches like "^(?!.foo$)" (all but '/foo') are fully allowed.

	- Empty string is alias for 'nothing match' special case.
	Notice, that if there any wide mask bound match, like '.*', it could be impossible to catch 'not found' case at all. 'Not found' binding for this case can be matched by using patterns like "^(?!.foo$)".


$_ctx
	Context added to specified URL.


$_code
	Default HTTP return code.
	Return code have priority over any other defined one.


$_headers
	Default custom return headers array.
*/
	static function bind($_url, $_ctx, $_code=0, $_headersA=[]){
		if (!array_key_exists($_url, self::$bindA))
			self::$bindA[$_url] = self::stxObj();

		self::$bindA[$_url]->ctx[] = $_ctx;


		if ($_code)
			self::$bindA[$_url]->code = $_code;
		
		foreach ($_headersA as $hName=>$hVal)
			self::$bindA[$_url]->headers[$hName] = $hVal;
	}
// -todo 31 (check) +0: check urls for duplicates



	private static function stxObj($_ctx=[]){
		return (object)['ctx'=>$_ctx, 'code'=>0, 'headers'=>[]];
	}

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
		
		//implicit bindings
		if (!count($matches)){
			//bound '/' to all binding
			if (KiUrl::uri()=='/')
				$matches = [self::stxObj($orderCtx)];
			
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

			foreach ($cSupport->hdrA as $hName=>$hVal)
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
	static private function matchUrl(){
		$bondA = [];
		$noneA = [];

		//collect detected url's
		foreach (self::$bindA as $cUrl=>$cBind){
			if ($cUrl==''){ //'not found' binding
				$noneA[] = $cBind;
			} else if (is_callable($cUrl) && $cUrl()){ //function binding
				$bondA[] = $cBind;
// -todo 34 (ux, routing) +0: match url variables
				$cRegex = str_replace('/', '\/', $cUrl);
			} else {
				if (preg_match("/^$cRegex$/", KiUrl::uri()))
					$bondA[] = $cBind;
			}
		}

		//no-match case
		if (!count($bondA))
			$bondA = $noneA;

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
					$fContextA[$cCtx] = (object)['hdrA'=>[], 'code'=>0];

				foreach ($cBind->headers as $cHead=>$cVal)
					$fContextA[$cCtx]->hdrA[$cHead] = $cVal;

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