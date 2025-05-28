<?php


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($tokens[1])) {
    $username = $tokens[1];
    $role = $_POST['role'];

    // Consultamos que el rol se vaya a cambiar y que no sea el único superuser
    
    $sql_superuser = "SELECT * FROM user WHERE role = 'superuser'";
    $result_superuser = $conn->query($sql_superuser);
    if ($result_superuser->num_rows == 1 && $result_superuser->fetch_assoc()['username'] == $username) {
        $error = "Cannot change role to the only superuser";
    }
    else{
        $stmt = $conn->prepare("UPDATE user SET role = ? WHERE username = ?");
        $stmt->bind_param("ss", $role, $username);
        $stmt->execute();
        $stmt->close();
        header("Location: /usuarios");
        exit();
    }
}


// Devolvemos todos los usuarios
$sql_users = "SELECT username, email, role FROM user";
$result_users = $conn->query($sql_users);


if ($result_users->num_rows > 0){
    while($row = $result_users->fetch_assoc()){
        $users[] = array(
            'username' => $row["username"] ?? null,
            'email' =>    $row["email"]    ?? null,
            'role' =>     $row["role"]     ?? null,
        );
    }
}
else{
    $users = array();
}

$conn->close();

echo $twig->render('userList.html.twig', [
    'error' => $error ?? null,
    'users' => $users,
    'user' => $user ?? null, // Heredado del index.php
    'user_json' => $user_json ?? null,  // Heredado del index.php
    'shared_images' => $shared_images ?? null, // Heredado del index.php
    'prohibited_words' => $prohibited_words_json ?? null, // Heredado del index.php
]);
?>