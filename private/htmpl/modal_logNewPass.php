
  <div class="modal" id="modalNewPass" role="dialog">
    <div class="modal-dialog">
    
      <!-- Modal content-->
      <div class="modal-content">
      <form role="form" id='formNewPass' action='#'>
        <div class="modal-body" style="padding:40px 50px;">
          <div class="form-group">
            <label>Установка нового пароля:</label>
          </div>

          <div class="form-group">
            <input type="password" class="form-control" id="pswNew" placeholder="Придумайте новый пароль">
          </div>
          <div class="form-group">
            <input type="password" class="form-control" id="pswNew2" placeholder="Введите пароль еще раз">
          </div>
          <input type="hidden" class="form-control" id="pswHash" value='<?=first($URL->args->hash, '')?>'>
          <input type="hidden" class="form-control" id="pswEmail" value='<?=first($URL->args->email, '')?>'>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success pull-right" id='btnNewPass'><span class="glyphicon glyphicon-off"></span> Установить</button>
          <button class="btn btn-danger btn-default pull-left" data-dismiss="modal"><span class="glyphicon glyphicon-remove"></span> Отмена</button>
        </div>
      </form>
      </div>
      
    </div>
  </div> 
