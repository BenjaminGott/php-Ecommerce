<?php
session_start();

function isUserLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Liste des routes accessibles sans connexion
$publicRoutes = [
    'login.php',
    'register.php',
    'index.php'
    'detail.php'
];

// Récupérer la route demandée
$requestUri = basename($_SERVER['REQUEST_URI']);

// Vérifier si la route nécessite une connexion
if (!in_array($requestUri, $publicRoutes) && !isUserLoggedIn()) {
    // Rediriger vers la page de connexion si non connecté
    header('Location: login.php');
    exit;
}

// Ici, tu peux inclure la logique pour charger les pages selon la route
// Par exemple un switch sur $requestUri pour inclure les fichiers correspondants
switch ($requestUri) {
    case 'index.php':
        include 'pages/index.php';
        break;
    case 'detail.php':
        include 'pages/detail.php';
        break;
    case 'profil.php':
        include 'pages/profil.php';
        break;
    // etc.
    default:
        http_response_code(404);
        echo "Page non trouvée";
        break;
}
