<?php
require (__dir__ .'/../../_3rd/PHPMailer/PHPMailerAutoload.php');

$errors= false;

switch (strtolower(first($URL->path[1], ''))) {
    case 'social': {
        $USER->socCB($URL);
        redirect('/');

        break;
    }

    case 'logout': {
        $USER->logout();

        $errors= ['1'=> [0]];

        break;
    }

    case 'login': {
        $USER->flexUser->login($URL->args->Email, $URL->args->Password, true);

        $errors= ["1"=>[$USER->errorGetLast()]];

        break;
    }

    case 'restore': {
        $email= first($URL->args->Email, '');

        $resReset= $USER->flexUser->resetPassword($email);
        if (!$resReset){
            $errors= ["1"=>[$USER->errorGetLast()]];
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
            $errors= ['1'=> ['Ошибка восстановления']];

        break;
    }

    case 'newpass': {
        $resReset= $USER->flexUser->newPassword($URL->args->hash,Array('Password'=>$URL->args->newPass));

        $errors= ["1"=>[$USER->errorGetLast()]];
        if (!$errors['1'][0])
          $USER->flexUser->login($URL->args->Email, $URL->args->newPass, true);
        break;
    }

    case 'register': {
        $stmt= $DB->prepare('SELECT count(*) FROM Users');
        $stmt->execute();
        $arr= $stmt->fetch();

        $USER->flexUser->register([
            'Username'=> "u_{$arr[0]}",
            'Email'=>$URL->args->Email,
            'Password'=>$URL->args->Password
        ]);

        $errors= ["1"=>[$USER->errorGetLast()]];
        if (!$errors['1'][0])
          $USER->flexUser->login($URL->args->Email, $URL->args->Password, true);
        break;
    }
}

if (!$errors['1'][0])
    $errors['1']= [];
echo json_encode($errors);


?>