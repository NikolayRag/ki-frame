<?

/*
Dictionary class.
Provides support for multi-lingual versions of named messages.
Messages are available as member variables as they added
or language is switched.


$DICT= new KiDICT(); //init
$DICT->add('ru',['thing'=>'Штука']); //add 'ru' language
$DICT->add('en',['thing'=>'Gizmo', 'stuff'=>'Dummy'], true); //add 'en'
$DICT->setLanguage('ru'); //defaults to 'ru'

echo $DICT->thing; //'Штука'
echo $DICT->stuff; //'Dummy' fallback
echo $DICT->test; //''


	add($_lang, $_dictArray, $_fallback=false)
		Add new text messages to dictionary.

		$_lang
			Name of language to add into

		$_dictArray
			[name=>text,...] message array

		$_fallback
			Indicates that messages provided are also fallback,
			i.e. they will return when requested if current language
			dont have requested block name.

	setLanguage($_lang)
		Set current language.
		By default, only fallback messages will return;

*/

class KiDICT {
	private
		//[lang=>[name=>text]] 2d array
		$dict=[],
		$currentLang='';

	function __construct(){
		$this->dict['']= [];
	}

	function add ($_lang, $_dictArray, $_fallback=false){
		if (!$_lang){
			$_lang= '';
			$_fallback= false; //it's already fallback lang
		}
		$_lang= "$_lang";


		//new lang
		if (!array_key_exists($_lang, $this->dict))
			$this->dict[$_lang]= [];


		foreach ($_dictArray as $cName=>$cVal){
			$this->dict[$_lang][$cName]= $cVal;

			if ($_fallback)
				$this->dict[''][$cName]= $cVal;
		}

	}


	function setLanguage($_lang){
		$this->currentLang= "$_lang";
	}


	function __get($_name){
		$tryLang= $this->currentLang;

		//check for current language
		if ($tryLang!=''){
			if (
		 		!array_key_exists($tryLang, $this->dict)
		 		|| !array_key_exists($_name, $this->dict[$tryLang])
	 		)
				$tryLang= '';
		}

		//recheck for fallback
		if ($tryLang==''){
			if (
		 		!array_key_exists($tryLang, $this->dict)
		 		|| !array_key_exists($_name, $this->dict[$tryLang])
	 		)
				return '';
		}

		return $this->dict[$tryLang][$_name];
	}

}

?>
