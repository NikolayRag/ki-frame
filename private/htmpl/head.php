

<?
    if (!$USER->isSigned){
        include('modal_logLogin.php');
        include('modal_logRegister.php');
        include('modal_logRestorePass.php');
        include('modal_logNewPass.php');
    }

	include('modal_alert.php');
?>


<div style='position:fixed;width:100%;'>
<span style='display:flex;margin:1em'>

<span style='flex-grow:1'></span>


<? if ($USER->isSigned) { ?>
    <span class="dropdown">
        <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown">
            <img src="<?=$USER->photo ?>" alt="" style='width:auto;height:2em'>
            <?=$USER->name?> <span class="caret"></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-right" role="menu">
            <li><a href='#' class='menuButton' role="menuitem" id='btnLogout'>Выйти</a></li>

        </ul>
    </span>

<? } else { ?>
    <button id='loginBtn' type="button" class="btn btn-default" data-toggle="modal" data-target="#modalLogin"><span class="glyphicon glyphicon-off"></span> Войти</button>
<? } ?>
</span>
</div>

<script src="/.js/bind_logon.js"></script>
