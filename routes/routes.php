<?php

function isUserLoggedIn() {
    return isset($_SESSION['user_id']);
}


$publicRoutes = [
    'login.php',
    'register.php',
    'index.php'
    'detail.php'
];


$requestUri = basename($_SERVER['REQUEST_URI']);

if (!in_array($requestUri, $publicRoutes) && !isUserLoggedIn()) {
    header('Location: login.php');
    exit;
}


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
    default:
        http_response_code(404);
        echo "Page non trouvée";
        break;
}
