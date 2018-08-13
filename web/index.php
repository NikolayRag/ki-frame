<?
include(__dir__ .'/../core/init.php');

include(__dir__ .'/../private/c_core.php');


function eee (){
	echo "<br>" . KF::lifetime();
};


$fn = function (){
	echo "<br>" . implode(',', [1,2,3,4,5]);
};

KF::hSetDebug(1,0);

KF::rReg(__dir__ .'/../private/htmpl/face.php');
KF::rReg("<br>123");
KF::rReg("eee");
KF::rReg($fn);

KF::end();
?>
