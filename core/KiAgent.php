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
use Sinergi\BrowserDetector\Os;
use Sinergi\BrowserDetector\Device;
use Sinergi\BrowserDetector\Language;

//  todo 25 (api, add) +0: detect isLegacy


class KiAgent{
	private static $isInitedBrowser, $isInitedOs, $isInitedDevice, $isInitedLanguage;

	private static $vBrowser, $vDefault, $vBot, $vOs, $vDevice, $vLang;



	static function os() {
		self::detectOs();
		return self::$vOs;
	}



	static function device() {
		self::detectDevice();
		return self::$vDevice;
	}



	static function lang() {
		self::detectLanguage();
		return self::$vLang;
	}



	static function browser() {
		self::detectBrowser();
		return self::$vBrowser;
	}



	static function isKnown() {
		self::detectBrowser();
		return (self::$vDefault || self::$vBot);
	}



	static function isDefault() {
		self::detectBrowser();
		return self::$vDefault;
	}



	static function isBot() {
		self::detectBrowser();
		return self::$vBot;
	}



	static private function detectBrowser() {
		if (self::$isInitedBrowser)
			return;
		self::$isInitedBrowser= True;


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



	static private function detectOs() {
		if (self::$isInitedOs)
			return;
		self::$isInitedOs= True;


		$cOs= new Os();

		self::$vOs = [
			$cOs->getName(),
			$cOs->getVersion(),
			$cOs->getIsMobile()
		];
	}



	static private function detectDevice() {
		if (self::$isInitedDevice)
			return;
		self::$isInitedDevice= True;


		$cDevice= new Device();

		self::$vDevice = $cDevice->getName();
	}



	static private function detectLanguage() {
		if (self::$isInitedLanguage)
			return;
		self::$isInitedLanguage= True;


		$cLang= new Language();

		self::$vLang = $cLang->getLanguages();
	}

}

?>
