<?php
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
        $error = $USER->passRestore($URL->args->Email);

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