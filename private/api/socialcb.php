<?php

if (!$URL->args->type){
    $SOC->socOut();
} else {
    $SOC->socReact($URL->args);
}

redirect('/');
