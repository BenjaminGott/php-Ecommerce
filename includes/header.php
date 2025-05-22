<?php
session_start();

function logout() {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    header('Location: index.php');
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    logout();
}

function headerComponent() {
    $isLoggedIn = isset($_SESSION['user_id']);
    $username = $_SESSION['username'] ?? '';

    $header = '<header style="padding: 10px; background: #f0f0f0;">';
    $header .= '<button onclick="location.href=\'index.php\'">Go Home</button> ';

    if ($isLoggedIn) {
        $header .= '<button onclick="location.href=\'add.php\'">Go Add</button> ';
        $header .= '<button onclick="location.href=\'pagnier.php\'">Go Pagnier</button> ';
        $header .= '<button onclick="location.href=\'profile.php\'">Go Profile</button> ';
        $header .= '<span>Bienvenue, ' . htmlspecialchars($username) . '</span> ';
        // Le bouton logout redirige vers la même page avec ?action=logout
        $header .= '<button onclick="location.href=\'?action=logout\'">Logout</button>';
    } else {
        $header .= '<button onclick="location.href=\'login.php\'">Go Login</button> ';
        $header .= '<button onclick="location.href=\'register.php\'">Go Register</button>';
    }

    $header .= '</header>';
    return $header;
}
?>
