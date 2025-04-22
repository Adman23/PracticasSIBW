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



// Hacemos la conexión a la base de datos
$servername = "lamp-mysql8";
$username = "root";
$password = "tiger";
$dbname = "SIBW";
$port = 3306;

// Código para extraer datos y variables

$conn = new mysqli($servername, $username, $password, $dbname, $port);
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Código repetido con portada.php----------------------------------------------------------------------
$sql_shared_images = "SELECT name, content FROM image WHERE type = 'background' OR type = 'other'";

$result_shared_images = $conn->query($sql_shared_images);
$shared_images = array();

while($row_shared_images = $result_shared_images->fetch_assoc()){
    array_push($shared_images, 
            $shared_images[$row_shared_images['name']] = base64_encode($row_shared_images["content"]));
}
//------------------------------------------------------------------------------------------------------




// Extraemos el código de la película y verificamos que es un número
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $id = filter_var($id, FILTER_VALIDATE_INT);
    if ($id === false) {
        header("Location: portada.php");
        exit();
    } 
}


// Para obtener la película que buscamos (El resultado es una única tupla)
$sql_film = "SELECT * FROM film WHERE id = $id";
$sql_images = "SELECT name, content FROM image WHERE film_id = $id";

$result = $conn->query($sql_film);
$result_images = $conn->query($sql_images);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    
    // Sabemos que cover solo es una tupla, porque id es clave primaria
    $film = array(
        'id' => $row["id"],
        'name' => $row["name"],
        'year' => $row["fecha"],
        'genre' => $row["genre"],
        'directors' => $row["directors"],
        'actors' => $row["actors"],
        'description' => $row["description"],
        // Falta extraer las distintas imágenes
    );

    while($row_images = $result_images->fetch_assoc()){
        $images[] = array(
            'name' => $row_images["name"] ?? null,
            'content' => base64_encode($row_images["content"]),
        );
    }
}
else {
    // No se encontró la película, redirigir a portada
    header("Location: portada.php");
    exit();
}




$conn->close();

echo $twig->render('pelicula.html.twig', ['css_print' => $css_print ?? null, 
                                        'film' => $film ?? null,
                                        'images' => $images ?? null,
                                        'shared_images' => $shared_images ?? null,]);
?>