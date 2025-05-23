<?php
include_once '../includes/header.php';
include_once '../includes/db_connect.php';

echo headerComponent();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT role FROM User WHERE id = :id");
$stmt->execute(['id' => $userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || $user['role'] !== 'administrateur') {
    http_response_code(403);
    echo "Accès refusé : Vous n'êtes pas administrateur.";
    exit;
}

$message = "";

if (isset($_POST['delete_user'])) {
    $userId = (int) $_POST['user_id'];
    try {
        $pdo->prepare("DELETE FROM Article WHERE author_id = :id")->execute(['id' => $userId]);
        $pdo->prepare("DELETE FROM User WHERE id = :id")->execute(['id' => $userId]);
        $message = "Utilisateur $userId supprimé avec succès.";
    } catch (PDOException $e) {
        $message = "Erreur lors de la suppression de l'utilisateur : " . $e->getMessage();
    }
}

if (isset($_POST['modify_user'])) {
    $userId = (int) $_POST['user_id'];
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $balance = floatval($_POST['balance']);
    $role = trim($_POST['role']);

    if ($username === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $balance < 0 || $role === '') {
        $message = "Données utilisateur invalides.";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE User SET username = :username, email = :email, balance = :balance, role = :role WHERE id = :id");
            $stmt->execute([
                'username' => $username,
                'email' => $email,
                'balance' => $balance,
                'role' => $role,
                'id' => $userId
            ]);
            $message = "Utilisateur $userId modifié avec succès.";
        } catch (PDOException $e) {
            $message = "Erreur lors de la modification de l'utilisateur : " . $e->getMessage();
        }
    }
}

if (isset($_POST['delete_article'])) {
    $articleId = (int) $_POST['article_id'];
    try {
        $pdo->prepare("DELETE FROM cart WHERE article_id = :id")->execute(['id' => $articleId]);

        $pdo->prepare("DELETE FROM Article WHERE id = :id")->execute(['id' => $articleId]);
        $message = "Article $articleId supprimé avec succès.";
    } catch (PDOException $e) {
        $message = "Erreur lors de la suppression de l'article : " . $e->getMessage();
    }
}

if (isset($_POST['modify_article'])) {
    $articleId = (int) $_POST['article_id'];
    $name = trim($_POST['name']);
    $price = floatval($_POST['price']);
    $published_at = $_POST['published_at'];
    $author_id = (int) $_POST['author_id'];

    if ($name === '' || $price < 0 || $published_at === '') {
        $message = "Données article invalides.";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE Article SET name = :name, price = :price, published_at = :published_at, author_id = :author_id WHERE id = :id");
            $stmt->execute([
                'name' => $name,
                'price' => $price,
                'published_at' => $published_at,
                'author_id' => $author_id,
                'id' => $articleId
            ]);
            $message = "Article $articleId modifié avec succès.";
        } catch (PDOException $e) {
            $message = "Erreur lors de la modification de l'article : " . $e->getMessage();
        }
    }
}

$users = $pdo->query("SELECT id, username, email, balance, role FROM User ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

$articles = $pdo->query("SELECT id, name, price, published_at, author_id FROM Article ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Admin - Panneau de gestion</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: auto;
            padding: 20px;
        }

        h1 {
            margin-bottom: 20px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 40px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f0f0f0;
        }

        form {
            margin: 0;
        }

        input[type="text"],
        input[type="email"],
        input[type="number"],
        input[type="date"],
        select {
            width: 100%;
            box-sizing: border-box;
            padding: 5px;
        }

        button {
            padding: 6px 12px;
            margin: 2px;
            cursor: pointer;
        }

        .message {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>

<body>

    <h1>Tableau Administrateur</h1>

    <?php if ($message): ?>
        <div class="message <?= strpos($message, 'succès') !== false ? 'success' : 'error' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <h2>Utilisateurs</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom d'utilisateur</th>
                <th>Email</th>
                <th>Solde (€)</th>
                <th>Rôle</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <form method="post">
                        <td><?= $user['id'] ?><input type="hidden" name="user_id" value="<?= $user['id'] ?>"></td>
                        <td><input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
                        </td>
                        <td><input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required></td>
                        <td><input type="number" name="balance" step="0.01" min="0"
                                value="<?= number_format($user['balance'], 2, '.', '') ?>" required></td>
                        <td>
                            <select name="role" required>
                                <option value="utilisateur" <?= $user['role'] === 'utilisateur' ? 'selected' : '' ?>>
                                    Utilisateur</option>
                                <option value="administrateur" <?= $user['role'] === 'administrateur' ? 'selected' : '' ?>>
                                    Administrateur</option>
                            </select>
                        </td>
                        <td>
                            <button type="submit" name="modify_user">Modifier</button>
                            <?php if ($_SESSION['user_id'] !== $user['id']): // empêcher auto-suppression ?>
                                <button type="submit" name="delete_user"
                                    onclick="return confirm('Supprimer cet utilisateur ?')">Supprimer</button>
                            <?php endif; ?>
                        </td>
                    </form>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Articles</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Prix (€)</th>
                <th>Date de publication</th>
                <th>ID Auteur</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($articles as $article): ?>
                <tr>
                    <form method="post">
                        <td><?= $article['id'] ?><input type="hidden" name="article_id" value="<?= $article['id'] ?>"></td>
                        <td><input type="text" name="name" value="<?= htmlspecialchars($article['name']) ?>" required></td>
                        <td><input type="number" step="0.01" min="0" name="price"
                                value="<?= number_format($article['price'], 2, '.', '') ?>" required></td>
                        <td><input type="date" name="published_at" value="<?= substr($article['published_at'], 0, 10) ?>"
                                required></td>
                        <td><input type="number" min="1" name="author_id" value="<?= $article['author_id'] ?>" required>
                        </td>
                        <td>
                            <button type="submit" name="modify_article">Modifier</button>
                            <button type="submit" name="delete_article"
                                onclick="return confirm('Supprimer cet article ?')">Supprimer</button>
                        </td>
                    </form>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</body>

</html>