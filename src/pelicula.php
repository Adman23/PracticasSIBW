<?php
/*
Se tienen las siguientes variables relevantes:
- variables de conexión
    - $servername
    - $username
    - $password
    - $dbname
    - $port
- $conn -> Tiene conexión abierta a la base de datos (hace falta cerrarla al final)
- $shared_images -> array con las imagenes compartidas (logos y demás)
- $page -> Página base en la que estamos
- $tokens -> array con los elementos de la url, el 0 es $page
*/



/* DEPRECATED
Extraemos el código de la película y verificamos que es un número

-> Ahora los enlaces se gestionan con url limpias
-> Usar el vector de tokens para comprobar si hay algún ID
-> En este caso, como 0 es $page, el 1 tiene que ser si o si el ID, sino está mal pasado

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $id = filter_var($id, FILTER_VALIDATE_INT);
    if ($id === false) {
        header("Location: portada.php");
        exit();
    } 
}
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

// Para obtener los comentarios de la película (El resultado es una o más tuplas)
$sql_comments = "SELECT author, email, date, text FROM comment WHERE film_id = $id";

$result             = $conn->query($sql_film);
$result_images      = $conn->query($sql_images);
$result_comments    = $conn->query($sql_comments);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    
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

    while($row_comments = $result_comments->fetch_assoc()){
        $comments[] = array(
            'author' => $row_comments["author"] ?? null,
            'email' => $row_comments["email"] ?? null,
            'date' => $row_comments["date"] ?? null,
            'text' => $row_comments["text"] ?? null,
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


// Para obtener las palabras prohibidas
$sql_prohibited_words = "SELECT word FROM banned_words";
$result_prohibited_words = $conn->query($sql_prohibited_words);

if ($result_prohibited_words->num_rows > 0) {
    // Con el resultado extraemos la columna entera de word y la pasamos a un array
    $prohibited_words = $result_prohibited_words->fetch_all(MYSQLI_ASSOC);
    $prohibited_words = array_column($prohibited_words, 'word');

    // Codificamos el array a JSON y lo pasamos a la plantilla para que se lo pase al javaScript
    $prohibited_words_json = json_encode($prohibited_words);
} else {
    $prohibited_words_json = json_encode(array()); // Si no hay que esté vacío y ya
}


$conn->close();

echo $twig->render('pelicula.html.twig', ['css_print' => $css_print, 
                                        'film' => $film ?? null,
                                        'images' => $images ?? null,
                                        'shared_images' => $shared_images ?? null, // Heredado del index.php
                                        'comments' => $comments ?? null,
                                        'prohibited_words' => $prohibited_words_json ?? null,]);
?>