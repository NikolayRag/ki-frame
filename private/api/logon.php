<?php
require (__dir__ .'/../../_3rd/PHPMailer/PHPMailerAutoload.php');

$errors= false;

switch (strtolower(first($URL->path[1], ''))) {
    case 'logout': {
        $USER->logout();

        $errors= $USER->log->getAllErrors();
        if (!count( getA($errors,'User::registration',[]) ))
            $errors= ['User::logout'=> []];

        break;
    }

    case 'login': {
        $USER->login($URL->args->Email, $URL->args->Password, true);

        $errors= $USER->log->getAllErrors();

        break;
    }

    case 'restore': {
        $email= first($URL->args->Email, '');

        $resReset= $USER->resetPassword($email);
        if (!$resReset){
            $errors= $USER->log->getAllErrors();
            break;
        }

        $srv= $_SERVER['SERVER_NAME'];
        $mailMessage= "<html><body>Добрый день,<br><br>Для вашего аккаунта на $srv запрошено восстановление пароля.<br>Пройдите по <a href=http://{$srv}/log_reset_pass?hash={$resReset->Confirmation}&email=$email>ЭТОЙ ССЫЛКЕ</a>, чтобы установить новый пароль.<br><br>Если вы не запрашивали изменение пароля, то просто проигнорируйте это письмо.<br><br><a href=http://$srv>$srv</a></body></html>";

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
            $errors= ['User::restore'=> ['Ошибка восстановления']];

        break;
    }

    case 'newpass': {
        $resReset= $USER->newPassword($URL->args->hash,Array('Password'=>$URL->args->newPass));

        $errors= $USER->log->getAllErrors();
        if (!count($errors['User::newPassword']))
          $USER->login($URL->args->Email, $URL->args->newPass, true);
        break;
    }

    case 'register': {
        //make fake username
        $stmt= $DB->prepare('SELECT count(*) FROM Users');
        $stmt->execute();
        $arr= $stmt->fetch();

        $USER->register([
            'Username'=> "User{$arr[0]}",
            'Email'=>$URL->args->Email,
            'Password'=>$URL->args->Password
        ]);

        $errors= $USER->log->getAllErrors();
        if (!count($errors['User::registration']))
          $USER->login($URL->args->Email, $URL->args->Password, true);
        break;
    }

    case 'edit': {
      try{
            $stmt= $DB->prepare('REPLACE INTO users_accounts (id_user, name1, name2, name3, position, phone) VALUES (?,?,?,?,?,?)');
            $stmt->execute([$USER->ID, $URL->args->fio1E, $URL->args->fio2E, $URL->args->fio3E, $URL->args->positionE, $URL->args->phoneE]);

            $errors= ['User::edit'=> []];
        } catch ( PDOException $Exception ){
            $errors= ['User::edit'=> [$Exception]];
        }
        break;
    }
}

echo json_encode($errors);

?>