<?
include(__dir__ .'/KiRouteSupport.php');



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
	private static $contextA=[], $contextOrder=[], $bindSrcA=[], $bindA=[];

	private static $cContext;


/*
Assign context to some code-generating routines.
Several routines may be assigned with same context, that will come out they result will be placed right one after another.
Order for multiple same-context code is the same as they were declared.


$_ctx
	String or number for context to be named.


$_src
	Array or one of three: function, filename, string.

	Function is called to generate content.
	If existing .php filename is given instead of function, it's imported.
	Otherwise, provided string is embedded as is.

	Function provided to context() return response data.
	Anything other than string returned treated as error and ignored in output.
*/
	static function context($_ctx, $_src){
		if (!is_array($_src))
			$_src = [$_src];

		if (!array_key_exists($_ctx, self::$contextA))
			self::$contextA[$_ctx] = new Ki_RouteCtx();
		$cCtx = self::$contextA[$_ctx];


		foreach ($_src as $cSrc) {
			if (array_search($cSrc, $cCtx->codeA) !== False)
				continue;

			$cCtx->name = $_ctx;
			$cCtx->codeA[] = $cSrc;
		}
	}



/*
Add context and headers to URL and set return code.
Different contexts may be bond to one URL, as well as one context may be bond to number of URLs.
All contexts for all matching URLs will be used without concurrency.

If nothing is bound at all, the only implicit assignment is '/' URL to '' context (root to default).
If nothing is bound to '' (404 case), it will implicitely be assigned to blank page with 404 return code. If '' url is bound, 404 return code must be then set explicitely.


$_url
	Match function, URL match regex, or static value.
	Array accepted, where ALL elements must match.

	If function supplied returns non-strict False, then there's no match. Any other return value triggers match.
	If array is returned, it's passed as 'variables' argument to bond context functions, if any.
	
	String regexp passed for match URI, starting either with unescaped '/' or '?'. Unescaped '/' are also allowed anywhere in regex.
	If started with '/', URL path is matched. Path string to match is everything after server name, starting with '/', and without arguments.
	If started with '?', arguments are matched. Any successfull match counts. WRONG useage: matching several arguments at once will fail constantly, like '?a=1&b=1'. Use several matches instead: [.., '?a=1', '?b=1']. 
	Named capture (?P<name>value) is allowed to scan variables. Captured value is passed then within named array into bond context functions, if any. Matching several different named variables within one binding will pass all of them as arguments. When regex wide mask matches several URL arguments, only first match defines variable=>value pair.
	Tricky regex matches like "/(?!foo$).*" (all but '/foo') are fully allowed.

	Variables matched are accessed at runtime by ::contextData()


	If first (or only) value specified is 404, match is used in case of no 'normal' matches found.
	Notice, that if there any wide mask bound match, like '.*' or True, it could become impossible to catch 'not found' case at all. 'Not found' binding for this case can be matched by using patterns like "^(?!.foo$)".


$_ctx
	Context added to specified URL.
	May be string or array of contexts.


$_code
	Default HTTP return code.
	Return code have priority over any other defined one.


$_headers
	Default custom return headers array.
*/
	static function bind($_url, $_ctx, $_code=0, $_headersA=[]){
		if (!is_array($_url))
			$_url = [$_url];


		$cKey = array_search($_url, self::$bindSrcA, True);
		if ($cKey === False){
			$cKey = count(self::$bindSrcA);
			self::$bindSrcA[] = $_url;
		}


		//detect 404 case
		$is404 = ($_url[0]===404);
		if ($is404)
			array_shift($_url);

		if (!array_key_exists($cKey, self::$bindA))
			self::$bindA[$cKey] = new Ki_RouteBind($_url, [], $is404);
		$cBind = self::$bindA[$cKey];



		if (!is_array($_ctx))
			$_ctx = [$_ctx];
		$cBind->ctxA = array_merge($cBind->ctxA, $_ctx);


		if ($_code)
			$cBind->return = $_code;
		
		foreach ($_headersA as $hName=>$hVal)
			$cBind->headersA[$hName] = $hVal;
	}



/*
Define context order for corresponding matches, when several contents match some URL.

Regex may be used to specify set of contexts, which will go as they were defined.
Regex used is implicitely expanded to full-string form (^...&).
When wide mask used, all matching context are switched off further match.
That is, if there's ['c1', 'c2', 'd2'] contexts defined, ordering with ['.2', 'c\d'] will result in ['c2', 'd2', 'c1'] since 'c2' is grabbed with '.2' match.  Using '.*' anywhere on order, will place all remaining contexts without sorting.

If particular context don't match, it is ignored.
All matching contexts will be used if no order specified.


$_ctxA
	Array of contexts.
	Default context may be refered as ''.
*/
	static function order($_ctxA=False){
		if (is_array($_ctxA))
			self::$contextOrder = $_ctxA;

		return self::$contextOrder;
	}





/*
Finalize: actually run matching route collection.
This is called once for entire http request.

_newOrder
	Array may be supplied to reorder output contexts.
	Remember again only those listed in array will be run at all.
*/
	static function render($_newOrder=False){
		$orderCtx = self::orderSnapshot($_newOrder);
		$matches = self::matchUrl();
		if (!count($matches))
			$matches = self::matchUrl(True);

		
		//implicit bindings
		if (!count($matches)){
			//bound '/' to all binding
			if (KiUrl::uri()=='/')
				$matches = [new Ki_RouteBind(['/'],$orderCtx)];
			
			//'not found'
			else
				KiHandler::setReturn(404);
		}



		$runA = self::orderRun($matches, $orderCtx);

		foreach ($runA as $cCtxName){
			KiRoute::$cContext = self::$contextA[$cCtxName];
			KiRoute::$cContext->runCtx();
			KiRoute::$cContext = Null;
		}
	}



/*
Provide current context variables, available only for bond code runtime.
Variables are NOT safe, they could be modified while runtime.
*/
	static function contextData(){
		$cVars = [];
		if (KiRoute::$cContext)
			$cVars = KiRoute::$cContext->varsA;

		return new LooseObject($cVars);
	}



/*
	PRIVATE
*/



/*
Fetch ordered and filtered context.
*/
	static private function orderSnapshot($_overOrder=False){
		if (!is_array($_overOrder))
			$_overOrder = self::$contextOrder;

		$ctxA = array_keys(self::$contextA);

		$collectA = [];
		if (count($_overOrder))
			foreach ($_overOrder as $cCtx){
				if (!is_string($cCtx)) //type check
					continue;

				$fA = array_filter($ctxA, function ($v) use ($cCtx) {return preg_match("/^$cCtx$/", $v);});
				$collectA = array_merge($collectA, $fA);
			}
		else
			$collectA = $ctxA;
		

		return array_values( array_unique($collectA) );
	}



/*
Detect all matching URL bindings.
*/
	static private function matchUrl($_do404=False){
		$bondA = [];

		//collect detected url's
		foreach (self::$bindA as $cBind)
			if ($cBind->match($_do404))
				$bondA[] = $cBind;

		return $bondA;
	}



/*
Collect all URL contexts in specified order
*/
	static private function orderRun($_bindA, $_order){
		$fContextA = [];
		//filter contexts out
//  todo 56 (ux, unsure) -1: maybe call same contexts several matches separately
		foreach ($_bindA as $cBind){ //all actual bindings
			foreach ($cBind->ctxA as $cCtx) {
// -todo 69 (ux, routing) +0: allow False context to be specified where no output needed
				if (array_search($cCtx, $_order) === False)
					continue;


				array_push($fContextA, $cCtx);

				//update stored context object
				$cCtx = self::$contextA[$cCtx];
				$cCtx->headersA = array_merge($cBind->headersA, $cCtx->headersA);

				if ($cBind->return)
					$cCtx->return = $cBind->return;

				$cCtx->varsA = array_merge($cBind->varsA, $cCtx->varsA);
			}
		}


		$outContextA = [];

 		//sort context with previously specified order
		foreach ($_order as $cCtx)
			if (in_array($cCtx, $fContextA))
				array_push($outContextA, $cCtx);


		return $outContextA;
	}
}
?>