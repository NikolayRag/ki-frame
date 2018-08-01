<?
/*
Routing matrix.
The idea is that 'some request' results in set of 'some contexts', independently defined.

*/



class KiRoute {
	private static $contextA=[], $bindA=[], $contextOrder=[];



/*
Assign context to some code-generating routines.
Several routines may be assigned with same context, that will come out they result will be placed right one at an other.
No order for multiple same-context code is defined.


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

		self::$contextA[$_ctx][] = $_src;
	}



/*
Assign default return code and headers to URL.
If concurrent URL matches will be found, most prioritized values take place.


$_url
	Regex to match URL against. URL always starts with root '/'.
	Named capture (?P<name>value) is allowed to scan variables.
	Tricky regex matches like "^(?!.foo$)" (all but '/foo') are fully allowed.

	Empty string is alias for 'nothing match' special case.


$_code
	Default HTTP return code.
	May be overrided inside $_src


$_headers
	Default custom return headers array.


$_priority
	When values are concurrent, biggest priority points one.
*/
	static function bind($_url, $_code=200, $_headersA=[], $_priority=1){
		checkUrl($_url);

		self::$bindA[$_url]->code = $_code;
		self::$bindA[$_url]->headersA = $_headersA;
		self::$bindA[$_url]->priority = $_priority;
	}



/*
Add context to URL.
All contexts for all matching URLs will be used without concurrency.
Different contexts may be bond to one URL, as well as one context may be bond to number of URLs.

$_url
	Same as for bind()


$_ctx
	Context assigned to specified URL.

*/
	static function bindCtx($_url, $_ctx){
		checkUrl($_url);

		self::$bindA[$_url]->ctx[] = $_ctx;
	}



	private static function checkUrl($_url){
		if (array_key_exists($_url, self::$bindA))
			return;

		self::$bindA[$_url] = (object)['ctx'=>[], 'code'=>200, 'headers'=>[], 'priority'=>-100];
	}



/*
Define context order for corresponding matches, when several contents match some URL.
Every context not ordered explicitely will have it's place after all explicit ones, in order it was declared first time by rIn.

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
Actually run matching route collection.
This is called at response generation stage for entire http request.
*/
	static function solve($_url){
	}

}
?>