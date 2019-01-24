<?
include(__dir__ .'/KiRouteCtx.php');
include(__dir__ .'/KiRouteBind.php');



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
		$matches = KiRouteBind::matchUrl(True);
		if (!count($matches))
			$matches = KiRouteBind::matchUrl(False);

		if (!count($matches))
			KiHandler::setReturn(404);


		if (!is_array($_newOrder))
			$_newOrder = [];

		$orderCtxA = KiRouteCtx::getOrder($_newOrder);
		$outRun = self::orderRun($matches, $orderCtxA);

		//Trigger inlined code
		$inlineCtxA = [];
		if (!$_newOrder or in_array('*', $_newOrder, True))
			$inlineCtxA = self::contextInline($matches);


		if ($outRun->return)
			KiHandler::setReturn($outRun->return);

		foreach ($outRun->headersA as $hName=>$hVal)
			KiHandler::setHeader($hName, $hVal);

		foreach (array_merge($outRun->ctxA, $inlineCtxA) as $cName=>$cCtx)
			$cCtx->run($outRun->varsA, $cName);

	}



/*
	PRIVATE
*/



/*
Collect anonymous context objects.
*/
	static private function contextInline($_bindA){
		$outContextA = [];


		return $outContextA;
	}



/*
Collect all URL contexts in specified order
*/
	static private function orderRun($_bindA, $_order){
		$fContextA = [];
		//filter contexts out

		$outVarsA = [];
		$outHeadersA = [];
		$outReturn = 0;
		foreach ($_bindA as $cBind){ //all actual bindings
			foreach ($cBind->ctxA as $cCtxName) {
				if (!in_array($cCtxName, $_order))
					continue;


				array_push($fContextA, $cCtxName);


				$outHeadersA = array_merge($cBind->headersA, $outHeadersA);

				if ($cBind->return)
					$outReturn = $cBind->return;

				$outVarsA = array_merge($cBind->varsA, $outVarsA);
			}
		}


		$outContextA = [];

 		//sort context with previously specified order
		foreach ($_order as $cCtxName)
			if (in_array($cCtxName, $fContextA))
				array_push($outContextA, KiRouteCtx::$contextA[$cCtxName]);


		return (object)[
			'ctxA' => $outContextA,
			'varsA' => $outVarsA,
			'headersA' => $outHeadersA,
			'return' => $outReturn
		];
	}
}
?>