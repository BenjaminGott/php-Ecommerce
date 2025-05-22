<?php
session_start();
include_once '../includes/header.php';
include_once '../includes/db_connect.php';

echo headerComponent();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "Article invalide.";
    exit;
}

$articleId = (int) $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM Article WHERE id = :id");
$stmt->execute(['id' => $articleId]);
$article = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$article) {
    echo "Article non trouvé.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (!isset($_SESSION['user_id'])) {
        header('Location: register.php');
        exit;
    }

    $quantity = max(1, (int)($_POST['quantity'] ?? 1));

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    if (isset($_SESSION['cart'][$articleId])) {
        $_SESSION['cart'][$articleId] += $quantity;
    } else {
        $_SESSION['cart'][$articleId] = $quantity;
    }

    $message = "Article ajouté au panier !";
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détails de l'article</title>
</head>
<body>
    <h1><?= htmlspecialchars($article['name']) ?></h1>
    <?php if (!empty($article['image_url'])): ?>
        <img src="<?= htmlspecialchars($article['image_url']) ?>" alt="Image" width="300">
    <?php endif; ?>
    <p><?= nl2br(htmlspecialchars($article['description'])) ?></p>
    <p>Prix : <?= number_format($article['price'], 2) ?> €</p>
    <p>Catégorie : <?= htmlspecialchars($article['categorie']) ?></p>
    <p>Publié le : <?= $article['published_at'] ?></p>

    <?php if (isset($message)): ?>
        <p style="color:green;"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="post">
        <label for="quantity">Quantité :</label>
        <input type="number" id="quantity" name="quantity" value="1" min="1" required>
        <button type="submit" name="add_to_cart">Ajouter au panier</button>
    </form>

    <p><a href="index.php">Retour à la liste</a></p>
</body>
</html>