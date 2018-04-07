<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
            
        <title></title>

        <script>
        <? foreach (KiCONST::dump() as $cName=>$cCont)
            echo "var {$cName}= " .json_encode($cCont) .";\n";
        ?>
        </script>
</head>
<body>


    <?php if ($SOC->socError) : ?>
        err:<?php echo $SOC->socError ?>
    <?php elseif ($SOC->socUser): ?>

        <a href="<?= $SOC->socUser->profileUrl ?>">
            <img src="<?=$SOC->socUser->photoUrl ?>" alt="">

            Name: <?="{$SOC->socUser->firstName} {$SOC->socUser->lastName}"?><br>
            Nickname: <?=$SOC->socUser->nickname?><br>
        </a>

        <a href="<?=$SOC->socialOutURL?>">Выйти</a>

    <?php else : 
        $socNamesA=['','Vk','Mailru','Fb','Git','Twitter'];
        foreach ($SOC->socialA as $type=>$v)
            echo "<a href='{$v['url']}'>{$socNamesA[$type]}</a>";
    endif; ?>


</body>
</html>

