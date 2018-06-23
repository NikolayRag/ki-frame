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
        $error = $USER->passNew($URL->args->Email, $URL->args->Password, $URL->args->hash);

        break;
    }

    case 'register': {
        $error = $USER->passRegister($URL->args->Email, $URL->args->Password);

        break;
    }
}

echo json_encode($error);


?>