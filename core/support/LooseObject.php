<?

/*
Ordinary object that allows accessing non-existent member variables.

__constuct($_obj, $_default=false)
	Initialize with provided Object or Array.
	Default value is provided to be returned 
	when accessing non-existent variable.

all()
	Return array containing all variables.

count()
	Return number of variables existing.

*/

class LooseObject {
	private $obj, $default;

	function __construct($_obj=[], $_default=false){
		$this->obj= (array)(object)$_obj;
		$this->default= $_default;
	}

	public function __set($_property, $_val) {
		$this->obj[$_property]= $_val;
	}

	public function __get($_property) {
		return getA($this->obj, $_property, $this->default);
	}

	function all(){
		return (array)$this->obj;
	}

	function count(){
		return count((array)$this->obj);
	}
}
?>
