<?php
$mdp = getenv('PHPMYADMIN_MDP') ;
$mysqli = new mysqli("localhost", "root", "Root" , "php-Ecommerce");

if ($mysqli->connect_errno) {
    die("Erreur de connexion : " . $mysqli->connect_error);
}
?>