<?php
$host = "localhost";
$user = "root";
$pass = "Root";
$db = "php-Ecommerce";


// Connexion au serveur MySQL
$conn = mysqli_connect($host, $user, $pass, $db);

// Vérification de la connexion
if (!$conn) {
    die("Échec de la connexion : " . mysqli_connect_error());
}

echo "Connexion réussie !";

// Exemple de requête simple
$sql = "SELECT * FROM user";
$result = mysqli_query($conn, $sql);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        echo "Ligne : " . json_encode($row) . "<br>";
    }
} else {
    echo "Erreur dans la requête : " . mysqli_error($conn);
}

mysqli_close($conn);
?>
