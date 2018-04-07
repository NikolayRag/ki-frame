<?
/*
Class used for determining incoming connection.

__construct()
	initiates fields once, based on servers HTTP_USER_AGENT string.

type()
	return detected client type:
	1: DEFAULT
		Normal modern browser
	2: MINI
		Mobile browser
	3: LEGACY
		Legacy browser
	4: DUMB
		Minimized clients, like CLI tools
	5: BOT
		Official search crawlers, spiders and 
	6: SCANNER
		Known malware and hacker tools

og()
	return flag determining if client requires mainly OpenGraph data.
	That is expantion field of BOT agents.

*/

class KiCLIENT{
	static $inited, $cType, $cOG=false;

	private function initC(){
		KiCONST::add('CLIENT_TYPE', (object)[
			'DEFAULT'=>	1,
			'MINI'=>	2,
			'LEGACY'=>	3,
			'DUMB'=>	4,
			'BOT'=>	5,
			'SCANNER'=>	6
		]);
	}


	function __construct() {
		if (self::$inited)
			return;

		self::$inited= true;


		$this->initC();

		global $CLIENT_TYPE;

		self::$cType= $CLIENT_TYPE->DEFAULT;

		$scannerNames= 'fake|nmap|nikto|wikto|sf|sqlmap|bsqlbf|w3af|acunetix|havij|appscan|morfeus|zmeu';
		if (preg_match("/$scannerNames/i", getA($_SERVER,'HTTP_USER_AGENT','fake'))){
			self::$cType= $CLIENT_TYPE->SCANNER;
			return;
		}


		$crawlerNames= 'bot|archiver|slurp|teoma|yandex|google|rambler|yahoo|accoona|aspseek|crawler|lycos|scooter|altavista|estyle|scrubby|facebook|facebot|vkshare';
		if (preg_match("/$crawlerNames/i", $_SERVER['HTTP_USER_AGENT'])){
			self::$cType= $CLIENT_TYPE->BOT;

			$OGNames= 'facebook|facebot|vkshare';
			if (preg_match("/$OGNames/i", $_SERVER['HTTP_USER_AGENT'])){
				self::$cOG= true;
			}
		}

	}

	function type(){
		return self::$cType;
	}


	function og(){
		return self::$cOG;
	}
}
?>
