<?php
include_once '../includes/db_connect.php';
include_once '../includes/header.php';

echo headerComponent();

if (!isset($_SESSION['user'])) {
    // non connecté
    header('Location: login.php');
    exit;
}

if ($_SESSION['user']['role'] !== 'admin') {
    // connecté mais pas admin
    http_response_code(403);
    echo "❌ Accès refusé : administrateur uniquement.";
    exit;
}

// ici : tu peux afficher la page admin
echo "<h1>Bienvenue sur la page admin</h1>";
