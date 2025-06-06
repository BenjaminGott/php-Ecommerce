<?php
include_once '../includes/header.php';
include_once '../includes/db_connect.php';

echo headerComponent();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$connectedUserId = $_SESSION['user_id'];
$targetUserId = isset($_GET['id']) ? (int) $_GET['id'] : $connectedUserId;
$isOwnProfile = ($connectedUserId === $targetUserId);

$userStmt = $pdo->prepare("SELECT username, email, balance, profile_picture FROM User WHERE id = :id");
$userStmt->execute(['id' => $targetUserId]);
$user = $userStmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "<p>Utilisateur introuvable.</p>";
    exit;
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isOwnProfile && isset($_POST['update_profile'])) {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $balanceInput = trim($_POST['balance'] ?? '');
    $profilePicture = trim($_POST['profile_picture'] ?? '');

    if ($username === '' || $email === '' || $balanceInput === '') {
        $message = "Veuillez remplir tous les champs.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Email invalide.";
    } elseif (!is_numeric($balanceInput) || $balanceInput < 0) {
        $message = "Le solde doit être un nombre positif.";
    } else {
        $balance = floatval($balanceInput);
        $updateStmt = $pdo->prepare("
            UPDATE User SET username = :username, email = :email, balance = :balance, profile_picture = :profile_picture WHERE id = :id
        ");
        $updateStmt->execute([
            'username' => $username,
            'email' => $email,
            'balance' => $balance,
            'profile_picture' => $profilePicture,
            'id' => $connectedUserId
        ]);
        $message = "Informations mises à jour avec succès.";
        $user['username'] = $username;
        $user['email'] = $email;
        $user['balance'] = $balance;
        $user['profile_picture'] = $profilePicture;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Profil - <?= htmlspecialchars($user['username']) ?></title>
    <link rel="stylesheet" href="../styles/profile.css">
</head>

<body>

    <h1>Profil de <?= htmlspecialchars($user['username']) ?></h1>

    <?php if ($message): ?>
        <div class="message <?= strpos($message, 'succès') !== false ? 'success' : 'error' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <?php if (!$isOwnProfile): ?>
        <h2>Informations publiques</h2>
        <?php if (!empty($user['profile_picture'])): ?>
            <img src="<?= htmlspecialchars($user['profile_picture']) ?>"
                alt="Photo de profil de <?= htmlspecialchars($user['username']) ?>" class="profile-pic">
        <?php endif; ?>
        <ul>
            <li>Nom d’utilisateur : <?= htmlspecialchars($user['username']) ?></li>
            <li>Email : <?= htmlspecialchars($user['email']) ?></li>
        </ul>

        <h2>Objets en vente</h2>
        <?php
        $stmt = $pdo->prepare("SELECT id, name, price, published_at FROM Article WHERE author_id = :id ORDER BY published_at DESC");
        $stmt->execute(['id' => $targetUserId]);
        $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <?php if (empty($articles)): ?>
            <p>Aucun article publié.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Prix (€)</th>
                        <th>Date</th>
                        <th>Détail</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($articles as $article): ?>
                        <tr>
                            <td><?= htmlspecialchars($article['name']) ?></td>
                            <td><?= number_format($article['price'], 2) ?></td>
                            <td><?= $article['published_at'] ?></td>
                            <td><a href="detail.php?id=<?= $article['id'] ?>" class="detail-link">Voir</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

    <?php else: ?>
        <?php
        $invoices = $pdo->prepare("SELECT * FROM Invoice WHERE user_id = :id ORDER BY transaction_date DESC");
        $invoices->execute(['id' => $connectedUserId]);
        $invoices = $invoices->fetchAll(PDO::FETCH_ASSOC);

        $history = $pdo->prepare("
        SELECT H.order_date, A.id AS article_id, A.name, H.quantity, A.price 
        FROM History H 
        JOIN Article A ON A.id = H.article_id 
        WHERE H.user_id = :id ORDER BY H.order_date DESC
    ");
        $history->execute(['id' => $connectedUserId]);
        $history = $history->fetchAll(PDO::FETCH_ASSOC);

        $sales = $pdo->prepare("SELECT id, name, price, published_at FROM Article WHERE author_id = :id ORDER BY published_at DESC");
        $sales->execute(['id' => $connectedUserId]);
        $sales = $sales->fetchAll(PDO::FETCH_ASSOC);
        ?>

        <nav>
            <button class="tab-btn active" data-tab="invoices">Factures</button>
            <button class="tab-btn" data-tab="history">Historique</button>
            <button class="tab-btn" data-tab="sales">Objets</button>
            <button class="tab-btn" data-tab="profile">Informations</button>
        </nav>

        <section id="invoices" class="active">
            <h2>Factures</h2>
            <?php if (empty($invoices)): ?>
                <p>Aucune facture.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Date</th>
                            <th>Montant</th>
                            <th>Adresse</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($invoices as $i): ?>
                            <tr>
                                <td><?= $i['id'] ?></td>
                                <td><?= $i['transaction_date'] ?></td>
                                <td><?= number_format($i['amount'], 2) ?> €</td>
                                <td><?= htmlspecialchars($i['billing_address'] . ', ' . $i['billing_postal_code'] . ' ' . $i['billing_city']) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>

        <section id="history">
            <h2>Historique d'achats</h2>
            <?php if (empty($history)): ?>
                <p>Aucun achat.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Article</th>
                            <th>Qté</th>
                            <th>PU (€)</th>
                            <th>Total (€)</th>
                            <th>Détail</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($history as $h): ?>
                            <tr>
                                <td><?= $h['order_date'] ?></td>
                                <td><?= htmlspecialchars($h['name']) ?></td>
                                <td><?= $h['quantity'] ?></td>
                                <td><?= number_format($h['price'], 2) ?></td>
                                <td><?= number_format($h['price'] * $h['quantity'], 2) ?></td>
                                <td><a href="detail.php?id=<?= $h['article_id'] ?>" class="detail-link">Voir</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>

        <section id="sales">
            <h2>Objets en vente</h2>
            <?php if (empty($sales)): ?>
                <p>Aucun article.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Prix (€)</th>
                            <th>Date</th>
                            <th>Détail</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sales as $s): ?>
                            <tr>
                                <td><?= htmlspecialchars($s['name']) ?></td>
                                <td><?= number_format($s['price'], 2) ?></td>
                                <td><?= $s['published_at'] ?></td>
                                <td><a href="detail.php?id=<?= $s['id'] ?>" class="detail-link">Voir</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>

        <section id="profile">
            <h2>Informations personnelles</h2>
            <?php if (!empty($user['profile_picture'])): ?>
                <img src="<?= htmlspecialchars($user['profile_picture']) ?>" alt="Photo de profil" class="profile-pic">
            <?php endif; ?>
            <form method="post">
                <label for="username">Nom</label>
                <input type="text" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>"
                    required>

                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>

                <label for="balance">Solde (€)</label>
                <input type="number" id="balance" name="balance" min="0" step="0.01"
                    value="<?= number_format($user['balance'], 2, '.', '') ?>" required>

                <label for="profile_picture">URL de la photo de profil</label>
                <input type="url" id="profile_picture" name="profile_picture"
                    value="<?= htmlspecialchars($user['profile_picture']) ?>" placeholder="https://example.com/photo.jpg">

                <button type="submit" name="update_profile" class="submit-btn">Mettre à jour</button>
            </form>
        </section>

        <script>
            const tabs = document.querySelectorAll('.tab-btn');
            const sections = document.querySelectorAll('section');

            tabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    tabs.forEach(t => t.classList.remove('active'));
                    sections.forEach(s => s.classList.remove('active'));

                    tab.classList.add('active');
                    document.getElementById(tab.dataset.tab).classList.add('active');
                });
            });
        </script>
    <?php endif; ?>

</body>

</html>