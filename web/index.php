<?
include(__dir__ .'/../core/init.php');
KF::debug(1,0);


include(__dir__ .'/../private/c_core.php');



KF::bind('/', KF::code('ki-frame'));



KF::bind(404, KF::code('404') );

KF::end();
?>
