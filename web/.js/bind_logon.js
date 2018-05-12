
var formRegister= document.getElementById('formRegister');
if (formRegister) formRegister.onsubmit= function(){
	var pass= document.getElementById('pswR').value;
	var uname= document.getElementById('usrnameR').value;
	var pass2= document.getElementById('psw2').value;

	LOGON.register(uname, pass, pass2, function(){refresh('/')}, popAlert)
	return false;
}


var formRestorePass= document.getElementById('formRestorePass');
if (formRestorePass) formRestorePass.onsubmit= function(){
	var uname= document.getElementById('restoreMail').value;

	LOGON.restore(uname, function(){$("#modalRestorePass").modal('hide'); popAlert("Для установки нового пароля\n на указанную почту отправлено письмо.\n\nПроверьте почту")}, popAlert);
	return false;
};


var formNewPass= document.getElementById('formNewPass');
if (formNewPass) formNewPass.onsubmit= function(){
	var pass= document.getElementById('pswNew').value;
	var pass2= document.getElementById('pswNew2').value;
	var pswHash= document.getElementById('pswHash').value;
	var pswEmail= document.getElementById('pswEmail').value;

	LOGON.newpass(pswHash, pswEmail, pass, pass2, function(){refresh('/')}, popAlert)
	return false;
}


var formLogin= document.getElementById('formLogin');
if (formLogin) formLogin.onsubmit= function(){
	var pass= document.getElementById('psw').value;
	var uname= document.getElementById('usrname').value;

	LOGON.login(uname, pass, function(){refresh('/')}, popAlert);
	return false;
};


var btnLogout= document.getElementById('btnLogout');
if (btnLogout) btnLogout.onclick= function(){
	LOGON.logout(function(){refresh('/')}, popAlert)
};






$('#modalLogin').on('shown.bs.modal', function () {
    $('#usrname').focus();
})

$('#modalRegister').on('shown.bs.modal', function () {
    $('#usrnameR').focus();
})

$('#modalRestorePass').on('shown.bs.modal', function () {
    $('#restoreMail').focus();
})

$('#modalNewPass').on('shown.bs.modal', function () {
    $('#pswNew').focus();
})

