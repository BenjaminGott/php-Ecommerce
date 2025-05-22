<?php
include_once '../includes/db_connect.php';
include_once '../includes/header.php';

echo headerComponent();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';
    $profilePicturePath = null;

    if ($username === '' || $email === '' || $password === '' || $passwordConfirm === '') {
        $error = "Tous les champs sont obligatoires.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "L'adresse email n'est pas valide.";
    } elseif ($password !== $passwordConfirm) {
        $error = "Les mots de passe ne correspondent pas.";
    } else {
        if (!empty($_POST['profile_picture'])) {
            $profilePicturePath = trim($_POST['profile_picture']);
            if (!filter_var($profilePicturePath, FILTER_VALIDATE_URL)) {
                $error = "Le lien de la photo de profil n'est pas valide.";
            }
        }

        if (!$error) {
            $stmt = $pdo->prepare('SELECT id FROM User WHERE username = :username OR email = :email');
            $stmt->execute(['username' => $username, 'email' => $email]);
            if ($stmt->fetch()) {
                $error = "Ce nom d'utilisateur ou cet email est déjà pris.";
            } else {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('INSERT INTO User (username, email, password, balance, profile_picture, role) VALUES (:username, :email, :password, :balance, :profile_picture, :role)');
                $stmt->execute([
                    'username' => $username,
                    'email' => $email,
                    'password' => $passwordHash,
                    'balance' => 10000.00,
                    'profile_picture' => $profilePicturePath,
                    'role' => 'user'
                ]);

                $userId = $pdo->lastInsertId();


                $_SESSION['user_id'] = $userId;
                $_SESSION['username'] = $username;
                $_SESSION['role'] = 'user';
                $_SESSION['profile_picture'] = $profilePicturePath;

                header('Location: index.php');
                exit;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><title>Inscription</title></head>
<body>
    <h1>Inscription</h1>

    <?php if ($error): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="post">
        <label for="username">Nom d'utilisateur :</label><br>
        <input type="text" id="username" name="username" required value="<?= htmlspecialchars($username ?? '') ?>"><br><br>

        <label for="email">Email :</label><br>
        <input type="email" id="email" name="email" required value="<?= htmlspecialchars($email ?? '') ?>"><br><br>

        <label for="password">Mot de passe :</label><br>
        <input type="password" id="password" name="password" required><br><br>

        <label for="password_confirm">Confirmer le mot de passe :</label><br>
        <input type="password" id="password_confirm" name="password_confirm" required><br><br>

        <label for="profile_picture">Lien de la photo de profil :</label><br>
        <input type="url" id="profile_picture" name="profile_picture" placeholder="https://example.com/photo.jpg" value="<?= htmlspecialchars($profilePicturePath ?? '') ?>"><br><br>

        <button type="submit">S'inscrire</button>
    </form>

    <p>Déjà inscrit ? <a href="login.php">Se connecter</a></p>
</body>
</html>