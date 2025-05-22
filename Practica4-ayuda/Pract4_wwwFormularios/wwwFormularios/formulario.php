<?php

  // Ejemplo basado en https://www.tutorialspoint.com/php/php_file_uploading.htm
  
  require_once "/usr/local/lib/php/vendor/autoload.php";

  $loader = new \Twig\Loader\FilesystemLoader('templates');
  $twig = new \Twig\Environment($loader);
  
  $varsParaTwig = [];
  
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(isset($_FILES['imagen'])){
        $errors= array();
        $file_name = $_FILES['imagen']['name'];
        $file_size = $_FILES['imagen']['size'];
        $file_tmp = $_FILES['imagen']['tmp_name'];
        $file_type = $_FILES['imagen']['type'];
        $file_ext = strtolower(end(explode('.',$_FILES['imagen']['name'])));
        
        $extensions= array("jpeg","jpg","png");
        
        if (in_array($file_ext,$extensions) === false){
          $errors[] = "Extensión no permitida, elige una imagen JPEG o PNG.";
        }
        
        if ($file_size > 2097152){
          $errors[] = 'Tamaño del fichero demasiado grande';
        }
        
        if (empty($errors)==true) {
          move_uploaded_file($file_tmp, "imagenesSubidas/" . $file_name);
          
          $varsParaTwig['imagen'] = "imagenesSubidas/" . $file_name;
        }
        
        if (sizeof($errors) > 0) {
          $varsParaTwig['errores'] = $errors;
        }
    }
  }
  
  echo $twig->render('formulario.html', $varsParaTwig);
?>
