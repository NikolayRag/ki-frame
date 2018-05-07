<?

/*
URL parser.
Works when called at 404 redirect i.e. at nearly every call.
Detects GET "?" parameters, path breadrolls,
and uses path[0] as identifier for requested mode.

__construct($_modeA, $_modeDefault, $_modeWrong)
	$_modeA
		'name'=>value pair array of modes for detection

	$_modeDefault
		default value for mode set if no path supplied

	$_modeWrong
		mode value if no suitable mode found in $_modeA provided

*/

class KiURL {
	var $type='', $path=[], $args;

	function __construct($_filterA){
		$this->https=
			getA($_SERVER, 'HTTPS') ||
			getA(json_decode(getA($_SERVER, 'HTTP_CF_VISITOR')),'scheme')=='https';
	

		$this->server= $_SERVER['SERVER_NAME'];


		$this->args= new LooseObject();

		foreach ($_POST as $pName=>$pVal)
			$this->args->$pName= $pVal;

		$uriA= explode("?", $_SERVER["REQUEST_URI"]);


		//Fill args
		if (isset($uriA[1]))
		  foreach(explode("&",$uriA[1]) as $x){
			$xSpl= explode("=",$x);
			$get= isset($xSpl[1])? urldecode($xSpl[1]) :False;

			$_REQUEST[$xSpl[0]]=
			$_GET[$xSpl[0]]=
			$this->args->$xSpl[0]=
				$get;
		  }


		$this->path= array_slice(
			explode("/", preg_replace('[/+]', '/', $uriA[0])), 1
		);



		$path0= strtoupper($this->path[0]);

		if ($path0!==''){
			$this->type= getA($_filterA, $path0);
		}
		
	}
}


?>
