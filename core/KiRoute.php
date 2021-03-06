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

	Matched contexts are placed at first match order.
	That is, if there's ['c1', 'c2', 'd2'] contexts defined, ordering with ['*2', 'c?'] will result in ['c2', 'd2', 'c1'] since 'c2' is matched already.

	Using '*' anywhere on order, will place all remaining contexts without sorting in order they were defined.

	Any context which didn't match will be skipped.

	All matching contexts will be used if no order specified.


	All variables from matched binding are passed to all bond contexts.

_doInline
	Add all inlined contexts, based on:
		True, forced "on",
		False, forced 'off',
		None, switched by blank order or '*' specified anywhere.

	Inlined contexts are used once, next to named ones.
*/
	static function render($_newOrder=[], $_doInline=Null, $_noneCode=404){
		if (!is_array($_newOrder))
			$_newOrder = [$_newOrder];

//match normal, or 404, or set default code
		$matches = KiRouteBind::matchUrl(True);
		if (!$matches)
			$matches = KiRouteBind::matchUrl(False);

		if (!$matches) {
			KiHandler::setReturn($_noneCode);
			return;
		}
		
		if ($_doInline===Null)
			$_doInline = !$_newOrder or array_search('*', $_newOrder);
		$outRun = self::collect($matches, $_newOrder, $_doInline);


//Set headers and code, trigger contexts code
		if ($outRun->return)
			KiHandler::setReturn($outRun->return);

		foreach ($outRun->headersA as $hName=>$hVal)
			KiHandler::setHeader($hName, $hVal);

		foreach ($outRun->ctxA as $cCtx)
			$cCtx->run();
	}



/*
	PRIVATE
*/



/*
Collect all bond contexts in specified order.

$_doInline
	Toggle to join all inlined context objects after all named.
	Inlined contexts are included only once alongside with named.
*/
	static private function collect($_bindA, $_newOrder, $_doInline){
		$fContextA = [];
		//filter contexts out

		$ctxInlineA = [];
		$ctxNamedA = KiRouteCtx::getNamed($_newOrder);

		$outHeadersA = [];
		$outReturn = 0;
		foreach ($_bindA as $cBind){ //all actual bindings
			foreach ($cBind->ctxA as $cCtx) {
				$cCtxO = null;

				switch (True) {
					case ($cCtx instanceof KiRouteCtx):
						$ctxInlineA[] = $cCtx;
						$cCtxO = $cCtx;

						break;


					case (in_array($cCtx, array_keys($ctxNamedA))):
						$fContextA[$cCtx] = True; //store names only names 
						$cCtxO = $ctxNamedA[$cCtx];
						
						break;


					default:
						continue;
				}


				$outHeadersA = array_merge($cBind->headersA, $outHeadersA);

				if ($cBind->return)
					$outReturn = $cBind->return;

				foreach ($cBind->varsA as $n=>$v)
					$cCtxO->varsA[$n] = $v;
			}
		}


		$filteredCtxA = array_intersect_key($ctxNamedA, $fContextA);
		if ($_doInline)
			foreach ($ctxInlineA as $cCtx){
				if (!in_array($cCtx, $filteredCtxA))
					$filteredCtxA[] = $cCtx;
			}


		return (object)[
			'ctxA' => $filteredCtxA,
			'headersA' => $outHeadersA,
			'return' => $outReturn
		];
	}
}
?>