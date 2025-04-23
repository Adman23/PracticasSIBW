<?php
// Hacemos la conexión a la base de datos
$servername = "lamp-mysql8";
$username = "root";
$password = "tiger";
$dbname = "SIBW";
$port = 3306;

$conn = new mysqli($servername, $username, $password, $dbname, $port);
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fname = $_POST["fname"];
    $femail = $_POST["femail"];
    $fcontent = $_POST["fcontent"];
    $film_id = $_GET['id'];

    $sql = "INSERT INTO comment(film_id, author, email, date, text) VALUES 
        ($film_id,'$fname','$femail', NOW(), '$fcontent')";

    if ($conn->query($sql) === FALSE) {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}else {
    echo "No se ha podido insertar el comentario";
}


$conn->close();
?>