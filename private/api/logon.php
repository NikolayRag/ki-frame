<?php
require (__dir__ .'/../../_3rd/PHPMailer/PHPMailerAutoload.php');

$error= false;

switch (strtolower(first($URL->path[1], ''))) {
    case 'social': {
        $USER->socCB($URL);
        redirect('/');

        break;
    }

    case 'logout': {
        $error = $USER->logout();

        break;
    }

    case 'login': {
        $error = $USER->passLogin($URL->args->Email, $URL->args->Password);

        break;
    }

    case 'restore': {
        $email= first($URL->args->Email, '');

        $resReset= $USER->flexUser->resetPassword($email);
        if (!$resReset){
            $error= $USER->flexErrorGetLast();
            break;
        }

        $srv= $_SERVER['SERVER_NAME'];
        $mailMessage= "<html><body>Добрый день,<br><br>Для вашего аккаунта на $srv запрошено восстановление пароля.<br>Пройдите по <a href=http://{$srv}/?=reset&hash={$resReset->Confirmation}&email=$email>ЭТОЙ ССЫЛКЕ</a>, чтобы установить новый пароль.<br><br>Если вы не запрашивали изменение пароля, то просто проигнорируйте это письмо.<br><br><a href=http://$srv>$srv</a></body></html>";

        $mail = new PHPMailer;
        $mail->IsSMTP();
        $mail->CharSet = 'UTF-8';

        $mail->Host       = $MAILCFG->SMTP;
        $mail->SMTPAuth   = true;
        $mail->SMTPSecure = "ssl";
        $mail->Port       = 465;
        $mail->Username   = $MAILCFG->USER;
        $mail->Password   = $MAILCFG->PASS;

        $mail->setFrom($MAILCFG->USER, 'Красные Кости');
        $mail->addAddress($URL->args->Email);
        $mail->Subject = 'Восстановление пароля';
        $mail->msgHTML($mailMessage);

        if (! $mail->send())
            $error= 'Ошибка восстановления';

        break;
    }

    case 'newpass': {
        $resReset= $USER->flexUser->newPassword($URL->args->hash,Array('Password'=>$URL->args->newPass));

        $error= $USER->flexErrorGetLast();
        if (!$error)
          $USER->flexUser->login($URL->args->Email, $URL->args->newPass, true);
        break;
    }

    case 'register': {
        $error = $USER->passRegister($URL->args->Email, $URL->args->Password);

        break;
    }
}

echo json_encode($error);


?>