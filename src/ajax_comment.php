<?php

// Esto es una configuración necesaria para que devuelva json que luego
// usa el script
header('Content-Type: application/json');

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

// Obtenemos el id
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $id = filter_var($id, FILTER_VALIDATE_INT);
    if ($id === false) {
        http_response_code(404); // Establece el código de estado HTTP 404
        echo "<h1>Error 404: Algún problema con consulta AJAX</h1>";
        echo "<p>Lo sentimos, la página que buscas no existe.</p>";
        exit();
    } 
}

// Para obtener los comentarios de la película (El resultado es una o más tuplas)
$sql_comments = "SELECT author, email, date, text FROM comment WHERE film_id = $id";
$result_comments    = $conn->query($sql_comments);

if ($result_comments->num_rows > 0) {
    while($row_comments = $result_comments->fetch_assoc()){
        $comments[] = array(
            'author' => $row_comments["author"] ?? null,
            'email' => $row_comments["email"] ?? null,
            'date' => $row_comments["date"] ?? null,
            'text' => $row_comments["text"] ?? null,
        );
    }
} else {
    $comments[] = array();
}

$conn->close();

echo json_encode($comments);
?>