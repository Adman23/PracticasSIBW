<?php

// Esto es una configuración necesaria para que devuelva json que luego
// usa el script
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


// Obtenemos el id
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $id = filter_var($id, FILTER_VALIDATE_INT);
    if ($id === false) {
        http_response_code(404); // Establece el código de estado HTTP 404
        echo "<h1>Error 404: Algún problema con consulta AJAX</h1>";
        echo "<p>Lo sentimos, la página que buscas no existe 1.</p>";
        exit();
    } 
}

if (isset($_GET['order'])) {
    $order = $_GET['order'];
    if (!in_array($order, ['insert', 'edit', 'delete'])) {
        http_response_code(404); // Establece el código de estado HTTP 404
        echo "<h1>Error 404: Algún problema con consulta AJAX</h1>";
        echo "<p>Lo sentimos, la página que buscas no existe 2.</p>";
        exit();
    }
} else {
    $order = null;
}

if (isset($_GET['getAll'])){
    $getAll = $_GET['getAll'];
} else {
    $getAll = false;
}

session_start(); 
// Obtenemos el usuario
if (!isset($_SESSION['username'])) {
    $user = null;                                          // Si no hay usuario, lo inicializamos a null
} else {
    $user = $_SESSION['username']; // Si hay usuario lo obtenemos
}


// Tanto editar como borrar un comentario necesitan el id del comentario

// METER UN COMENTARIO NUEVO
try {
    if ($_SERVER["REQUEST_METHOD"] == "POST" && $order == 'insert') {
        $fcontent = $_POST["fcontent"];
        $film_id = $id;

        $sql = "INSERT INTO comment(film_id, author, date, text) VALUES 
            ($film_id,'$user', NOW(), '$fcontent')";

        if ($conn->query($sql) === FALSE) {
            throw new Exception($conn->error);
        }
    }
} catch (Exception $e) {
    echo json_encode([
        "error" => $e->getMessage(),
        "order" => $order,
    ]);
    exit();
}

// EDITAR UN COMENTARIO EXISTENTE
try {
    if ($_SERVER["REQUEST_METHOD"] == "POST" && $order == 'edit') {
        $fcontent = $_POST["fcontent"];
        $commentId = $_GET['commentId'] ?? null;
        
        $sql = "UPDATE comment SET text = '$fcontent', edited=1 WHERE id = $commentId";

        if ($conn->query($sql) === FALSE) {
            throw new Exception($conn->error);
        }
    }
} catch (Exception $e) {
    echo json_encode([
        "error" => $e->getMessage(),
    ]);
    exit();
}


// BORRAR UN COMENTARIO
try {
    if ($_SERVER["REQUEST_METHOD"] == "POST" && $order == 'delete') {
        $commentId = $_GET['commentId'] ?? null;
        $sql = "DELETE FROM comment WHERE id = $commentId";

        if ($conn->query($sql) === FALSE) {
            throw new Exception($conn->error);
        }
    }
} catch (Exception $e) {
    echo json_encode([
        "error" => $e->getMessage(),
    ]);
    exit();
}





// Esto lo hace siempre
// Para obtener los comentarios de la película (El resultado es una o más tuplas)
if (!$getAll) {
    $sql_comments = "SELECT id, author, date, text, edited FROM comment WHERE film_id = $id";
}
else{
    $sql_comments = "SELECT comment.id AS comment_id, 
                            comment.author, 
                            comment.date, 
                            comment.text, 
                            comment.edited, 
                            film.id AS film_id, 
                            film.name AS film_name
                            FROM comment 
                            JOIN film ON comment.film_id = film.id 
                            ORDER BY date DESC";
}
$result_comments = $conn->query($sql_comments);


if ($result_comments->num_rows > 0) {
    while($row_comments = $result_comments->fetch_assoc()){
        $user_author = $row_comments["author"];
        $email = null;

        if ($user_author) {
            $sql_email = "SELECT role,email FROM user WHERE username = '$user_author'";
            $result_email = $conn->query($sql_email);
            if ($result_email && $row_email = $result_email->fetch_assoc()) {
                $email = $row_email['email'];
                $role = $row_email['role'];
            }
        }
        if ($getAll){
            $comments[] = array(
                'id' => $row_comments["comment_id"] ?? null,
                'author' => $user_author ?? null,
                'role' => $role ?? null,
                'email' => $email ?? null,
                'date' => $row_comments["date"] ?? null,
                'text' => $row_comments["text"] ?? null,
                'edited' => $row_comments["edited"] ?? null,
                'film_id' => $row_comments["film_id"] ?? null,
                'film_name' => $row_comments["film_name"] ?? null,        
            );
        }
        else{
            $comments[] = array(
                'id' => $row_comments["id"] ?? null,
                'author' => $user_author ?? null,
                'role' => $role ?? null,
                'email' => $email ?? null,
                'date' => $row_comments["date"] ?? null,
                'text' => $row_comments["text"] ?? null,
                'edited' => $row_comments["edited"] ?? null,    
            );
        }
    }
} else {
    $comments = array();
}

$conn->close();


$q = isset($_POST['q']) ? trim($_POST['q']) : '';
$filtered_comments = [];


if ($q !== '') {
    foreach ($comments as $comment) {
        if (
            stripos($comment['text'], $q) !== false
        ) {
            $filtered_comments[] = $comment;
        }
    }
} else {
    $filtered_comments = $comments;
}


echo json_encode($filtered_comments);
?>