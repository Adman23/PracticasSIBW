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

$errors = array();

if (!isset($sing)){
    $sing = false;
}
if (!isset($edit)){
    $edit = false;
}

// Ahora mismo es así, cuando se vaya a hacer la modificación de usuarios se cambia
if ($edit){
    $user_edit = $user;
}

// Habrá casos en los que user edit coincida o no, depende de si es modificandose los datos a uno o 
// modificando los datos a otro usuario
if ($_SERVER["REQUEST_METHOD"] == "POST"  && ($sing || $edit)) {
    $username= $_POST["username"] ?? null;
    $new_username= $_POST["new_username"] ?? null;
    $password= $_POST["password"] ?? null;
    $new_password = $_POST["new_password"] ?? null;
    $email= $_POST["email"] ?? null;
    $age= $_POST["age"] ?? null;
    $role = null;

    if ($user && $user['role'] == 'superuser') {
        $role = $_POST["role"] ?? null;
    }

    if ($sing){
        $state=singUp($username, $password, $email, $role, $age, $conn);
    } else { // Se puede usar $_SESSION['username']
        if ($new_username == null || $new_username == '') {
            $new_username = $user_edit['username']; // Si no se ha cambiado el nombre de usuario, lo mantenemos
        }
        $state=logEdit($user_edit['username'], $new_username, $password, $new_password, $email, $role, $age, $conn);
    }

    if ($state == 'success') {
        if ($sing) {
            $_SESSION['username'] = $username;
        } else {
            $_SESSION['username'] = $user['username'];
        }
        header("Location: /logIn");
        exit(); // Evita la ejecución del resto
    } else {
        array_push($errors, $state);
    }

}
else{
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $username = $_POST["username"];
        $password = $_POST["password"];
        $state=checkLogin($username, $password, $conn);
        if ( $state == 'success') {
            $_SESSION['username'] = $username;
            header("Location: /portada");
            exit(); // Evita la ejecución del resto
        } else {
            array_push($errors, $state);
        }
    }
}


$conn->close();

echo $twig->render('logIn.html.twig', [ 'errors' => $errors ?? null,
                                        'edit' => $edit ?? null,
                                        'sing' => $sing ?? null, 
                                        'user' => $user ?? null, 
                                        'shared_images' => $shared_images ?? null, // Heredado del index.php
                                        'prohibited_words' => $prohibited_words_json ?? null,]);
?>