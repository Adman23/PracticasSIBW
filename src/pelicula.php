<?php
require_once '/usr/local/lib/php/vendor/autoload.php';
$paths = require 'paths.php';

$loader = new \Twig\Loader\FilesystemLoader(__DIR__.$paths['templates_path']);
$twig = new \Twig\Environment($loader);

foreach ($paths as $key => $value) {
    $twig->addGlobal($key, $value);
}

// pagina.php
if(isset($_GET['print'])) {
    // El parámetro 'print' existe en la URL
    if($_GET['print'] == '1') {
        // Cargar CSS para imprimir
        $css_print = 'pelicula_imprimir.css';
    }
}

// Código para extraer datos y variables


echo $twig->render('pelicula.html.twig', ['css_print' => $css_print ?? null]);
?>