<?php
session_start();

function logout()
{
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
    session_destroy();
    header('Location: index.php');
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    logout();
}

function headerComponent()
{
    $isLoggedIn = isset($_SESSION['user_id']);
    $username = $_SESSION['username'] ?? '';

    $header = '<header style="padding: 10px; background: #f0f0f0;">';
    $header .= '<button onclick="location.href=\'index.php\'">Go Home</button> ';

    if ($isLoggedIn) {
        $header .= '<button onclick="location.href=\'sell.php\'">Vendre un objet</button> ';
        $header .= '<button onclick="location.href=\'cart.php\'">Voir mon panier</button> ';
        $header .= '<button onclick="location.href=\'profile.php\'">Voir mon Profile</button> ';
        $header .= '<span>Bienvenue, ' . htmlspecialchars($username) . '</span> ';
        $header .= '<button onclick="location.href=\'?action=logout\'">Ce déconecter</button>';
    } else {
        $header .= '<button onclick="location.href=\'login.php\'">Ce conecter</button> ';
        $header .= '<button onclick="location.href=\'register.php\'">S"inscrire</button>';
    }

    $header .= '</header>';
    return $header;
}
?>