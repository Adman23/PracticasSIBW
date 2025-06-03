<?php

//-------------------------------------------------------------------------------------------
// Adyacente al controlador de películas, devuelve todas en vez de una
header('Content-Type: application/json');

require_once 'bdUtils.php'; // Para usuarios

// Configuramos variables básicas de la aplicación
$paths = require 'paths.php';
$servername = "lamp-mysql8";
$username = "root";
$password = "tiger";
$dbname = "SIBW";
$port = 3306;

// Abrimos la conexión a la base de datos (Se hace todo y luego lo cierra el php especifico)
$conn = new mysqli($servername, $username, $password, $dbname, $port);
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
//-------------------------------------------------------------------------------------------

//-------------------------------------------------------------------------------------------
// Obtenemos todas las películas
$sql_films = "SELECT id, name, genre, directors, actors, description FROM film ORDER BY fecha DESC";
$result_films = $conn->query($sql_films);

// Para obtener la carátula de cada película
$stmt = $conn->prepare("SELECT content FROM image WHERE image.film_id = ? AND image.type = 'cover'");
$stmt->bind_param("i", $film_id);

if ($result_films->num_rows > 0){
    while($row = $result_films->fetch_assoc()){

        // Obtenemos la imagen
        $film_id = $row["id"];
        $stmt->execute();
        $cover = $stmt->get_result();
        $cover_row = $cover->fetch_assoc();


        $films[] = array(
            'id' =>         $row["id"]          ?? null,
            'name' =>       $row["name"]        ?? null,
            'genre' =>      $row["genre"]       ?? null,
            'directors' =>  $row["directors"]   ?? null,
            'actors' =>     $row["actors"]      ?? null, 
            'description' =>$row["description"] ?? null,
            'cover' => base64_encode($cover_row["content"]),
        );
    }
}
else{
    $films = array();
}
//-------------------------------------------------------------------------------------------


//-------------------------------------------------------------------------------------------
// FILTRO DE BÚSQUEDA
$q = isset($_POST['q']) ? trim($_POST['q']) : '';
$filtered_films = [];


// Esto es para filtrarlas según el parámetro de búsqueda
if ($q !== '') {
    foreach ($films as $film) {
        if (
            stripos($film['name'], $q) !== false
        ) {
            $filtered_films[] = $film;
        }
    }
} else {
    $filtered_films = $films;
}
//-------------------------------------------------------------------------------------------


// Cerramos la conexión y enviamos las películas resultantes
$conn->close();
echo json_encode($filtered_films);
?>
