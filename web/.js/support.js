String.prototype.formatPhone= function(){
// todo 209 (clean, js) +0: format incomplete numbers
	var cPhone= this.split(/[^\d]/).join('').slice(0,11);

	var cPrefix= cPhone.slice(0,1);
	var cCode= cPhone.slice(1,4);
	var cNum1= cPhone.slice(4,7);
	var cNum2= cPhone.slice(7,9);
	var cNum3= cPhone.slice(9,11);

	var out= '';
	if (cPrefix && cPrefix.length)
		out+= (cPrefix!='8'?'+':'') +cPrefix;

	if (cCode && cCode.length)
		 out+= ' ('+cCode;

	if (cNum1 && cNum1.length)
		out+= ') '+cNum1;

	if (cNum2 && cNum2.length)
		out+= '-'+cNum2;

	if (cNum3 && cNum3.length)
		out+= '-'+cNum3;

	return out;
}



/*
Show modal message.
	Params:
	
	_txt
		Message to show

	_stuff
		optional object supplied

		.title
			bold title of message

		.icon
			'glyphicon-' second part of bootstrap glyphicon

		.closeButton
			text for Close button

		.confirmCB
			Callback function for confirm button.
			If not supplied, button is not shown.

		.confirmButton
			text for Confirm button.

		.confirmSuppress
			suppress confirm button from being default.
			It IS if not supplied
*/
function popAlert(_txt, _stuff){
	_stuff= _stuff || {};

	_stuff.title= _stuff.title ||'';
	_stuff.closeButton= _stuff.closeButton ||'Закрыть';
	_stuff.confirmButton= _stuff.confirmButton ||'Подтвердить';

	document.getElementById('modalAlertIcon').className= _stuff.icon?
		("glyphicon glyphicon-" +_stuff.icon) :'';
	document.getElementById('modalAlertTitle').innerHTML=
		(_stuff.title+'').split("\n").join("<br>");
	document.getElementById('modalAlertMsg').innerHTML=
		(_txt+'').split("\n").join("<br>");

	document.getElementById('modalAlertCloseText').innerHTML=
		_stuff.closeButton;

	document.getElementById('modalAlertAcceptText').innerHTML=
		_stuff.confirmButton;


	var showConfirm= (typeof _stuff.confirmCB == 'function');
	document.getElementById('modalAlertAccept').style.display=
		showConfirm? '' : 'none';
	document.getElementById('modalAlertAccept').onclick=
		showConfirm? _stuff.confirmCB :null;

	$('#modalAlert').on('shown.bs.modal', (showConfirm && !_stuff.confirmSuppress)?
		function () {
		    $('#modalAlertAccept').focus();
	    }
	    :function () {
		    $('#modalAlertClose').focus();
		}
	);

    $("#modalAlert").modal('show');
}





function refresh(_addr){
	if (_addr)
		document.location= _addr;
	else
		document.location.reload(true);
}


var DOM= function(_id,_root){
	var allSib= (_root||document).getElementsByTagName('*');
	for (var i= 0; i<allSib.length; i++)
	  if (allSib.item(i).id==_id)
	  	return allSib.item(i);
}



/*
Executes function within Timeout in single context.
If subsequent call with same context occurs within timeout period occurs,
previous function call will be suppressed, and new one will be prepared.

Returns
	Current context,
	New created if not supplied.

Params:
	_fn
		Function to be called

	_timeout
		Delay to call _fn

	_context
		Provided object to be reused in subsequent calls.
		lazyRun() fills back _context.timer and _context.deadline
		If none supplied, new one is created and returned.

	_limitTime
		Optional Date to execute _fn without any more delay

*/
function lazyRun(_fn, _timeout, _context, _limitTime){
	_context= _context ||{};

	if (!_context.timer) //first/outdated call
		_context.deadline= _limitTime? (new Date().valueOf()+ _limitTime) :0;
	else {
		if (_context.deadline){
			var tillDeadline= _context.deadline -new Date().valueOf();
			if (_timeout>tillDeadline)
			  _timeout= tillDeadline>0 ?tillDeadline:0;
		}

		clearTimeout(_context.timer);
	}

	_context.timer= setTimeout(function(){_fn();_context.timer= undefined},_timeout);

	return _context;
}
