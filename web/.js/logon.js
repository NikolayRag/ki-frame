var LOGON= {

register: function(_name, _pass, _pass2, _cb, _cbErr){
	if (this.checkLocked())
		return;

	this.cb= _cb;
	this.cbErr= _cbErr;

	if (_name== ''){
		this.logonCBErr(0, '', 'Емейл пустой');
		return;
	}

	if (_pass!= _pass2){
		this.logonCBErr(0, '', 'Пароли не одинаковые');
		return;
	}
	if (_pass== ''){
		this.logonCBErr(0, '', 'Пароль пустой');
		return;
	}

    var logonValue= {
    	Password:	_pass,
    	Email:		_name
    }

   SESSION.async('/logon/register', logonValue, this.logonCB.bind(this), this.logonCBErr.bind(this));
},


restore: function(_name, _cb, _cbErr){
	if (this.checkLocked())
		return;

	this.cb= _cb;
	this.cbErr= _cbErr;

	if (_name== ''){
		this.logonCBErr(0, '', 'Емейл пустой');
		return;
	}

    var logonValue= {
    	Email:	_name
    }

    SESSION.async('/logon/restore', logonValue, this.logonCB.bind(this), this.logonCBErr.bind(this));
},

newpass: function(_hash, _email, _pass, _pass2, _cb, _cbErr){
	if (this.checkLocked())
		return;

	this.cb= _cb;
	this.cbErr= _cbErr;

	if (_pass!= _pass2){
		this.logonCBErr(0, '', 'Пароли не одинаковые');
		return;
	}
	if (_pass== ''){
		this.logonCBErr(0, '', 'Пароль пустой');
		return;
	}

    var logonValue= {
    	Password:	_pass,
    	hash:	_hash,
    	Email:	_email
    }

    SESSION.async('/logon/newpass', logonValue, this.logonCB.bind(this), this.logonCBErr.bind(this));
},

login: function(_name, _pass, _cb, _cbErr){
	if (this.checkLocked())
		return;

	this.cb= _cb;
	this.cbErr= _cbErr;

	if (_name== ''){
		this.logonCBErr(0, '', 'Емейл пустой');
		return;
	}

	if (_pass== ''){
		this.logonCBErr(0, '', 'Пароль пустой');
		return;
	}

    var logonValue= {
    	Password:	_pass,
    	Email:		_name
    }

    SESSION.async('/logon/login', logonValue, this.logonCB.bind(this), this.logonCBErr.bind(this));
},

logout: function(_cb, _cbErr){
	if (this.checkLocked())
		return;

	this.cb= _cb;
	this.cbErr= _cbErr;

    var logoutValue= {
	}

	SESSION.async('/logon/logout', logoutValue, this.logonCB.bind(this), this.logonCBErr.bind(this));
},

//todo 3 (lib, login, refactor, code) +0: move 'edit profile' outside of Login
//todo 5 (lib, login, ux) +0: add optional email validation

edit: function(_fio1E, _fio2E, _fio3E, _positionE, _phoneE, _cb, _cbErr){
	if (this.checkLocked())
		return;

	this.cb= _cb;
	this.cbErr= _cbErr;

	if (!_fio1E || !_fio2E || !_fio3E || !_positionE){
		this.logonCBErr(0, '', 'Поля пустые');
		return;
	}

    var logonValue= {
		fio1E:	_fio1E,
		fio2E:	_fio2E,
		fio3E:	_fio3E,
		positionE:	_positionE,
		phoneE:	_phoneE
    }

    SESSION.async('/logon/edit', logonValue, this.logonCB.bind(this), this.logonCBErr.bind(this));
},





//PRIVATE


locked: false,

cb: null,
cbErr: null,

checkLocked: function(){
	if (this.locked){
		console.log('err: logon pending');
		return true;
	}

    this.locked= true;
},

logonCB: function(_res){
	console.log('logon:', _res);
	this.locked= false;

	var jsonVal= JSON.parse(_res);

	if (jsonVal){
		this.logonCBErr(0, '', jsonVal);
		return;
	}

	this.cb && this.cb();
},


logonCBErr: function(_code, _text, _t){
	console.log('err: '+_text +"\n" +_t);

	this.locked= false;
	this.cbErr && this.cbErr(_t);
}

}

