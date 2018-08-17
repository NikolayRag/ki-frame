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

function redirect($url) {
    header('Location: ' . $url);

    $content = sprintf(
        '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><meta http-equiv="refresh" content="1;url=%1$s" /><title>Redirecting...</title></head><body>Redirecting...</body></html>',
        htmlspecialchars($url, ENT_QUOTES, 'UTF-8')
    );

    echo $content;
}



/*
Send email.
*/
function sendMail($_smtp, $_user, $_pass, $_email, $_from, $_subj, $_body, $_port=465){
	require (__dir__ .'/../../_3rd/PHPMailer/PHPMailerAutoload.php');

    $mail = new PHPMailer;
    $mail->IsSMTP();
    $mail->CharSet = 'UTF-8';

    $mail->Host       = $_smtp;
    $mail->SMTPAuth   = true;
    $mail->SMTPSecure = "ssl";
    $mail->Port       = $_port;
    $mail->Username   = $_user;
    $mail->Password   = $_pass;

    $mail->setFrom($_user, $_from);
    $mail->addAddress($_email);
    $mail->Subject = $_subj;
    $mail->msgHTML($_body);

    return $mail->send();
}

?>

