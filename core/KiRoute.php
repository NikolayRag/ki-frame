<?
/*
Routing matrix. It formed out of three rule definitions:
Use context(ctx, src) to assign context name to code-generating function.
Use pin(ctx, URL, ..) to bind named contexts to URLs.
Finally, order([ctx1, .., ctxX]) arranges order of possible context inclusion.
If order() is omited, context order is the same the contexts was defined first.

The idea is that 'some request' may call 'some code' in 'some order', all of which are independently defined.
*/


class KiRoute {

/*
Assign context name or number to code-generating routine.

$_ctx
	String or number for context to be named.

$_src
	One of three: function, filename, string.

	Function provided to context() return response data.
	Anything other than string returned treated as error and ignored in output.
*/
	static function context($_ctx, $_src){
	}



/*
Bind context to URL.

$_ctx
	Context assigned to specified URL.

$_url
	URL string to match.
	If url is True, it is assumed to be any URL at all.
	If url is False, route matches 404.

*/
	static function bind($_ctx, $_url=False){

	}



/*
Define context order for corresponding matches, when several contets match some URL.
Every context not ordered explicitely will have it's place after all explicit ones, in order it was declared first time by rIn.

$_ctxA
	Array of contexts.
	Default context may be refered as ''.
*/
	static function order($_ctxA){

	}





/*
Actually run matching route collection.
This is called at response generation stage for entire http request.
*/
	static function solve($_url){
	}

}
?>