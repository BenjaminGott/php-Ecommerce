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

    $header = '<header style="
        background-color: #2980b9;
        padding: 15px 20px;
        display: flex;
        align-items: center;
        gap: 10px;
        color: white;
        font-family: Arial, sans-serif;
        flex-wrap: wrap;
        justify-content: center;
    ">';

    $header .= '<style>
        header button {
            background-color: #3498db;
            border: none;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: background-color 0.3s ease;
            font-family: inherit;
        }
        header button:hover {
            background-color: #1f6391;
        }
        header span {
            font-weight: 600;
            margin-left: 15px;
            white-space: nowrap;
        }
    </style>';

    $header .= '<button onclick="location.href=\'index.php\'">Go Home</button> ';

    if ($isLoggedIn) {
        $header .= '<button onclick="location.href=\'sell.php\'">Vendre un objet</button> ';
        $header .= '<button onclick="location.href=\'cart.php\'">Voir mon panier</button> ';
        $header .= '<button onclick="location.href=\'profile.php\'">Voir mon profil</button> ';
        $header .= '<span>Bienvenue, ' . htmlspecialchars($username) . '</span> ';
        $header .= '<button onclick="location.href=\'?action=logout\'">Se déconnecter</button>';
    } else {
        $header .= '<button onclick="location.href=\'login.php\'">Se connecter</button> ';
        $header .= '<button onclick="location.href=\'register.php\'">S\'inscrire</button>';
    }

    $header .= '</header>';
    return $header;
}

?>