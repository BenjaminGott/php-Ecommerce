<?php

include_once '../includes/db_connect.php';
include_once '../includes/header.php';

echo headerComponent();


if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}



$categories = [
    'Jeux vidéo',
    'Vêtements',
    'Téléphones / Smartphones',
    'Ordinateurs / Tablettes',
    'Meubles',
    'Électroménager',
    'Livres',
    'Instruments de musique',
    'Accessoires de mode',
    'Voitures',
    'Motos / Scooters',
    'Articles de sport',
    'Bijoux',
    'Produits de beauté / cosmétiques',
    'Jouets pour enfants',
    'Outils de bricolage',
    'Décoration intérieure',
    'Appareils photo / caméras',
    'Articles pour animaux',
    'Matériel de jardinage'
];

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $desc = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? '';
    $category = $_POST['categorie'] ?? '';
    $image = $_POST['image_url'] ?? '';
    $quantity = $_POST['stock'] ?? '';

    if ($name && $desc && $price !== '' && $category && $quantity !== '') {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO Article (name, description, price, published_at, author_id, image_url, categorie)
                VALUES (:name, :description, :price, NOW(), :author, :image, :category)");
            $stmt->execute([
                'name' => $name,
                'description' => $desc,
                'price' => $price,
                'author' => $_SESSION['user_id'],
                'image' => $image,
                'category' => $category
            ]);

            $articleId = $pdo->lastInsertId();

            $stockStmt = $pdo->prepare("INSERT INTO Stock (article_id, quantity) VALUES (:id, :qte)");
            $stockStmt->execute([
                'id' => $articleId,
                'qte' => $quantity
            ]);

            $pdo->commit();
            $message = "✅ Article ajouté avec succès !";
        } catch (Exception $e) {
            $pdo->rollBack();
            $message = "❌ Erreur lors de l’ajout : " . $e->getMessage();
        }
    } else {
        $message = "❌ Tous les champs sont requis.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Vendre un article</title>
</head>

<body>
    <h1>Mettre en vente un article</h1>

    <?php if ($message): ?>
        <p><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="post">
        <label>Nom de l’article :<br>
            <input type="text" name="name" required>
        </label><br><br>

        <label>Description :<br>
            <textarea name="description" required></textarea>
        </label><br><br>

        <label>Prix (€) :<br>
            <input type="number" step="0.01" name="price" required>
        </label><br><br>

        <label>Catégorie :<br>
            <select name="categorie" required>
                <option value="">-- Choisir une catégorie --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
                <?php endforeach; ?>
            </select>
        </label><br><br>

        <label>Quantité en stock :<br>
            <input type="number" name="stock" required min="0">
        </label><br><br>

        <label>URL de l’image :<br>
            <input type="text" name="image_url">
        </label><br><br>

        <button type="submit">Mettre en vente</button>
    </form>
</body>

</html>