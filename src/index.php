<?php

// Función que modifica la url (sacada de stackoverflow)
function set_url( $url )
{
    echo("<script>history.replaceState({},'','$url');</script>");
}

// Cargamos lo necesario para el uso de Twig
require_once '/usr/local/lib/php/vendor/autoload.php';

// Configuramos variables básicas de la aplicación
$paths = require 'paths.php';
$servername = "lamp-mysql8";
$username = "root";
$password = "tiger";
$dbname = "SIBW";
$port = 3306;


//------------------------------------------------------------------------------------------------------
// En este caso este código se repite en todas las páginas que hay (portada y pelicula) por lo que hacemos
// la consulta directamente aqui
$loader = new \Twig\Loader\FilesystemLoader(__DIR__.$paths['templates_path']);
$twig = new \Twig\Environment($loader);


foreach ($paths as $key => $value) {
    $twig->addGlobal($key, $value);
}
// Abrimos la conexión a la base de datos (Se hace todo y luego lo cierra el php especifico)
$conn = new mysqli($servername, $username, $password, $dbname, $port);
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$sql_shared_images = "SELECT name, content FROM image WHERE type = 'background' OR type = 'other'";

$result_shared_images = $conn->query($sql_shared_images);
$shared_images = array();

while($row_shared_images = $result_shared_images->fetch_assoc()){
    array_push($shared_images, 
            $shared_images[$row_shared_images['name']] = base64_encode($row_shared_images["content"]));
}
//------------------------------------------------------------------------------------------------------
// Configuramos la obtención de url
$request = $_SERVER['REQUEST_URI']; // Obtenemos la url completa (después del dominio/localhost)
$tokens = explode('/', trim($request, '/')); // Obtenemos los elementos del link
$page = $tokens[0]; // Obtenemos la página base (siempre será portada o alguna otra) 

if (!isset($page) || $page == '') {
    $page = 'portada'; // Si no hay nada, por defecto es portada
    set_url('/portada'); // Cambiamos la url para que no se vea el localhost
} 

$valid_pages = ['portada', 'pelicula']; // Páginas válidas

if (in_array($page, $valid_pages)) {
    include "$page.php"; // Incluimos la página correspondiente
} else {
    $conn->close(); // Cerramos la conexión si no se accedía a ninguna de las conexiones
    http_response_code(404); // Establece el código de estado HTTP 404
    echo "<h1>Error 404: Página no encontrada</h1>";
    echo "<p>Lo sentimos, la página que buscas no existe.</p>";
    exit();
}
?>
