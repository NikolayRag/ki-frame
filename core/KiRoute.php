<?
/*
Routing matrix.
The idea is that 'some request' results in set of 'some contexts', independently defined.

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

		self::$contextA[$_ctx][] = $_src;
	}



/*
Assign default return code and headers to URL.
If concurrent URL matches will be found, most prioritized values take place.


$_url
	One of:

	- Match function name.

	- Regex to match URL against. URL always starts with root '/'.
	Named capture (?P<name>value) is allowed to scan variables.
	Tricky regex matches like "^(?!.foo$)" (all but '/foo') are fully allowed.

	- Empty string is alias for 'nothing match' special case.


$_code
	Default HTTP return code.
	May be overrided inside $_src


$_headers
	Default custom return headers array.


$_priority
	When values are concurrent, biggest priority points the ones.
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
If nothing was bound at all, the only implicit assignment is '/' URL to '' context (root to default).


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
Every context not ordered explicitely will have it's place after all explicit ones, in order it was declared first time by context().
If particular context don't exist, it is ignored.


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
		self::init();


		$actualOrder = self::buildOrder();

		foreach ($actualOrder as $cCtx){
	echo "ctx: '$cCtx' >>>\n";

			$cContentA = '';

			foreach (getA(self::$contextA,$cCtx,[]) as $cSrc)
				$cContentA = self::runContent($cSrc);

			KiHandler::contentSet($cCtx, implode('', $cContentA));
	echo "<<<\n";
		}

		KiHandler::contentOrder($actualOrder);
	}



/*
Initialize environment: database, user account and rights, etc.
*/
	static private function init(){
		$dbCfg= KC::DBCFG();
		$DB = new PDO("mysql:host={$dbCfg->HOST};dbname={$dbCfg->NAME};charset=utf8", $dbCfg->USER, $dbCfg->PASS, array(PDO::ATTR_PERSISTENT=>true));
		$DB->exec("set names utf8");

		//additional error callback (to DB,table)
		KiHandler::errCB(ErrCB\errCBDB($DB, 'site_log_errors'));


		$USER= new KiAuth($DB, KC::SOCIAL());
	}



/*
Build actual context order based on registered context list and explicit context order.
*/
	static private function buildOrder(){
		$outContextA = [''];

		return $outContextA;
	}



/*
Build all registered code generators.
*/
	static private function runContent($_src){
		echo "c: ";
		print_r($_src);
		echo "\n";
		return '';
	}
}
?>