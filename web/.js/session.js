DIC= {
	popStateVAsync0: '0',
	popStateVAsync500: 'Проблема с сервером',
	errrAsyncNA: 'async: Internal browser error',
	errrAsyncWrongData: 'async: Wrong data ',
	uploadRestricted: 'Запрещено'
};

var SESSION= new function(){

this.inAsync= false;
this.asyncQueue= [];
this.httpRequest= null;
this.cbError= null;

////ASYNC
this.asyncStatusString= function(_code){
	var verbMsg= '';
	switch (_code){
		case 0: verbMsg= DIC.popStateVAsync0; break;
		case 500: verbMsg= DIC.popStateVAsync500; break;
	}
	return (verbMsg!=''? ' \n'+ verbMsg :'');
}

this.asyncInit= function(){
	this.httpRequest= null;

	if (window.XMLHttpRequest) { // Mozilla, Safari, ...
		this.httpRequest= new XMLHttpRequest();
		if (this.httpRequest.overrideMimeType)
		  this.httpRequest.overrideMimeType('text/plain');
	} else if (window.ActiveXObject) { // IE
		try {this.httpRequest= new ActiveXObject("Msxml2.XMLHTTP");}
		catch (e) {
			try {this.httpRequest = new ActiveXObject("Microsoft.XMLHTTP");}
			catch (e){return}
		}
	}

	return true;
}
/*
_saveURL
_saveData:	Array or Object of POST data passed; SHOULD REMAIN ARRAY TYPE DUE TO MINIFYING
_cbOk:		normal callback
_cbError:		error callback
_sync:		1 for blocked call
_headersA:	additional headers
*/
this.async= function(_saveURL, _saveData, _cbOk, _cbError, _sync, _headersA) {
	if (this.inAsync){
		this.asyncQueue.push(arguments);
		return;
	}

	_cbError= _cbError || this.cbError;

	if (!this.httpRequest && !this.asyncInit()){
		_cbError && _cbError(0, '', DIC.errrAsyncNA);
		return;
	}

	this.inAsync= true;

	this.httpRequest.onreadystatechange = function(_e) {
		if (_e.target.readyState != 4)
		  return;

		if (_e.target.status == 200)
		  _cbOk && _cbOk(_e.target.responseText);
		else
		  _cbError && _cbError(_e.target.status, _e.target.responseText, this.asyncStatusString(_e.target.status));

		this.inAsync= false;

		if (this.asyncQueue.length)
		  this.async.apply(this,this.asyncQueue.shift());
	}.bind(this);


	var saveData= [];
	for (var dName in _saveData)
	  dName && saveData.push([dName,encodeURIComponent(_saveData[dName])].join('='));

	this.httpRequest.open('POST', _saveURL+'?'+saveData.join('&'), !_sync);
	this.httpRequest.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
	for (var iH in _headersA)
	  this.httpRequest.setRequestHeader(_headersA[iH][0], encodeURIComponent(_headersA[iH][1]));
	  
	this.httpRequest.send();


	return this.httpRequest;
}

}


if (!File.prototype.slice){
	if (File.prototype.webkitSlice)
	  File.prototype.slice= File.prototype.webkitSlice;
	else if (File.prototype.mozSlice)
	  File.prototype.slice= File.prototype.mozSlice;
}
