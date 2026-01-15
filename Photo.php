<?php
require_once 'connect.php';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Traitement upload
$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload') {
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $description = $_POST['description'] ?? '';
        $fileTmp = $_FILES['photo']['tmp_name'];
        $fileName = basename($_FILES['photo']['name']);
        $uploadDir = "uploads/";
        
        // Validation : taille max 5MB
        $maxSize = 5 * 1024 * 1024;
        if ($_FILES['photo']['size'] > $maxSize) {
            $message = "Erreur : Fichier trop volumineux (max 5MB).";
        } else if (!in_array($_FILES['photo']['type'], ['image/jpeg', 'image/png', 'image/gif', 'image/webp'])) {
            $message = "Erreur : Format non accepté (JPG, PNG, GIF, WebP uniquement).";
        } else {
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Générer un nom unique pour éviter les collisions
            $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
            $uniqueName = uniqid('photo_') . '_' . time() . '.' . $fileExtension;
            $targetPath = $uploadDir . $uniqueName;

            if (move_uploaded_file($fileTmp, $targetPath)) {
                $stmt = $pdo->prepare("INSERT INTO photos (filename, description, votes) VALUES (?, ?, 0)");
                $stmt->execute([$uniqueName, $description]);
                $message = "Photo envoyée avec succès !";
            } else {
                $message = "Erreur lors de l'upload.";
            }
        }
    } else {
        $message = "Erreur : Veuillez sélectionner une photo.";
    }
}

// Traitement vote
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'vote') {
    $photo_id = $_POST['photo_id'] ?? null;
    if ($photo_id && is_numeric($photo_id)) {
        $stmt = $pdo->prepare("UPDATE photos SET votes = votes + 1 WHERE id = ?");
        $stmt->execute([(int)$photo_id]);
        $message = "Merci pour votre vote !";
    } else {
        $message = "Erreur : Photo invalide.";
    }
}

// Récupération des photos
$photos = $pdo->query("SELECT * FROM photos ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Concours Photo - Participation</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="page">
    <header class="header">
        <h1 class="title">Concours Photo</h1>
        <nav class="menu">
            <a href="index.html">Accueil</a>
            <a href="Photo.php">Concours Photo</a>
        </nav>
    </header>

    <main class="content">
        <?php if ($message): ?>
            <p class="message"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <h2>Soumettre une photo</h2>
        <form class="form" method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="upload">
            <label for="photo">Choisissez une photo :</label><br>
            <input type="file" name="photo" id="photo" accept="image/*" required><br><br>

            <label for="description">Description :</label><br>
            <textarea name="description" id="description" rows="3" cols="30"></textarea><br><br>

            <button type="submit">Envoyer</button>
        </form>

        <h2>Voter pour une photo</h2>
        <form class="form" method="post">
            <input type="hidden" name="action" value="vote">
            <label for="photo_id">Sélectionnez une photo :</label><br>
            <select name="photo_id" id="photo_id" required>
                <?php foreach ($photos as $photo): ?>
                    <option value="<?= $photo['id'] ?>">
                        <?= htmlspecialchars($photo['description'] ?: $photo['filename']) ?> 
                        (<?= $photo['votes'] ?> votes)
                    </option>
                <?php endforeach; ?>
            </select><br><br>
            <button type="submit">Voter</button>
        </form>

        <h2>Galerie des photos</h2>
        <?php if (empty($photos)): ?>
            <p style="font-style: italic; color: #888;">Aucune photo soumise pour le moment. Soyez le premier à participer !</p>
        <?php else: ?>
            <div class="photo-gallery">
                <?php foreach ($photos as $photo): ?>
                    <div class="photo-item">
                        <img src="uploads/<?= htmlspecialchars($photo['filename']) ?>" alt="photo" class="image">
                        <div class="photo-info">
                            <p><strong><?= htmlspecialchars($photo['description'] ?: 'Sans titre') ?></strong></p>
                            <p class="vote-count"><?= $photo['votes'] ?> vote<?= $photo['votes'] > 1 ? 's' : '' ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <footer class="footer">
        <p>2025 Concours Photo - Espace Naturel de la Motte</p>
    </footer>
</body>
</html>