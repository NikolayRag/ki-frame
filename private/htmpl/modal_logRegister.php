
  <div class="modal" id="modalRegister" role="dialog">
    <div class="modal-dialog">
    
      <!-- Modal content-->
      <div class="modal-content">
      <form role="form" id='formRegister' action='#'>
        <div class="modal-body" style="padding:40px 50px;">
          <div class="form-group">
            <label>Регистрация:</label>
          </div>

          <div class="form-group">
            <label for="usrnameR"><span class="glyphicon glyphicon-user"></span> Емейл</label>
            <input type="text" class="form-control" id="usrnameR" placeholder="Введите емейл">
          </div>
          <br>
          <div class="form-group">
            <label for="pswR"><span class="glyphicon glyphicon-eye-open"></span> Пароль</label>
            <input type="password" class="form-control" id="pswR" placeholder="Придумайте пароль">
          </div>
          <div class="form-group">
            <input type="password" class="form-control" id="psw2" placeholder="Введите пароль еще раз">
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success pull-right" id='btnRegister'><span class="glyphicon glyphicon-off"></span> Зарегистрировать</button>
          <button class="btn btn-danger btn-default pull-left" data-dismiss="modal"><span class="glyphicon glyphicon-remove"></span> Отмена</button>
        </div>
      </form>
      </div>
      
    </div>
  </div> 
