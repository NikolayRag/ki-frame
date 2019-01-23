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
/*
Finalize: actually run matching route collection.
This is called once for entire http request.

_newOrder
	Array of glob patterns may be used to specify contexts, which will go in order they matched.

	Matched contexts are switched off further match.
	That is, if there's ['c1', 'c2', 'd2'] contexts defined, ordering with ['*2', 'c?'] will result in ['c2', 'd2', 'c1'] since 'c2' is matched first.

	Using '*' anywhere on order, will place all remaining contexts without sorting in order they were defined.

	If non-empty array is provided, any context which didnt match will be ignored.

	If particular context don't match, it is ignored.

	All matching contexts will be used if no order specified.
*/
	static function render($_newOrder=False){
		$matches = self::matchUrl(Ki_RouteBind::$bindA);
		if (!count($matches))
			$matches = self::matchUrl(Ki_RouteBind::$bind404A);

		if (!count($matches))
			KiHandler::setReturn(404);


		if (!is_array($_newOrder))
			$_newOrder = [];

		$orderCtxA = self::contextGetOrder($_newOrder);
		$runCtxA = self::orderRun($matches, $orderCtxA);

		//Trigger inlined code
		$inlineCtxA = [];
		if (!$_newOrder or in_array('*', $_newOrder, True))
			$inlineCtxA = self::contextInline($matches);


		foreach (array_merge($runCtxA, $inlineCtxA) as $cCtx)
			$cCtx->run();
	}



/*
	PRIVATE
*/



/*
Fetch ordered and filtered context.

Array may be supplied to reorder output contexts.
If specified, only listed in array will be run at all.
*/
	static private function contextGetOrder($_orderA){
		$ctxA = array_keys(Ki_RouteCtx::$contextA);

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
Collect anonymous context objects.
*/
	static private function contextInline($_bindA){
		$outContextA = [];


		return $outContextA;
	}



/*
Detect all matching URL bindings.
*/
	static private function matchUrl($_bindA){
		//collect detected url's
		$bondA = [];
		foreach ($_bindA as $cBind)
			if ($cBind->match())
				$bondA[] = $cBind;

		return $bondA;
	}



/*
Collect all URL contexts in specified order
*/
	static private function orderRun($_bindA, $_order){
		$fContextA = [];
		//filter contexts out

		foreach ($_bindA as $cBind){ //all actual bindings
			foreach ($cBind->ctxA as $cCtxName) {
				if (array_search($cCtxName, $_order) === False)
					continue;


				array_push($fContextA, $cCtxName);

				//update stored context object
				$cCtx = Ki_RouteCtx::$contextA[$cCtxName];
				$cCtx->headersA = array_merge($cBind->headersA, $cCtx->headersA);

				if ($cBind->return)
					$cCtx->return = $cBind->return;

				$cCtx->varsA = array_merge($cBind->varsA, $cCtx->varsA);
			}
		}


		$outContextA = [];

 		//sort context with previously specified order
		foreach ($_order as $cCtxName)
			if (in_array($cCtxName, $fContextA))
				array_push($outContextA, Ki_RouteCtx::$contextA[$cCtxName]);


		return $outContextA;
	}
}
?>