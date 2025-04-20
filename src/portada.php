<?php
require_once '/usr/local/lib/php/vendor/autoload.php';
$paths = require 'paths.php';


$loader = new \Twig\Loader\FilesystemLoader(__DIR__.$paths['templates_path']);
$twig = new \Twig\Environment($loader);

foreach ($paths as $key => $value) {
    $twig->addGlobal($key, $value);
}


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


$films = null;

// Para obtener el listado de peliculas
$sql_films = "SELECT * FROM film";

// Para obtener la carátula de cada película
$stmt = $conn->prepare("SELECT content FROM image WHERE image.film_id = ? AND image.type = 'cover'");
$stmt->bind_param("i", $film_id);

$result = $conn->query($sql_films);
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        // Obtenemos la imagen
        $film_id = $row["id"];
        $stmt->execute();
        $cover = $stmt->get_result();
        $cover_row = $cover->fetch_assoc();
        
        // Sabemos que cover solo es una tupla, porque solo hay un image.type cover por film_id
        $films[] = array(
            'name' => $row["name"],
            'year' => $row["fecha"],
            'genre' => ($row["genre"]),
            'directors' => $row["directors"],
            'actors' => $row["actors"],
            'description' => $row["description"],
            'cover' => base64_encode($cover_row["content"]),
        );
    }
}

$conn->close();

echo $twig->render('portada.html.twig', ['films' => $films ?? null]);
?>