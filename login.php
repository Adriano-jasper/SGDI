<?php  

include "ConfigLogin.php";

?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Main CSS-->
    <link rel="stylesheet" type="text/css" href="css/main.css">
    <!-- Font-icon css-->
    <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <title>Login</title>
  </head>
  <body>
    <a href="index.html" class="back-button">
      &#8592; Voltar
    </a>
    <section class="material-half-bg">
      <div class="cover"></div>
    </section>
    <section class="login-content" style="background: linear-gradient(to bottom,#00365f 50%, #f8f9fa 50%);">
      <div class="logo">
        <h1>PGDI</h1>
      </div>
      <div class="login-box">
        <form class="login-form" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
          <h3 class="login-head" style="color: #00365f;"><i class="fa fa-lg fa-fw fa-user"></i>Login</h3>
          <div class="form-group">
            <label class="control-label">Email</label>
            <input class="form-control" type="text" placeholder="Email" name="email" autofocus>
          </div>
          <div class="form-group">
            <label class="control-label">Senha</label>
            <input class="form-control" type="password" placeholder="Password" name="senha">
          </div>
          <div class="form-group">
            <div class="utility">
              <p class="semibold-text mb-2"><a href="#" data-toggle="flip" style="color: #00365f;">Esqueceste a Senha ?</a></p>
            </div>
          </div>
          <div class="form-group btn-container">
            <button name="enviar" class="btn btn-primary btn-block" style="background-color: #00365f; border: #00365f; "><i class="fa fa-sign-in fa-lg fa-fw"></i>SIGN IN</button>
          </div>
        </form>
        <form class="forget-form" action="#">
          <h3 class="login-head" style="color: #00365f;"><i class="fa fa-lg fa-fw fa-lock"></i>Esqueceste a Senha ?</h3>
          <div class="form-group">
            <label class="control-label">EMAIL</label>
            <input class="form-control" type="text" placeholder="Email">
          </div>
          <div class="form-group btn-container">
            <button class="btn btn-primary btn-block" style="background-color: #00365f; border: #00365f;"><i class="fa fa-unlock fa-lg fa-fw"></i>RESET</button>
          </div>
          <div class="form-group mt-3">
            <p class="semibold-text mb-0"><a href="#" data-toggle="flip" style="color: #00365f;"><i class="fa fa-angle-left fa-fw"></i>Voltar para Login</a></p>
          </div>
        </form>
      </div>
    </section>
    <!-- Essential javascripts for application to work-->
    <script src="js/jquery-3.2.1.min.js"></script>
    <script src="js/popper.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/main.js"></script>
    <!-- The javascript plugin to display page loading on top-->
    <script src="js/plugins/pace.min.js"></script>
    <script type="text/javascript">
      // Login Page Flipbox control
      $('.login-content [data-toggle="flip"]').click(function() {
      	$('.login-box').toggleClass('flipped');
      	return false;
      });
    </script>
  </body>
</html>