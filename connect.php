
<<<<<<< HEAD
<?php
// Connexion à la base
$host = 'localhost';
$dbname = 'photodata';
$username = 'root';
$password = '';

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

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($fileTmp, $targetPath)) {
            $stmt = $pdo->prepare("INSERT INTO photos (filename, description, votes) VALUES (?, ?, 0)");
            $stmt->execute([$fileName, $description]);
            $message = "Photo envoyée avec succès !";
        } else {
            $message = "Erreur lors de l'upload.";
        }
    }
}

// Traitement vote
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'vote') {
    $photo_id = $_POST['photo_id'] ?? null;
    if ($photo_id) {
        $stmt = $pdo->prepare("UPDATE photos SET votes = votes + 1 WHERE id = ?");
        $stmt->execute([$photo_id]);
        $message = "Merci pour votre vote !";
    }
}

// Récupération des photos
$photos = $pdo->query("SELECT * FROM photos ORDER BY votes DESC")->fetchAll(PDO::FETCH_ASSOC);
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
            <a href="concours.php">Concours Photo</a>
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
        <?php foreach ($photos as $photo): ?>
            <div style="margin:20px;">
                <img src="uploads/<?= htmlspecialchars($photo['filename']) ?>" alt="photo" class="image"><br>
                <p><?= htmlspecialchars($photo['description']) ?></p>
                <p><strong><?= $photo['votes'] ?> votes</strong></p>
            </div>
        <?php endforeach; ?>
    </main>

    <footer class="footer">
        <p>2025 Concours Photo - Espace Naturel de la Motte</p>
    </footer>
</body>
</html>
=======


<?php
//c'est pour ce connecter a la base de données , mettez require_once 'connect.php' si vous avez besoin de la base 
$host = 'localhost'; //Hébergement local
$dbname = 'photodata';
$username = 'root';
$password = ''; //Par défault sur MAMP

//la table qui contient les informations des photos s'appelle photos
// ces éléments sont id (automatique Primary Key),nom (le nom de la personne qui donne les photos ), adresse du fichier stocké ( adresse du fichier dans votre pc) et un mail 
?>
>>>>>>> Victor_herbgement
