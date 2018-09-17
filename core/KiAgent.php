<?
/*
Class used for determining incoming connection.
*/
use Sinergi\BrowserDetector\Browser;
use Sinergi\BrowserDetector\Os;
use Sinergi\BrowserDetector\Device;
use Sinergi\BrowserDetector\Language;

//  todo 25 (api, add) +0: detect isLegacy


class KiAgent{
	private static $isInited, $isInitedBrowser, $isInitedOs, $isInitedDevice, $isInitedLanguage;

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

		self::init();

		include (__dir__ . '/../_3rd/php-browser-detector/src/Browser.php');
		include (__dir__ . '/../_3rd/php-browser-detector/src/BrowserDetector.php');


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

		self::init();

		include (__dir__ . '/../_3rd/php-browser-detector/src/Os.php');
		include (__dir__ . '/../_3rd/php-browser-detector/src/OsDetector.php');


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

		self::init();

		include (__dir__ . '/../_3rd/php-browser-detector/src/Device.php');
		include (__dir__ . '/../_3rd/php-browser-detector/src/DeviceDetector.php');


		$cDevice= new Device();

		self::$vDevice = $cDevice->getName();
	}



	static private function detectLanguage() {
		if (self::$isInitedLanguage)
			return;
		self::$isInitedLanguage= True;

		self::init();

		include (__dir__ . '/../_3rd/php-browser-detector/src/Language.php');
		include (__dir__ . '/../_3rd/php-browser-detector/src/LanguageDetector.php');
		include (__dir__ . '/../_3rd/php-browser-detector/src/AcceptLanguage.php');


		$cLang= new Language();

		self::$vLang = $cLang->getLanguages();
	}



	static private function init() {
		if (self::$isInited)
			return;
		self::$isInited = True;


		include (__dir__ . '/../_3rd/php-browser-detector/src/DetectorInterface.php');
		include (__dir__ . '/../_3rd/php-browser-detector/src/UserAgent.php');
		include (__dir__ . '/../_3rd/php-browser-detector/src/InvalidArgumentException.php');
	}

}

?>
