<?php
/*
Se tienen las siguientes variables relevantes:
- variables de conexión
    - $servername
    - $username
    - $password
    - $dbname
    - $port
    - $user
- $conn -> Tiene conexión abierta a la base de datos (hace falta cerrarla al final)
- $shared_images -> array con las imagenes compartidas (logos y demás)
- $page -> Página base en la que estamos
- $tokens -> array con los elementos de la url, el 0 es $page
- $user -> Puede ser null o el usuario que ha iniciado sesión
*/

$films = null;

// Para obtener el listado de peliculas
$sql_films = "SELECT id,name FROM film";

// Para obtener la carátula de cada película
$stmt = $conn->prepare("SELECT content FROM image WHERE image.film_id = ? AND image.type = 'cover'");
$stmt->bind_param("i", $film_id);


// Extraer resultado-----------------------------------------------------------------------------------
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
            'id' => $row["id"],
            'name' => $row["name"],
            'cover' => base64_encode($cover_row["content"]),
        );
    }
}

$conn->close();

echo $twig->render('portada.html.twig', ['user' => $user ?? null, 'films' => $films ?? null, 'shared_images' => $shared_images ?? null]);
?>