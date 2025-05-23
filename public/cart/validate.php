<?php
include_once '../includes/header.php';
include_once '../includes/db_connect.php';
session_start();

echo headerComponent();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$message = "";

// Récupérer panier
$stmt = $pdo->prepare("
    SELECT A.id, A.name, A.price, C.quantity, S.quantity AS stock
    FROM Cart C
    JOIN Article A ON A.id = C.article_id
    LEFT JOIN Stock S ON S.article_id = A.id
    WHERE C.user_id = :user
");
$stmt->execute(['user' => $userId]);
$cart = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($cart)) {
    echo "<p>Votre panier est vide.</p>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_order'])) {
    // Récupérer infos facturation
    $billing_address = trim($_POST['billing_address'] ?? '');
    $billing_city = trim($_POST['billing_city'] ?? '');
    $billing_postal_code = trim($_POST['billing_postal_code'] ?? '');
    $comment = trim($_POST['comment'] ?? '');

    if (empty($billing_address) || empty($billing_city) || empty($billing_postal_code)) {
        $message = "Veuillez remplir toutes les informations de facturation.";
    } else {
        // Calcul total & vérification stock
        $total = 0;
        $stockOk = true;

        foreach ($cart as $item) {
            if ($item['quantity'] > $item['stock']) {
                $stockOk = false;
                $message .= "Stock insuffisant pour " . htmlspecialchars($item['name']) . ".<br>";
            }
            $total += $item['price'] * $item['quantity'];
        }

        // Vérifier solde
        $balanceStmt = $pdo->prepare("SELECT balance FROM User WHERE id = :id");
        $balanceStmt->execute(['id' => $userId]);
        $balance = $balanceStmt->fetchColumn();

        if (!$stockOk) {
            $message .= "Veuillez réduire les quantités dans votre panier.<br>";
        } elseif ($balance < $total) {
            $message .= "Solde insuffisant.<br>";
        } else {
            // Début transaction
            $pdo->beginTransaction();
            try {
                // Déduire solde utilisateur
                $pdo->prepare("UPDATE User SET balance = balance - :total WHERE id = :id")
                    ->execute(['total' => $total, 'id' => $userId]);

                // Mettre à jour stock et insérer historique
                $historyStmt = $pdo->prepare("INSERT INTO History (user_id, article_id, quantity, order_date) VALUES (:user, :article, :qty, NOW())");
                $stockUpdateStmt = $pdo->prepare("UPDATE Stock SET quantity = quantity - :qty WHERE article_id = :article");

                foreach ($cart as $item) {
                    $stockUpdateStmt->execute(['qty' => $item['quantity'], 'article' => $item['id']]);
                    $historyStmt->execute([
                        'user' => $userId,
                        'article' => $item['id'],
                        'qty' => $item['quantity']
                    ]);
                }

                // Créer facture
                $invoiceStmt = $pdo->prepare("
                    INSERT INTO Invoice (user_id, transaction_date, amount, billing_address, billing_city, billing_postal_code)
                    VALUES (:user, NOW(), :amount, :address, :city, :postal)
                ");
                $invoiceStmt->execute([
                    'user' => $userId,
                    'amount' => $total,
                    'address' => $billing_address,
                    'city' => $billing_city,
                    'postal' => $billing_postal_code
                ]);

                // Vider panier
                $pdo->prepare("DELETE FROM Cart WHERE user_id = :id")->execute(['id' => $userId]);

                $pdo->commit();
                $message = "✅ Commande validée avec succès.";
            } catch (Exception $e) {
                $pdo->rollBack();
                $message = "Erreur lors de la validation : " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Confirmation de commande</title>
</head>
<body>
    <h1>Confirmation de commande</h1>

    <?php if ($message): ?>
        <p style="color:<?= strpos($message, '✅') === 0 ? 'green' : 'red' ?>"><?= $message ?></p>
    <?php endif; ?>

    <?php if (strpos($message, '✅') !== 0): ?>
        <h2>Résumé de votre panier :</h2>
        <ul>
            <?php foreach ($cart as $item): ?>
                <li>
                    <?= htmlspecialchars($item['name']) ?> — Quantité : <?= $item['quantity'] ?> — Prix unitaire : <?= number_format($item['price'], 2) ?> €
                </li>
            <?php endforeach; ?>
        </ul>

        <form method="post">
            <h3>Informations de facturation</h3>
            <label for="billing_address">Adresse :</label><br>
            <textarea id="billing_address" name="billing_address" required></textarea><br><br>

            <label for="billing_city">Ville :</label><br>
            <input type="text" id="billing_city" name="billing_city" required><br><br>

            <label for="billing_postal_code">Code postal :</label><br>
            <input type="text" id="billing_postal_code" name="billing_postal_code" required><br><br>

            <label for="comment">Commentaire (optionnel) :</label><br>
            <textarea id="comment" name="comment"></textarea><br><br>

            <button type="submit" name="confirm_order">Valider la commande</button>
        </form>
    <?php else: ?>
        <p><a href="../index.php">Retour à l'accueil</a></p>
    <?php endif; ?>
</body>
</html>
