<?
/*
Dictionary class.
Provides support for multi-lingual versions of named messages.
Messages are available as member variables as they added
or language is switched.
*/
class KiDict {
	private
		$__dict=[], //[lang=>[name=>text]] 2d array
		$__currentLang;



	function __construct($_defaultLang=''){
		$this->__currentLang = $_defaultLang;

		$this->__dict[''] = [];
		$this->__dict[$this->__currentLang] = [];
	}



/*
Add new text messages to dictionary.

$_dictArray
	[name=>text,...] message array.


$_lang
	Name of language to add into. Default language if omited.


$_fallback
	Indicates that messages provided are also fallback,
	i.e. they will return when requested if current language
	dont have requested block name.
*/
	function add($_dictArray, $_lang=False, $_fallback=false){
		if ($_lang===False)
			$_lang= $this->__currentLang;
		$_lang= "$_lang";


		//new lang
		if (!array_key_exists($_lang, $this->__dict))
			$this->__dict[$_lang]= [];


		foreach ($_dictArray as $cName=>$cVal){
			$this->__dict[$_lang][$cName]= $cVal;

			if ($_fallback)
				$this->__dict[''][$cName]= $cVal;
		}

	}



/*
Set current language.
*/
	function setLanguage($_lang){
		$this->__currentLang= "$_lang";
	}



/*
Named dictionary variable getter.
*/
	function __get($_name){
		$tryLang= $this->__currentLang;

		//check for current language
		if ($tryLang!=''){
			if (
		 		!array_key_exists($tryLang, $this->__dict)
		 		|| !array_key_exists($_name, $this->__dict[$tryLang])
	 		)
				$tryLang= '';
		}


		$langA = getA($this->__dict, $tryLang, []);
		return getA($langA, $_name, '');
	}

}

?>
