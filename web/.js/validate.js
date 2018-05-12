var Validate= function(){

this.validateA= {};

this.add= function(_elName, _fnCheck, _message){
    var cEl= document.getElementById(_elName);

    this.validateA[_elName]= {
        fn: _fnCheck.bind(cEl),
        msg: _message
    };
}

this.test= function(_el){
    if (_el)
        return this.validateA[_el].fn();

    var invalidFields= [];

    for (var cEl in this.validateA){
        if (!this.validateA[cEl].fn())
            invalidFields.push(this.validateA[cEl].msg);
    }

    return invalidFields;
}

}
