<?

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

    $mail->send();

    return $mail->ErrorInfo;
}



?>
