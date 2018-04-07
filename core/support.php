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


function stopAndRedirect($url) {
    header('Location: ' . $url);

    $content = sprintf(
        '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><meta http-equiv="refresh" content="1;url=%1$s" /><title>Redirecting to %1$s</title></head><body>Redirecting to <a href="%1$s">%1$s</a>.</body></html>',
        htmlspecialchars($url, ENT_QUOTES, 'UTF-8')
    );

    echo $content;

    exit;
}

?>

