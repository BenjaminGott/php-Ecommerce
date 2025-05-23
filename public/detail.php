<?php
include_once '../includes/header.php';
include_once '../includes/db_connect.php';


echo headerComponent();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "Article invalide.";
    exit;
}

$articleId = (int) $_GET['id'];

// Récupérer article + stock
$stmt = $pdo->prepare("
    SELECT A.*, S.quantity AS stock 
    FROM Article A 
    LEFT JOIN Stock S ON S.article_id = A.id 
    WHERE A.id = :id
");
$stmt->execute(['id' => $articleId]);
$article = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$article) {
    echo "Article non trouvé.";
    exit;
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }

    $quantity = max(1, (int)($_POST['quantity'] ?? 1));

    if ($quantity > $article['stock']) {
        $message = "❌ Stock insuffisant. Stock actuel : {$article['stock']}.";
    } else {
        $userId = $_SESSION['user_id'];

 
        $cartCheck = $pdo->prepare("SELECT id, quantity FROM Cart WHERE user_id = :user AND article_id = :article");
        $cartCheck->execute(['user' => $userId, 'article' => $articleId]);
        $existing = $cartCheck->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            
            $newQty = $existing['quantity'] + $quantity;
            $cartUpdate = $pdo->prepare("UPDATE Cart SET quantity = :qty WHERE id = :id");
            $cartUpdate->execute(['qty' => $newQty, 'id' => $existing['id']]);
        } else {
        
            $cartInsert = $pdo->prepare("INSERT INTO Cart (user_id, article_id, quantity) VALUES (:user, :article, :qty)");
            $cartInsert->execute(['user' => $userId, 'article' => $articleId, 'qty' => $quantity]);
        }

        $message = "✅ Article ajouté au panier.";
    }
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
    <p>Stock disponible : <?= $article['stock'] ?? '0' ?></p>

    <?php if ($message): ?>
        <p style="color:green;"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="post">
        <label for="quantity">Quantité :</label>
        <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?= $article['stock'] ?>" required>
        <button type="submit" name="add_to_cart">Ajouter au panier</button>
    </form>

    <p><a href="index.php">⬅ Retour à la liste</a></p>
</body>
</html>
