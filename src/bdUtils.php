<?php
// Cuando se usa get_user se entiende que se ha hecho checkLogin y guardado el usuario en la sesión
// en caso de algún error lanza un error 500 porque no debería de haber problema
function getUser($username, $conn) {
    // Obtenemos el usuario de la base de datos a partir del username
    if ($conn->ping()){
        $sql_user = "SELECT * FROM user WHERE username = '$username'";
        $result = $conn->query($sql_user);
        if ($result->num_rows > 0) {
            // Devuelve directamente el array asociativo del usuario, parametros son:
            // username, password, role(enum -> superuser, manager, moderator, registered), email, age
            return $result->fetch_assoc(); // Devolvemos el usuario, username es primary key, solo una tupla
        } else {
            http_response_code(500); // Error interno del servidor, condición inesperada
            echo "<h1>Error 500: Error interno del servidor</h1>";
            echo "<p>Lo sentimos, ha ocurrido un error inesperado #1.</p>";
            exit();
        }
    }
    else{
        http_response_code(500); // Error interno del servidor, condición inesperada
        echo "<h1>Error 500: Error interno del servidor</h1>";
        echo "<p>Lo sentimos, ha ocurrido un error inesperado #2.</p>";
        exit();
    }
}

function checkLogin($username, $password, $conn){
    // Comprobamos si el usuario existe

    $sql_user = "SELECT * FROM user WHERE username = '$username'";
    $result = $conn->query($sql_user);
    if ($result->num_rows > 0) {
        // Si existe, comprobamos la contraseña
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            return 'success'; // Contraseña correcta
        } else {
            return 'wrong password'; // Contraseña incorrecta
        }
    } else {
        return 'username doesnt exists'; // Usuario no encontrado
    }
}

function singUp($username, $password, $email, $role, $age, $conn){
    // Comprobamos si el usuario ya existe
    
    $sql_user = "SELECT * FROM user WHERE username = '$username'";
    $result = $conn->query($sql_user);
    if (!$role) {
        $role = 'registered'; // Si no hay rol, el predeterminado es 'registered'
    }
    if ($result->num_rows > 0) {
        return 'username already exists'; // Usuario ya existe
    } else {
        // Si no existe, lo creamos
        // Esto no debería de hacer falta porque el formulario ya hace required, pero por si acaso 
        if ($username == null || $password == null || $email == null || $age == null || $role == null) {
            return 'missing fields'; // Campos obligatorios no proporcionados
        }
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $sql_insert = "INSERT INTO user (username, password, role, email, age) VALUES ('$username', '$password_hash','$role', '$email', '$age')";
        if ($conn->query($sql_insert) === TRUE) {
            return 'success'; // Usuario creado correctamente
        } else {
            return $conn->error; // Error al crear el usuario
        }
    }
}

function logEdit($username, $new_username,  $password, $new_password, $email, $role, $age, $conn){
    // Comprobamos si el usuario ya existe
    $sql_user = "SELECT * FROM user WHERE username = '$username'";
    $result = $conn->query($sql_user);
    if ($result->num_rows > 0) {
        $result = $result->fetch_assoc();
        $fields = [];

        // Añadimos a modificar los que no sean null
        if ($new_username !== null && $new_username !== $username && $new_username !== '') {
            array_push($fields, "username='$new_username'");
        }
        if ($new_password !== null && $new_password !== '' && $new_password !== $password) {
            if (password_verify($password, $result['password'])) {
                $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                array_push($fields, "password='$password_hash'");
            } else {
                return 'wrong password';
            }
        }
        if ($email !== null && $email !== '' && $result['email'] !== $email) {
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                array_push($fields, "email='$email'");
            }
            else {
                return 'invalid email';
            }
        }
        if ($role !== null && $role !== '' && $result['role'] !== $role) {
            // Consultamos que el rol se vaya a cambiar y que no sea el único superuser
            $sql_superuser = "SELECT * FROM user WHERE role = 'superuser'";
            $result_superuser = $conn->query($sql_superuser);
            if ($result_superuser->num_rows == 1) {
                return 'cannot change role to the only superuser';
            }
            else{
                array_push($fields, "role='$role'");
            }
        }
        if ($age !== null && $age !== '' && $result['age'] !== $age) {
            array_push($fields, "age='$age'");
        }

        if (empty($fields)) {
            return 'no fields to update';
        }

        $sql_update = "UPDATE user SET " . implode(', ', $fields) . " WHERE username='$username'";

        if ($conn->query($sql_update) === TRUE) {
            return 'success'; // Usuario editado correctamente
        } else {
            return $conn->error; // Error al editar el usuario
        }
    } else {
        return 'username doesnt exists'; // Usuario no encontrado
    }
}

?>