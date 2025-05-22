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


//-------------------------------------------------------------------------------------------------------
// Extraemos el código de la película y verificamos que es un número
if (isset($tokens[1])) {
    $id = $tokens[1];
    $id = filter_var($id, FILTER_VALIDATE_INT);
    if ($id === false) {
        $conn->close();
        http_response_code(404); // Establece el código de estado HTTP 404
        echo "<h1>Error 404: Página no encontrada</h1>";
        echo "<p>Lo sentimos, la página que buscas no existe.</p>";
        exit();
    } 
} else {
    $conn->close();
    http_response_code(404); // Establece el código de estado HTTP 404
    echo "<h1>Error 404: Página no encontrada</h1>";
    echo "<p>Lo sentimos, la página que buscas no existe.</p>";
    exit();
}

// Comprobamos si tiene otro parámetro que sea print 
if (isset($tokens[2])) {
    if ($tokens[2] == 'print') {
        $css_print = true;
    } else {
        $conn->close();
        http_response_code(404); // Establece el código de estado HTTP 404
        echo "<h1>Error 404: Página no encontrada</h1>";
        echo "<p>Lo sentimos, la página que buscas no existe.</p>";
        exit();
    }
} else {
    $css_print = false;
}

// Ya no se aceptan más parámetros
if (isset($tokens[3])) {
    $conn->close();
    http_response_code(404); // Establece el código de estado HTTP 404
    echo "<h1>Error 404: Página no encontrada</h1>";
    echo "<p>Lo sentimos, la página que buscas no existe.</p>";
    exit();
}
//-------------------------------------------------------------------------------------------------------

// Para obtener la película que buscamos (El resultado es una única tupla)
$sql_film = "SELECT * FROM film WHERE id = $id";

// Para obtener las imágenes de la película (El resultado es una o más tuplas)
$sql_images = "SELECT name, content FROM image WHERE film_id = $id";

$result             = $conn->query($sql_film);
$result_images      = $conn->query($sql_images);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    
    $film = array(
        'id' => $row["id"],
        'id_json' => json_encode($row["id"]), // Para el js
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
    // No se encontró la película
    $conn->close();
    http_response_code(404); // Establece el código de estado HTTP 404
    echo "<h1>Error 404: Página no encontrada</h1>";
    echo "<p>Lo sentimos, la página que buscas no existe.</p>";
    exit();
}





$conn->close();

echo $twig->render('pelicula.html.twig', ['user' => $user ?? null, 
                                        'user_json' => $user_json ?? null,  
                                        'css_print' => $css_print, 
                                        'film' => $film ?? null,
                                        'images' => $images ?? null,
                                        'shared_images' => $shared_images ?? null, // Heredado del index.php
                                        'prohibited_words' => $prohibited_words_json ?? null,]);
?>