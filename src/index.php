<?php
session_start();  // session_destroy(); 
// Función que modifica la url (sacada de stackoverflow)
function set_url( $url )
{
    echo("<script>history.replaceState({},'','$url');</script>");
}

// Cargamos lo necesario para el uso de Twig
require_once '/usr/local/lib/php/vendor/autoload.php';
require_once 'bdUtils.php'; // Funciones para la base de datos

// Configuramos variables básicas de la aplicación
$paths = require 'paths.php';
$servername = "lamp-mysql8";
$username = "root"; // alternativa root / en la base de datos del portatil tengo adam
$password = "tiger"; // alternativa tiger / en la base de datos del portatil tengo navarro
$dbname = "SIBW";
$port = 3306;


//------------------------------------------------------------------------------------------------------
// En este caso este código se repite en todas las páginas que hay (portada y pelicula) por lo que hacemos
// la consulta directamente aqui
$loader = new \Twig\Loader\FilesystemLoader(__DIR__.$paths['templates_path']);
$twig = new \Twig\Environment($loader);


foreach ($paths as $key => $value) {
    $twig->addGlobal($key, $value);
}

// Abrimos la conexión a la base de datos (Se hace todo y luego lo cierra el php especifico)
$conn = new mysqli($servername, $username, $password, $dbname, $port);
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$sql_shared_images = "SELECT name, content FROM image WHERE type = 'background' OR type = 'other'";

$result_shared_images = $conn->query($sql_shared_images);
$shared_images = array();

while($row_shared_images = $result_shared_images->fetch_assoc()){
    array_push($shared_images, 
            $shared_images[$row_shared_images['name']] = base64_encode($row_shared_images["content"]));
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



//------------------------------------------------------------------------------------------------------
// Configuramos la obtención de url
$request = $_SERVER['REQUEST_URI']; // Obtenemos la url completa (después del dominio/localhost)
$tokens = explode('/', trim($request, '/')); // Obtenemos los elementos del link
$page = $tokens[0]; // Obtenemos la página base (siempre será portada o alguna otra) 

if (!isset($page) || $page == '') {
    $page = 'portada'; // Si no hay nada, por defecto es portada
    set_url('/portada'); // Cambiamos la url para que no se vea el localhost
}


// Comenzamos la sesión del usuario en caso de que no esté iniciada
/*
    El tamaño que tienen las cookies no suele ser muy grande, por lo que la estructura que 
    voy a usar es guardar el username del usuario en la sesión, en caso de que esté 
    se pasa a twig el usuario (usamos una función de php para obtener el usuario a partir del username,
    definida abajo)
*/

if (!isset($_SESSION['username'])) {
    $user = null;                                           // Si no hay usuario, lo inicializamos a null
} else {
    $user = getUser($_SESSION['username'], $conn) ?? null; // Si hay usuario lo obtenemos, en este punto la conexión ya está abierta
    $user_json = json_encode($user);                       // Lo pasamos a json para poder usarlo en javascript
}

$log_pages = ['signUp', 'logOut', 'logEdit']; // Páginas que están asociadas a logIn
$db_use_pages = ['pelicula', 'logIn', "usuarios"]; // Páginas válidas que usan la conexión a la base de datos
$render_only_pages = ['commentList', 'peliculas', 'portada'];


if (in_array($page, $log_pages)) {

    if ($page == 'logOut') {
        session_destroy();
        $user = null; 
    }
    else
    if ($page == 'signUp') {
        $sing = true;
    }
    else
    if ($page == 'logEdit') {
        $edit = true;
    }

    $page = 'logIn'; // Si la página es de logIn, la redirigimos a logIn
}


if (in_array($page, $render_only_pages)) {
    $conn->close(); // Cerramos la conexión a base de datos
    echo $twig->render($page.'.html.twig', [    'user' => $user ?? null, 
                                                'user_json' => $user_json ?? null, 
                                                'shared_images' => $shared_images ?? null,
                                                'prohibited_words' => $prohibited_words_json ?? null,]);
}
else{
    if (in_array($page, $db_use_pages)) {
        include "$page.php"; // Incluimos la página correspondiente
    } else {
        $conn->close(); // Cerramos la conexión 
        http_response_code(404); // Establece el código de estado HTTP 404
        echo "<h1>Error 404: Página no encontrada</h1>";
        echo "<p>Lo sentimos, la página '$page' no existe.</p>";
        exit();
    }
}
?>
