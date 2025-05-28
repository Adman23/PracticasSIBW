<?php
// Custom exception for insert errors

mysqli_report(MYSQLI_REPORT_OFF);
class ExceptionInsert extends Exception {}
class ExceptionInsertImage extends Exception {}
class ExceptionEdit extends Exception {}

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


// CONTROLADOR DE PELÍCULAS, SE LANZA DESDE INDEX


// DOS COMPORTAMIENTOS PARA TENER TODAS LAS OPERACIONES CRUD
/*
    CASO 1 -->  NO es post y por lo tanto comprobará que esté --> /pelicula/id (read)
                y si no está es error, pero también comprobará parámetro opcional --> /pelicula/id/print
    CASO 2 -->  SI es post, por lo que cogerá el argumento que tiene que ser uno de los permitidos
                insert (create), edit (update) o delete (delete) 

*/

$css_print = false;
$null = NULL;
$errors = array();
if ($_SERVER["REQUEST_METHOD"] == "POST"){
    // Comprobamos el primer argumento que tiene que ser la orden si es insert
    if (isset($tokens[1])){
        $insert = $tokens[1];
    }
    else{
        $conn->close();
        http_response_code(404); // Establece el código de estado HTTP 404
        echo "<h1>Error 404: Instrucción no es válida</h1>";
        echo "<p>Lo sentimos, no has indicado instrucción.</p>";
        exit();
    }

    if (isset($tokens[2])){
        $edit = $tokens[2];
    }
    else{
        $conn->close();
        http_response_code(404); // Establece el código de estado HTTP 404
        echo "<h1>Error 404: Instrucción no es válida</h1>";
        echo "<p>Lo sentimos, no has indicado instrucción.</p>";
        exit();
    }

    if (in_array($insert, ['insert', 'edit'])){
        $order = $insert;
    }
    else if ($edit=='edit'){
        $order = $edit;
    }
    else{   // Si es post y no es ninguna de esas órdenes ponemos error
        $conn->close();
        http_response_code(404); // Establece el código de estado HTTP 404
        echo "<h1>Error 404: Instrucción no es válida</h1>";
        echo "<p>Lo sentimos, la instrucción indicada no es válida.</p>";
        exit();
    }


    // INSERT --> No requiere del id
    try {
        if ($order == 'insert') {
            $name = $conn->real_escape_string($_POST['name']);
            $fecha = $conn->real_escape_string($_POST['date']);
            $genre = $conn->real_escape_string($_POST['genre']);
            $directors = $conn->real_escape_string($_POST['directors']);
            $actors = $conn->real_escape_string($_POST['actors']);
            $description = $conn->real_escape_string($_POST['description']);
            $hashtags = $conn->real_escape_string($_POST['hashtags']);


            $sql = "INSERT INTO film
            (name, fecha, genre, directors, actors, description, hashtags) VALUES
                    ('$name', '$fecha', '$genre', '$directors', '$actors', '$description', '$hashtags')";

            if ($conn->query($sql) === FALSE) {
                throw new ExceptionInsert($conn->error);
            }
            $id = $conn->insert_id;
            
            // En caso de haber insertado bien la película insertamos las imágenes asociadas
            if (isset($_FILES["cover"])){
                if ($_FILES["cover"]['error'] !== UPLOAD_ERR_OK){
                    throw new ExceptionInsertImage("No se ha subido bien la portada");
                }
                
                // $id es el film_id
                $cover_name = $name . "_cover";
                $type = "cover";
                $cover = file_get_contents($_FILES["cover"]["tmp_name"]); // Esto es el content

                $stmt = $conn->prepare("INSERT INTO image (film_id, name, type, content) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("issb", $id, $cover_name, $type, $null);
                $stmt->send_long_data(3, $cover);
                if (!$stmt->execute()) {
                    throw new ExceptionInsertImage($stmt->error);
                }
                $stmt->close();
            }
            else{
                throw new ExceptionInsertImage("No hay portada");
            }

            // No es estrictamente necesario que tenga imágenes
            if (in_array(UPLOAD_ERR_OK, $_FILES['images']['error'])){
                $images = $_FILES["images"];
                $num_images = count($images["name"]);
                for ($i = 0; $i<$num_images; $i++){
                    if ($images['error'][$i] === UPLOAD_ERR_OK){
                        $tmp_name = $images['tmp_name'][$i];
                        $im_content = file_get_contents($tmp_name);
                        $im_name = pathinfo($images['name'][$i], PATHINFO_FILENAME);
                        $type = "review";
                                
                        $stmt = $conn->prepare("INSERT INTO image (film_id, name, type, content) VALUES (?, ?, ?, ?)");
                        $stmt->bind_param("issb", $id, $im_name, $type, $null);
                        $stmt->send_long_data(3, $im_content);
                        if (!$stmt->execute()) {
                            throw new ExceptionInsertImage($stmt->error);
                        }
                        $stmt->close();
                    }
                }
            }
            
            $conn->close();
            header("Location: /pelicula/$id");
            exit();
        }
        else{ // Tiene que ser una de las otras dos
            // Obtenemos el id
            if (isset($tokens[1])) {
                $id = $tokens[1];
                $id = filter_var($id, FILTER_VALIDATE_INT);
                if ($id === false) {
                    $conn->close();
                    http_response_code(404); // Establece el código de estado HTTP 404
                    echo "<h1>Error 404: Algún problema obteniendo el identificador</h1>";
                    echo "<p>Lo sentimos, El identificador no es válido</p>";
                    exit();
                } 
            }
            
            // EDIT
            if ($order == 'edit' && $_SERVER["REQUEST_METHOD"] == "POST") {
                $fields = [];
                $types = '';
                $values = [];

                if (isset($_POST['name']) && $_POST['name'] !== '') {
                    $fields[] = "name=?";
                    $types .= 's';
                    $values[] = $_POST['name'];
                }
                if (isset($_POST['date']) && $_POST['date'] !== '') {
                    $fields[] = "fecha=?";
                    $types .= 's';
                    $values[] = $_POST['date'];
                }
                if (isset($_POST['genre']) && $_POST['genre'] !== '') {
                    $fields[] = "genre=?";
                    $types .= 's';
                    $values[] = $_POST['genre'];
                }
                if (isset($_POST['directors']) && $_POST['directors'] !== '') {
                    $fields[] = "directors=?";
                    $types .= 's';
                    $values[] = $_POST['directors'];
                }
                if (isset($_POST['actors']) && $_POST['actors'] !== '') {
                    $fields[] = "actors=?";
                    $types .= 's';
                    $values[] = $_POST['actors'];
                }
                if (isset($_POST['description']) && $_POST['description'] !== '') {
                    $fields[] = "description=?";
                    $types .= 's';
                    $values[] = $_POST['description'];
                }
                if (isset($_POST['hashtags']) && $_POST['hashtags'] !== '') {
                    $fields[] = "hashtags=?";
                    $types .= 's';
                    $values[] = $_POST['hashtags'];
                }

                if (empty($fields)) {
                    throw new ExceptionEdit("No fields to update");
                }

                if (!isset($id)) {
                    throw new ExceptionEdit("Film ID is required for edit");
                }

                $sql = "UPDATE film SET " . implode(', ', $fields) . " WHERE id=?";
                $types .= 'i';
                $values[] = $id;

                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    throw new ExceptionEdit($conn->error);
                }

                $stmt->bind_param($types, ...$values);

                if (!$stmt->execute()) {
                    throw new ExceptionEdit($stmt->error);
                }

                $stmt->close(); 

                if ($_FILES["cover"]["error"] != UPLOAD_ERR_NO_FILE){
                    if ($_FILES["cover"]['error'] !== UPLOAD_ERR_OK){
                        throw new ExceptionEdit("No se ha subido bien la portada (edit)");
                    }
                    $cover_name = "cover";
                    $cover = file_get_contents($_FILES["cover"]["tmp_name"]); // Esto es el content

                    $stmt = $conn->prepare("UPDATE image SET name=?, content=? WHERE film_id = ?");
                    $stmt->bind_param("sbi", $cover_name, $null, $id);
                    $stmt->send_long_data(1, $cover);
                    if (!$stmt->execute()) {
                        throw new ExceptionEdit($stmt->error);
                    }
                    $stmt->close();
                }
                
                // Si se quiere borrar alguna imagen
                if (isset($_POST["delete_images"])){
                    $ids = $_POST["delete_images"];
                    $num_ids = count($_POST["delete_images"]);
                    for ($i = 0; $i < $num_ids; $i++){
                        $img_id = intval($ids[$i]);
                        $stmt = $conn->prepare("DELETE FROM image WHERE id = ?");
                        $stmt->bind_param("i", $img_id);
                        if (!$stmt->execute()) {
                            $stmt->close();
                            throw new ExceptionEdit($conn->error);
                        }
                        $stmt->close();
                    }
                }

                if (in_array(UPLOAD_ERR_OK, $_FILES['images']['error'])){
                    $images = $_FILES["images"];
                    $num_images = count($images["name"]);
                    for ($i = 0; $i<$num_images; $i++){
                        if ($images['error'][$i] === UPLOAD_ERR_OK){
                            $tmp_name = $images['tmp_name'][$i];
                            $im_content = file_get_contents($tmp_name);
                            $im_name = pathinfo($images['name'][$i], PATHINFO_FILENAME);
                            $type = "review";
                                    
                            $stmt = $conn->prepare("INSERT INTO image (film_id, name, type, content) VALUES (?, ?, ?, ?)");
                            $stmt->bind_param("issb", $id, $im_name, $type, $null);
                            $stmt->send_long_data(3, $im_content);
                            if (!$stmt->execute()) {
                                throw new ExceptionInsertImage($stmt->error);
                            }
                            $stmt->close();
                        }
                    }
                }

                $conn->close();
                header("Location: /pelicula/$id");
                exit();
            }                
        }
    }
    catch (ExceptionEdit $e){
        $page = "editPelicula";
        array_push($errors, $e->getMessage());
    }
    catch (ExceptionInsertImage $e){
        $sql = "DELETE FROM film WHERE id = $id";
        if ($conn->query($sql) === FALSE) {
            throw new ExceptionInsert("No se ha podido borrar la película insertada y falló meter la imagen");
        }
        throw new ExceptionInsert($e->getMessage());
        exit();
    }
    catch (ExceptionInsert $e) {
        $conn->close();
        array_push($errors, $e->getMessage());
        echo $twig->render('insert_pelicula.html.twig', ['errors' => $errors,
                                                'user' => $user ?? null, // Heredado del index.php
                                                'user_json' => $user_json ?? null,  // Heredado del index.php
                                                'shared_images' => $shared_images ?? null, // Heredado del index.php
                                                'prohibited_words' => $prohibited_words_json ?? null,]);
        exit();
    }
    catch (Exception $e){
        $conn->close();
        echo "Hubo un problema {$e->getMessage()}";
        exit();
    }

    
}       // En caso de que no sea POST (comportamiento normal)
else{
    //-------------------------------------------------------------------------------------------------------
    // Extraemos el código de la película y verificamos que es un número
    if (isset($tokens[1])) {
        $id = $tokens[1];
        // Caso para insert (renderizar primero la página del formulario)
        if ($id === 'insert'){
            $conn->close();
            echo $twig->render('insert_pelicula.html.twig', ['errors' => $errors,
                                                    'user' => $user ?? null, // Heredado del index.php
                                                    'user_json' => $user_json ?? null,  // Heredado del index.php
                                                    'shared_images' => $shared_images ?? null, // Heredado del index.php
                                                    'prohibited_words' => $prohibited_words_json ?? null,]);
            exit();
        }
        // --------------------------------------------------------------

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

    $page = "pelicula";
    // Comprobamos si tiene otro parámetro que sea print
    if (isset($tokens[2])) {
        if ($tokens[2] == 'print') {
            $css_print = true;
        } 
        else if ($tokens[2] == "edit"){
            $page = "editPelicula";
        }
        else if ($tokens[2] == "delete"){
            // DELETE
            $sql = "DELETE FROM film WHERE id = $id";

            if ($conn->query($sql) === FALSE) {
                throw new Exception($conn->error);
            }
            header("Location: /portada");
            exit();
        }
        else{
            $conn->close();
            http_response_code(404); // Establece el código de estado HTTP 404
            echo "<h1>Error 404: Página no encontrada</h1>";
            echo "<p>Lo sentimos, la página que buscas no existe.</p>";
            exit();
        }
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
}


// Para obtener la película que buscamos (El resultado es una única tupla)
$sql_film = "SELECT * FROM film WHERE id = $id";

// Para obtener las imágenes de la película (El resultado es una o más tuplas)
$sql_images = "SELECT id, name, content, type FROM image WHERE film_id = $id";

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
        'hashtags' => $row["hashtags"],
        // Falta extraer las distintas imágenes
    );

    while($row_images = $result_images->fetch_assoc()){
        $images[] = array(
            'id' => $row_images["id"] ?? null,
            'name' => $row_images["name"] ?? null,
            'content' => base64_encode($row_images["content"]),
            'type' =>$row_images["type"] ?? null,
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

echo $twig->render($page.'.html.twig', ['errors' => $errors ?? null,
                                        'user' => $user ?? null, // Heredado del index.php
                                        'user_json' => $user_json ?? null,  // Heredado del index.php
                                        'css_print' => $css_print, 
                                        'film' => $film ?? null,
                                        'images' => $images ?? null,
                                        'shared_images' => $shared_images ?? null, // Heredado del index.php
                                        'prohibited_words' => $prohibited_words_json ?? null,]);
?>