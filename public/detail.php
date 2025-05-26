<?php
include_once '../includes/header.php';
include_once '../includes/db_connect.php';

session_start();

echo headerComponent();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "Article invalide.";
    exit;
}

$articleId = (int) $_GET['id'];
$userId = $_SESSION['user_id'] ?? null;

$stmt = $pdo->prepare("
    SELECT A.*, S.quantity AS stock, U.username AS seller_name, U.id AS seller_id 
    FROM Article A 
    LEFT JOIN Stock S ON S.article_id = A.id 
    JOIN User U ON A.author_id = U.id
    WHERE A.id = :id
");
$stmt->execute(['id' => $articleId]);
$article = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$article) {
    echo "Article non trouvé.";
    exit;
}

$isOwner = ($userId && $article['author_id'] == $userId);

$isAdmin = false;
if ($userId) {
    $adminCheck = $pdo->prepare("SELECT role FROM User WHERE id = :id");
    $adminCheck->execute(['id' => $userId]);
    $isAdmin = $adminCheck->fetchColumn() === 'admin';
}


$hasBought = false;
if ($userId) {
    $checkPurchase = $pdo->prepare("SELECT COUNT(*) FROM History WHERE user_id = :uid AND article_id = :aid");
    $checkPurchase->execute(['uid' => $userId, 'aid' => $articleId]);
    $hasBought = $checkPurchase->fetchColumn() > 0;
}

$isFavorite = false;
if ($userId) {
    $favCheck = $pdo->prepare("SELECT COUNT(*) FROM Favorite WHERE user_id = :uid AND article_id = :aid");
    $favCheck->execute(['uid' => $userId, 'aid' => $articleId]);
    $isFavorite = $favCheck->fetchColumn() > 0;
}

$message = "";


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_article']) && ($isOwner || $isAdmin)) {
    $pdo->prepare("DELETE FROM History WHERE article_id = :id")->execute(['id' => $articleId]);
    $pdo->prepare("DELETE FROM Favorite WHERE article_id = :id")->execute(['id' => $articleId]);
    $pdo->prepare("DELETE FROM Cart WHERE article_id = :id")->execute(['id' => $articleId]);
    $pdo->prepare("DELETE FROM Stock WHERE article_id = :id")->execute(['id' => $articleId]);

    $del = $pdo->prepare("DELETE FROM Article WHERE id = :id");
    $del->execute(['id' => $articleId]);

    header("Location: index.php");
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_favorite'])) {
    if (!$userId) {
        header('Location: login.php');
        exit;
    }
    if ($isFavorite) {
        $pdo->prepare("DELETE FROM Favorite WHERE user_id = :uid AND article_id = :aid")
            ->execute(['uid' => $userId, 'aid' => $articleId]);
        $message = "⭐ Article retiré des favoris.";
        $isFavorite = false;
    } else {
        $pdo->prepare("INSERT INTO Favorite (user_id, article_id, created_at) VALUES (:uid, :aid, NOW())")
            ->execute(['uid' => $userId, 'aid' => $articleId]);
        $message = "✅ Article ajouté aux favoris.";
        $isFavorite = true;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($isOwner || $isAdmin) && isset($_POST['edit_article'])) {
    $name = trim($_POST['name']);
    $desc = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $categorie = trim($_POST['categorie']);
    $stock = (int) $_POST['stock'];
    $image = trim($_POST['image_url']);

    if ($name && $price >= 0 && $categorie) {
        $pdo->prepare("
            UPDATE Article 
            SET name = :n, description = :d, price = :p, categorie = :c, image_url = :img
            WHERE id = :id
        ")->execute([
                    'n' => $name,
                    'd' => $desc,
                    'p' => $price,
                    'c' => $categorie,
                    'img' => $image,
                    'id' => $articleId
                ]);

        $pdo->prepare("UPDATE Stock SET quantity = :q WHERE article_id = :id")
            ->execute(['q' => $stock, 'id' => $articleId]);

        $message = "✅ Modifications enregistrées.";
        header("Location: detail.php?id=$articleId");
        exit;
    } else {
        $message = "❌ Remplir tous les champs correctement.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (!$userId) {
        header('Location: login.php');
        exit;
    }

    $quantity = max(1, (int) ($_POST['quantity'] ?? 1));

    if ($quantity > $article['stock']) {
        $message = "❌ Stock insuffisant.";
    } else {
        $cartCheck = $pdo->prepare("SELECT id, quantity FROM Cart WHERE user_id = :user AND article_id = :article");
        $cartCheck->execute(['user' => $userId, 'article' => $articleId]);
        $existing = $cartCheck->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            $pdo->prepare("UPDATE Cart SET quantity = :qty WHERE id = :id")
                ->execute(['qty' => $existing['quantity'] + $quantity, 'id' => $existing['id']]);
        } else {
            $pdo->prepare("INSERT INTO Cart (user_id, article_id, quantity) VALUES (:user, :article, :qty)")
                ->execute(['user' => $userId, 'article' => $articleId, 'qty' => $quantity]);
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
    <style>
        body {
            max-width: 800px;
            margin: auto;
            font-family: sans-serif;
        }

        form {
            margin-top: 20px;
        }

        input,
        textarea {
            width: 100%;
            margin: 5px 0;
            padding: 8px;
        }

        label {
            font-weight: bold;
        }

        button {
            padding: 10px 20px;
            margin-top: 10px;
            cursor: pointer;
        }

        .message {
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
        }

        .message.success {
            background: #d4edda;
            color: #155724;
        }

        .message.error {
            background: #f8d7da;
            color: #721c24;
        }

        img {
            max-width: 300px;
            margin: 10px 0;
        }

        /* Styles étoile favoris */
        .favorite-btn {
            background: none;
            border: none;
            font-size: 28px;
            color: #f39c12;
            padding: 0;
            margin-bottom: 15px;
        }

        .favorite-btn:hover {
            color: #d35400;
        }

        .delete-btn {
            background-color: #e74c3c;
            color: white;
            border: none;
            padding: 10px 20px;
            margin-top: 20px;
            cursor: pointer;
            border-radius: 4px;
        }

        .delete-btn:hover {
            background-color: #c0392b;
        }
    </style>
</head>

<body>

    <h1><?= htmlspecialchars($article['name']) ?></h1>

    <?php if (!empty($article['image_url'])): ?>
        <img src="<?= htmlspecialchars($article['image_url']) ?>" alt="Image produit">
    <?php endif; ?>

    <p>Vendu par : <a
            href="profile.php?id=<?= $article['seller_id'] ?>"><?= htmlspecialchars($article['seller_name']) ?></a></p>

    <?php if ($message): ?>
        <div class="message <?= str_starts_with($message, '✅') || str_starts_with($message, '⭐') ? 'success' : 'error' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <?php if ($isOwner || $isAdmin): ?>
        <form method="post">
            <input type="hidden" name="edit_article" value="1">
            <label>Nom</label>
            <input type="text" name="name" value="<?= htmlspecialchars($article['name']) ?>" required>

            <label>Description</label>
            <textarea name="description" rows="4"><?= htmlspecialchars($article['description']) ?></textarea>

            <label>Prix (€)</label>
            <input type="number" name="price" step="0.01" min="0" value="<?= $article['price'] ?>" required>

            <label>Catégorie</label>
            <input type="text" name="categorie" value="<?= htmlspecialchars($article['categorie']) ?>" required>

            <label>Stock</label>
            <input type="number" name="stock" min="0" value="<?= $article['stock'] ?>" required>

            <label>Image URL</label>
            <input type="text" name="image_url" value="<?= htmlspecialchars($article['image_url']) ?>">

            <button type="submit">Enregistrer</button>
        </form>

        <form method="post" onsubmit="return confirm('Voulez-vous vraiment supprimer cet article ?');"
            style="margin-top:10px;">
            <button type="submit" name="delete_article" class="delete-btn">Supprimer l'article</button>
        </form>
    <?php else: ?>
        <?php if ($userId): ?>
            <form method="post" style="display:inline;">
                <button class="favorite-btn" type="submit" name="toggle_favorite" aria-label="Toggle favori">
                    <?= $isFavorite ? '★' : '☆' ?>
                </button>
            </form>
        <?php endif; ?>

        <p><?= nl2br(htmlspecialchars($article['description'])) ?></p>
        <p>Prix : <?= number_format($article['price'], 2) ?> €</p>
        <p>Catégorie : <?= htmlspecialchars($article['categorie']) ?></p>
        <p>Publié le : <?= $article['published_at'] ?></p>
        <p>Stock disponible : <?= $article['stock'] ?? '0' ?></p>

        <?php if ($article['stock'] > 0): ?>
            <form method="post">
                <label for="quantity">Quantité :</label>
                <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?= $article['stock'] ?>" required>
                <button type="submit" name="add_to_cart">Ajouter au panier</button>
            </form>
        <?php else: ?>
            <p><strong>Article en rupture de stock.</strong></p>
        <?php endif; ?>
    <?php endif; ?>

    <p><a href="index.php">⬅ Retour</a></p>

</body>

</html>