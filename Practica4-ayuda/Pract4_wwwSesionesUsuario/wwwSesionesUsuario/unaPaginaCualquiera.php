<?php
  require_once "/usr/local/lib/php/vendor/autoload.php";

  $loader = new \Twig\Loader\FilesystemLoader('templates');
  $twig = new \Twig\Environment($loader);
  
  require_once 'bdUsuarios.php';
  
  $variablesParaTwig = [];
  
  session_start();
  
  if (isset($_SESSION['nickUsuario'])) {
    $variablesParaTwig['user'] = getUser($_SESSION['nickUsuario']);
  }
  
  echo $twig->render('unaPaginaCualquiera.html', $variablesParaTwig);
?>
