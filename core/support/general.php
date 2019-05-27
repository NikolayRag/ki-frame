<?

define('ROOT', $_SERVER['DOCUMENT_ROOT']);


/*
return named array element
default value is used nothing found
*/
function getA($_arr, $_field, $_default=false){
	if (is_array($_arr))
		return (array_key_exists($_field, $_arr)? $_arr[$_field]: $_default);
	
	if (is_object($_arr))
		return (array_key_exists($_field, $_arr)? $_arr->$_field: $_default);

	return $_default;
}


/*
return first non-false argument
*/
function first(){
	$vars= func_get_args();
	foreach ($vars as $cVal){
		if ($cVal)
	    	return $cVal;
	}

	return $cVal; //return last anyway
}



function dump($_v){
    echo str_replace(["\n"," ","\t"], ["<br>","&nbsp;","&nbsp;&nbsp;&nbsp;&nbsp;"], print_r($_v, True));
}
?>

