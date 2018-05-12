
  <div class="modal" id="modalLogin" role="dialog">
    <div class="modal-dialog">
    
      <!-- Modal content-->
      <div class="modal-content">
      <form role="form" id='formLogin' action='#'>
        <div class="modal-body" style="padding:40px 50px;">
            <div class="form-group">
              <label>Вход:</label>
            </div>


        <div class="row">
          <div class="col-xs-4" style='text-align:center'>
            <br>
<?
    foreach ($USER->socUrlA as $type=>$name)
        echo "<a href='{$name['url']}'><img src='{$name['icon']}' style='width:4em;height:4em;'></a><br>";
?>
          </div>

          <div class="col-xs-8">
            <div class="form-group">
              <label for="usrname"><span class="glyphicon glyphicon-user"></span> Емейл</label>
              <input type="text" class="form-control" id="usrname" placeholder="Введите емейл" tabindex=1>
              <a href="#" data-toggle="modal" data-dismiss="modal" data-target="#modalRegister"><h4>Новый пользователь</h4></a>
            </div>
            <br>
            <div class="form-group">
              <label for="psw"><span class="glyphicon glyphicon-eye-open"></span> Пароль</label>
              <input type="password" class="form-control" id="psw" placeholder="Введите пароль" tabindex=2>
              <a href="#" data-toggle="modal" data-dismiss="modal" data-target="#modalRestorePass"><h5>Забыли пароль?</h5></a>
            </div>
          </div>
        </div>



        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success pull-right" id='btnLogin' tabindex=3><span class="glyphicon glyphicon-off"></span> Войти</button>
          <button class="btn btn-danger btn-default pull-left" data-dismiss="modal"><span class="glyphicon glyphicon-remove"></span> Отмена</button>
        </div>
      </form>
      </div>
      
    </div>
  </div> 
