<?php

  /* Ejemplo basado en: */
  /* https://www.php.net/manual/es/session.examples.basic.php */
  
  
  require_once "/usr/local/lib/php/vendor/autoload.php";

  $loader = new \Twig\Loader\FilesystemLoader('templates');
  $twig = new \Twig\Environment($loader);
  
  
  session_start();  // Comprueba si se hainicializado una sesión con anterioridad.
                    // En caso afirmativo rellena la variable $_SESSION con la
                    // información que tenía

  if (!isset($_SESSION['count'])) {
    $_SESSION['count'] = 1;
  } else {
    $_SESSION['count']++;
  }
  
  echo $twig->render('contador.html', ["cuenta" => $_SESSION['count']]);
?>
