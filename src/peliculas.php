<?php
// Adyacente al controlador de películas, devuelve todas en vez de una




// Devolvemos todas las películas
$sql_films = "SELECT id, name, genre, directors, actors, description FROM film ORDER BY fecha DESC";
$result_films = $conn->query($sql_films);

if ($result_films->num_rows > 0){
    while($row = $result_films->fetch_assoc()){
        $films[] = array(
            'id' =>         $row["id"]          ?? null,
            'name' =>       $row["name"]        ?? null,
            'genre' =>      $row["genre"]       ?? null,
            'directors' =>  $row["directors"]   ?? null,
            'actors' =>     $row["actors"]      ?? null, 
            'description' =>$row["description"] ?? null,
        );
    }
}
else{
    $films = array();
}

$q = isset($_POST['q']) ? trim($_POST['q']) : '';
$filtered_films = [];

if ($q !== '') {
    foreach ($films as $film) {
        if (
            stripos($film['name'], $q) !== false ||
            stripos($film['description'], $q) !== false
        ) {
            $filtered_films[] = $film;
        }
    }
} else {
    $filtered_films = $films;
}

$conn->close();

echo $twig->render('filmList.html.twig', ['films' => $filtered_films,
                                        'user' => $user ?? null, // Heredado del index.php
                                        'user_json' => $user_json ?? null,  // Heredado del index.php
                                        'shared_images' => $shared_images ?? null, // Heredado del index.php
                                        'prohibited_words' => $prohibited_words_json ?? null,]); // Heredado del index.php
?>
