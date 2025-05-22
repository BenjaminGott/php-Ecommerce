<?php
include_once '../includes/db_connect.php';
include_once '../includes/header.php';

echo headerComponent();

$allowedSorts = ['categorie', 'price', 'published_at'];
$allowedOrders = ['ASC', 'DESC'];
$sort = in_array($_GET['sort'] ?? '', $allowedSorts) ? $_GET['sort'] : 'published_at';
$order = in_array($_GET['order'] ?? '', $allowedOrders) ? $_GET['order'] : 'DESC';
$selectedCategory = $_GET['category'] ?? '';
$search = $_GET['search'] ?? '';

$categories = [
    'Jeux vidéo', 'Vêtements', 'Téléphones / Smartphones', 'Ordinateurs / Tablettes',
    'Meubles', 'Électroménager', 'Livres', 'Instruments de musique',
    'Accessoires de mode', 'Voitures', 'Motos / Scooters', 'Articles de sport',
    'Bijoux', 'Produits de beauté / cosmétiques', 'Jouets pour enfants',
    'Outils de bricolage', 'Décoration intérieure', 'Appareils photo / caméras',
    'Articles pour animaux', 'Matériel de jardinage'
];

// Construction de la requête avec filtres
$sql = "SELECT * FROM Article WHERE 1";
$params = [];

if ($selectedCategory !== '') {
    $sql .= " AND categorie = :cat";
    $params['cat'] = $selectedCategory;
}
if ($search !== '') {
    $sql .= " AND name LIKE :search";
    $params['search'] = '%' . $search . '%';
}

$sql .= " ORDER BY $sort $order";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

function sortLink($field, $currentSort, $currentOrder, $category, $search, $label) {
    $newOrder = ($currentSort === $field && $currentOrder === 'ASC') ? 'DESC' : 'ASC';
    $arrow = $currentSort === $field ? ($currentOrder === 'ASC' ? '↑' : '↓') : '';
    $params = http_build_query([
        'sort' => $field,
        'order' => $newOrder,
        'category' => $category,
        'search' => $search
    ]);
    return "<a href=\"?$params\">$label $arrow</a>";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des articles</title>
</head>
<body>
    <h1>Articles en vente</h1>

    <form method="get" style="margin-bottom:15px;">
        <label for="search">Rechercher :</label>
        <input type="text" name="search" id="search" value="<?= htmlspecialchars($search) ?>">

        <label for="category">Catégorie :</label>
        <select name="category" id="category">
            <option value="">-- Toutes --</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= htmlspecialchars($cat) ?>" <?= ($cat === $selectedCategory) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Filtrer</button>
    </form>

    <div>
        <strong>Trier par :</strong>
        <?= sortLink('published_at', $sort, $order, $selectedCategory, $search, 'Date') ?> |
        <?= sortLink('price', $sort, $order, $selectedCategory, $search, 'Prix') ?> 
    </div>

    <?php if (empty($articles)): ?>
        <p>Aucun article trouvé.</p>
    <?php else: ?>
        <?php foreach ($articles as $article): ?>
            <div style="border:1px solid #ccc; margin:10px; padding:10px;">
                <h2><?= htmlspecialchars($article['name']) ?></h2>
                <p><?= nl2br(htmlspecialchars($article['description'])) ?></p>
                <p>Prix : <?= number_format($article['price'], 2) ?> €</p>
                <p>Catégorie : <?= htmlspecialchars($article['categorie']) ?></p>
                <p>Publié le : <?= $article['published_at'] ?></p>
                <?php if (!empty($article['image_url'])): ?>
                    <img src="<?= htmlspecialchars($article['image_url']) ?>" alt="Image" width="150">
                <?php endif; ?>
                <a href="detail.php?id=<?= urlencode($article['id']) ?>">
                 <button type="button">Voir détail</button>
</a>

            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
