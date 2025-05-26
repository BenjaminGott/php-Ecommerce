<?php
include_once '../includes/header.php';
include_once '../includes/db_connect.php';

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

// SOUMISSION D'UN AVIS
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review']) && $hasBought) {
    $rating = (int) $_POST['rating'];
    $comment = trim($_POST['comment']);

    if ($rating >= 1 && $rating <= 5 && $comment) {
        $existingReview = $pdo->prepare("SELECT id FROM Review WHERE user_id = :uid AND article_id = :aid");
        $existingReview->execute(['uid' => $userId, 'aid' => $articleId]);

        if ($existingReview->fetch()) {
            // Update existing review
            $pdo->prepare("UPDATE Review SET rating = :r, comment = :c, created_at = NOW() WHERE user_id = :uid AND article_id = :aid")
                ->execute(['r' => $rating, 'c' => $comment, 'uid' => $userId, 'aid' => $articleId]);
        } else {
            // Insert new review
            $pdo->prepare("INSERT INTO Review (user_id, article_id, rating, comment, created_at) VALUES (:uid, :aid, :r, :c, NOW())")
                ->execute(['uid' => $userId, 'aid' => $articleId, 'r' => $rating, 'c' => $comment]);
        }

        $message = "✅ Merci pour votre avis !";
        header("Location: detail.php?id=$articleId");
        exit;
    } else {
        $message = "❌ Note ou commentaire invalide.";
    }
}

// Calcul de la note moyenne
$avgRatingStmt = $pdo->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as count_reviews FROM Review WHERE article_id = :aid");
$avgRatingStmt->execute(['aid' => $articleId]);
$ratingData = $avgRatingStmt->fetch(PDO::FETCH_ASSOC);
$avgRating = $ratingData['avg_rating'] ? number_format($ratingData['avg_rating'], 2) : null;
$countReviews = $ratingData['count_reviews'];

?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Détails de l'article</title>
    <link rel="stylesheet" href="../styles/detail.css">

</head>

<body>

    <h1><?= htmlspecialchars($article['name']) ?></h1>

    <?php if (!empty($article['image_url'])): ?>
        <img src="<?= htmlspecialchars($article['image_url']) ?>" alt="Image produit">
    <?php endif; ?>

    <p>Vendu par : <a
            href="profile.php?id=<?= $article['seller_id'] ?>"><?= htmlspecialchars($article['seller_name']) ?></a></p>

    <?php if ($avgRating !== null): ?>
        <p class="avg-rating">Note moyenne : <?= $avgRating ?>/5 (<?= $countReviews ?> avis)</p>
    <?php else: ?>
        <p class="avg-rating">Aucun avis pour cet article.</p>
    <?php endif; ?>

    <?php if ($message): ?>
        <div class="message <?= str_starts_with($message, '✅') ? 'success' : 'error' ?>">
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

        <?php if ($hasBought): ?>
            <h2>Laisser un avis</h2>
            <form method="post">
                <label>Note (1 à 5)</label>
                <input type="number" name="rating" min="1" max="5" required>

                <label>Commentaire</label>
                <textarea name="comment" rows="4" required></textarea>

                <button type="submit" name="submit_review">Envoyer l'avis</button>
            </form>
        <?php endif; ?>
    <?php endif; ?>

    <h2>Avis des utilisateurs</h2>
    <?php
    $reviews = $pdo->prepare("
        SELECT R.rating, R.comment, R.created_at, U.username 
        FROM Review R 
        JOIN User U ON R.user_id = U.id 
        WHERE R.article_id = :aid 
        ORDER BY R.created_at DESC
    ");
    $reviews->execute(['aid' => $articleId]);
    $allReviews = $reviews->fetchAll(PDO::FETCH_ASSOC);

    if ($allReviews):
        foreach ($allReviews as $rev): ?>
            <div class="review">
                <strong><?= htmlspecialchars($rev['username']) ?></strong> - Note : <?= $rev['rating'] ?>/5<br>
                <em><?= nl2br(htmlspecialchars($rev['comment'])) ?></em><br>
                <small>le <?= $rev['created_at'] ?></small>
            </div>
        <?php endforeach;
    else: ?>
        <p>Aucun avis pour cet article.</p>
    <?php endif; ?>

    <p><a href="index.php">⬅ Retour</a></p>

</body>

</html>