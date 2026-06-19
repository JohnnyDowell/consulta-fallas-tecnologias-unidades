<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="./../libraries/bootstrap-4.3.1/css/bootstrap.min.css" rel="stylesheet"/>
    <link rel="icon" type="image/x-icon" href="./../images/favicon.png">
    <title>Iniciar sesión</title>
    <style media="screen">
      body{
        margin        : 0;
        padding       : 0;
        display       : grid;
        place-content : center;
        min-height    : 100vh;
      }
      @keyframes tada {
        0% { transform: scale(1); }
        10%, 20% { transform: scale(0.9) rotate(-3deg); }
        30%, 50%, 70%, 90% { transform: scale(1.1) rotate(3deg); }
        40%, 60%, 80% { transform: scale(1.1) rotate(-3deg); }
        100% { transform: scale(1) rotate(0); }
      }

      .tada-animation {
        display: inline-block;
        animation: tada 1s ease-in-out;
      }
    </style>
</head>
<body class="bg-info">
  <div class="container d-flex justify-content-center">
    <form action="validator.php" method="post" class="card shadow border-dark">
      <div class="card-title">
        <h1 class="p-2 bg-primary text-white">Iniciar sesión</h1>
      </div>
      <div class="card-body d-flex flex-column justify-content-center">
        <input name="user" required type="text" placeholder="Usuario" class="form-control">
        <br>
        <input name="pass" required type="password" placeholder="Contraseña" class="form-control">
        <br>
        <input type="submit" value="Acceder" class="btn btn-outline-primary">
        <?php
          session_start();
          if(!empty($_SESSION["error-message"])) {
            $message = $_SESSION["error-message"];
            ?>
            <div class="my-2 alert alert-danger tada-animation" role="alert">
              <?php echo $message ?>
            </div>
            <?php
          }
          session_destroy();
        ?>
      </div>
    </form>
  </div>
 </body>
</html>
