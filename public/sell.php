<?php
include_once '../includes/header.php';
include_once '../includes/db_connect.php';


echo headerComponent();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$message = "";

// Supprimer un article du panier
if (isset($_POST['remove'])) {
    $removeId = (int)$_POST['remove'];
    $pdo->prepare("DELETE FROM Cart WHERE user_id = :user AND article_id = :article")
        ->execute(['user' => $userId, 'article' => $removeId]);
}

// Modifier quantités dans le panier
if (isset($_POST['update']) && isset($_POST['quantities'])) {
    foreach ($_POST['quantities'] as $articleId => $qty) {
        $qty = max(1, (int)$qty);

        // Vérifie le stock disponible
        $stockStmt = $pdo->prepare("SELECT quantity FROM Stock WHERE article_id = :article");
        $stockStmt->execute(['article' => $articleId]);
        $stockQty = $stockStmt->fetchColumn();

        if ($qty <= $stockQty) {
            $pdo->prepare("UPDATE Cart SET quantity = :qty WHERE user_id = :user AND article_id = :article")
                ->execute(['qty' => $qty, 'user' => $userId, 'article' => $articleId]);
        } else {
            $message .= "❌ Quantité demandée pour l'article ID $articleId dépasse le stock disponible ($stockQty).<br>";
        }
    }
}

// Passer commande
if (isset($_POST['checkout'])) {
    // Récupérer panier avec prix et quantité
    $stmt = $pdo->prepare("
        SELECT A.id, A.price, A.name, C.quantity, S.quantity AS stock
        FROM Cart C
        JOIN Article A ON A.id = C.article_id
        LEFT JOIN Stock S ON S.article_id = A.id
        WHERE C.user_id = :user
    ");
    $stmt->execute(['user' => $userId]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $total = 0;
    $stockOk = true;

    // Vérification stock et total
    foreach ($items as $item) {
        if ($item['quantity'] > $item['stock']) {
            $stockOk = false;
            $message .= "❌ Stock insuffisant pour l'article " . htmlspecialchars($item['name']) . ". Disponible: {$item['stock']}, demandé: {$item['quantity']}.<br>";
        }
        $total += $item['price'] * $item['quantity'];
    }

    // Vérification solde utilisateur
    $balanceStmt = $pdo->prepare("SELECT balance FROM User WHERE id = :id");
    $balanceStmt->execute(['id' => $userId]);
    $balance = $balanceStmt->fetchColumn();

    if ($stockOk) {
        if ($balance >= $total) {
            // Déduire le solde
            $pdo->prepare("UPDATE User SET balance = balance - :total WHERE id = :id")
                ->execute(['total' => $total, 'id' => $userId]);

            // Déduire le stock dans la table Stock
            foreach ($items as $item) {
                $pdo->prepare("UPDATE Stock SET quantity = quantity - :qty WHERE article_id = :article")
                    ->execute(['qty' => $item['quantity'], 'article' => $item['id']]);
            }

            // Créer la facture (adresse facturation statique pour l'exemple)
            $pdo->prepare("
                INSERT INTO Invoice (user_id, transaction_date, amount, billing_address, billing_city, billing_postal_code)
                VALUES (:user, NOW(), :amount, 'Adresse inconnue', 'Ville', '00000')
            ")->execute([
                'user' => $userId,
                'amount' => $total
            ]);

            // Vider le panier
            $pdo->prepare("DELETE FROM Cart WHERE user_id = :id")->execute(['id' => $userId]);

            $message = "✅ Commande effectuée avec succès.";
        } else {
            $message .= "❌ Solde insuffisant.";
        }
    }
}

// Charger le panier
$stmt = $pdo->prepare("
    SELECT A.id, A.name, A.price, A.image_url, C.quantity, S.quantity AS stock
    FROM Cart C
    JOIN Article A ON A.id = C.article_id
    LEFT JOIN Stock S ON S.article_id = A.id
    WHERE C.user_id = :user
");
$stmt->execute(['user' => $userId]);
$cart = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon panier</title>
</head>
<body>
    <h1>🛒 Mon panier</h1>

    <?php if ($message): ?>
        <p style="color:green;"><?= $message ?></p>
    <?php endif; ?>

    <?php if (empty($cart)): ?>
        <p>Votre panier est vide.</p>
    <?php else: ?>
        <form method="post">
            <table border="1" cellpadding="10">
                <tr>
                    <th>Image</th>
                    <th>Nom</th>
                    <th>Prix</th>
                    <th>Quantité</th>
                    <th>Stock</th>
                    <th>Total</th>
                    <th>Action</th>
                </tr>
                <?php $grandTotal = 0; ?>
                <?php foreach ($cart as $item): 
                    $total = $item['price'] * $item['quantity'];
                    $grandTotal += $total;
                ?>
                    <tr>
                        <td><?php if ($item['image_url']):
