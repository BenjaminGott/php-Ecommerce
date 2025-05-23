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

// Gestion mise à jour quantité
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_quantity'], $_POST['cart_id'], $_POST['quantity'])) {
        $cartId = (int) $_POST['cart_id'];
        $quantity = max(1, (int) $_POST['quantity']);

        $stmt = $pdo->prepare("UPDATE Cart SET quantity = :qty WHERE id = :id AND user_id = :user");
        $stmt->execute(['qty' => $quantity, 'id' => $cartId, 'user' => $userId]);

        $message = "Quantité mise à jour.";
    }

    // Suppression article du panier
    if (isset($_POST['delete_item'], $_POST['cart_id'])) {
        $cartId = (int) $_POST['cart_id'];

        $stmt = $pdo->prepare("DELETE FROM Cart WHERE id = :id AND user_id = :user");
        $stmt->execute(['id' => $cartId, 'user' => $userId]);

        $message = "Article supprimé du panier.";
    }
}

// Récupérer les articles dans le panier
$stmt = $pdo->prepare("
    SELECT C.id AS cart_id, A.id AS article_id, A.name, A.price, C.quantity
    FROM Cart C
    JOIN Article A ON A.id = C.article_id
    WHERE C.user_id = :user
");
$stmt->execute(['user' => $userId]);
$cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calcul du total panier
$grandTotal = 0;
foreach ($cartItems as $item) {
    $grandTotal += $item['price'] * $item['quantity'];
}

// Récupérer solde utilisateur
$balanceStmt = $pdo->prepare("SELECT balance FROM User WHERE id = :id");
$balanceStmt->execute(['id' => $userId]);
$userBalance = $balanceStmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Mon panier</title>
</head>

<body>
    <h1>Mon panier</h1>

    <?php if ($message): ?>
        <p style="color:green;"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <?php if (empty($cartItems)): ?>
        <p>Votre panier est vide.</p>
    <?php else: ?>
        <table border="1" cellpadding="5" cellspacing="0">
            <thead>
                <tr>
                    <th>Article</th>
                    <th>Prix unitaire (€)</th>
                    <th>Quantité</th>
                    <th>Total (€)</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cartItems as $item):
                    $totalPrice = $item['price'] * $item['quantity'];
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($item['name']) ?></td>
                        <td><?= number_format($item['price'], 2) ?></td>
                        <td>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="cart_id" value="<?= $item['cart_id'] ?>">
                                <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="1" required
                                    style="width:60px;">
                                <button type="submit" name="update_quantity">Modifier</button>
                            </form>
                        </td>
                        <td><?= number_format($totalPrice, 2) ?></td>
                        <td>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="cart_id" value="<?= $item['cart_id'] ?>">
                                <button type="submit" name="delete_item"
                                    onclick="return confirm('Supprimer cet article du panier ?')">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <tr>
                    <td colspan="3" style="text-align:right;"><strong>Total général :</strong></td>
                    <td colspan="2"><strong><?= number_format($grandTotal, 2) ?> €</strong></td>
                </tr>
            </tbody>
        </table>

        <br>

        <?php if ($userBalance < $grandTotal): ?>
            <p style="color:red;">Solde insuffisant pour passer la commande. Votre solde est de
                <?= number_format($userBalance, 2) ?> €, total panier <?= number_format($grandTotal, 2) ?> €.
            </p>
        <?php else: ?>
            <form method="post" action="validate.php">
                <button type="submit">Valider la commande</button>
            </form>
        <?php endif; ?>
    <?php endif; ?>

</body>

</html>