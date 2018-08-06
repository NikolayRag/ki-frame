<?
/*
Class used for determining incoming connection.
*/

spl_autoload_register(
    function ($class) {
		$cClassA= explode('\\', $class);
		if ($cClassA[0]!='Sinergi' || $cClassA[1]!='BrowserDetector')
			return;

        $baseDir = __DIR__ . '/../_3rd/php-browser-detector/src';
        $path = $baseDir . '/' . str_replace('\\', '/', $cClassA[2]) . '.php';

        if (is_file($path))
            require $path;
	}
);



use Sinergi\BrowserDetector\Browser;
//  todo 24 (api, add) +0: other useragent detectors
//use Sinergi\BrowserDetector\Os;
//use Sinergi\BrowserDetector\Device;
//use Sinergi\BrowserDetector\Language;

//  todo 25 (api, add) +0: detect legacy


class KiAgent{
	private static $isInited;

	private static $vBrowser, $vDefault, $vBot;



	static function browser() {
		self::detect();
		return self::$vBrowser;
	}



	static function isKnown() {
		self::detect();
		return (self::$vDefault || self::$vBot);
	}



	static function isDefault() {
		self::detect();
		return self::$vDefault;
	}



	static function isBot() {
		self::detect();
		return self::$vBot;
	}



	static private function detect() {
		if (self::$isInited)
			return;
		self::$isInited= True;


		$cAgent= new Browser();

		self::$vBrowser = $cAgent->getName();


		if ($cAgent->isRobot()){
			self::$vBot = True;
		}


		if (self::$vBrowser){
			self::$vDefault = True;
			return;
		}


	}

}

?>
