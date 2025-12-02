<?php
// Traitement du formulaire
$errors = [];
$success = null;
$uploadedFileUrl = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';

    // Validation de base
    if ($name === '') {
        $errors[] = 'Le nom est requis.';
    }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Un email valide est requis.';
    }

    if (!isset($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {
        $errors[] = "Aucune image sélectionnée.";
    } else {
        $file = $_FILES['image'];

        // Vérifier les erreurs d'upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Erreur lors de l\'envoi du fichier.';
        } else {
            // Limite de taille (2MB)
            $maxSize = 2 * 1024 * 1024;
            if ($file['size'] > $maxSize) {
                $errors[] = 'Le fichier est trop grand (max 2MB).';
            }

            // Vérifier que c'est bien une image
            $imageInfo = @getimagesize($file['tmp_name']);
            if ($imageInfo === false) {
                $errors[] = 'Le fichier n\'est pas une image valide.';
            }

            // Autoriser certaines extensions
            $allowedExt = ['jpg', 'jpeg', 'png', 'gif'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedExt, true)) {
                $errors[] = 'Extension non prise en charge. Utilisez jpg, png ou gif.';
            }

            // Si tout va bien, déplacer le fichier dans le dossier images
            if (empty($errors)) {
                $imagesDir = __DIR__ . DIRECTORY_SEPARATOR . 'images';
                if (!is_dir($imagesDir)) {
                    if (!mkdir($imagesDir, 0755, true)) {
                        $errors[] = 'Impossible de créer le dossier images.';
                    }
                }

                if (empty($errors)) {
                    // Générer un nom unique
                    $safeBase = preg_replace('/[^A-Za-z0-9_-]/', '_', pathinfo($file['name'], PATHINFO_FILENAME));
                    $newName = $safeBase . '_' . uniqid() . '.' . $ext;
                    $destination = $imagesDir . DIRECTORY_SEPARATOR . $newName;

                    if (move_uploaded_file($file['tmp_name'], $destination)) {
                        $success = 'Image téléchargée avec succès.';
                        // URL relative pour affichage
                        $uploadedFileUrl = 'images/' . $newName;
                    } else {
                        $errors[] = 'Échec lors du déplacement du fichier.';
                    }
                }
            }
        }
    }
}
?>

<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Concours - Upload d'image</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { font-family: Arial, sans-serif; max-width:800px; margin:40px auto; padding:0 20px; }
        form { background:#f7f7f7; padding:20px; border-radius:6px; }
        label { display:block; margin-top:10px; }
        input[type="text"], input[type="email"], input[type="file"] { width:100%; padding:8px; margin-top:6px; }
        button { margin-top:12px; padding:10px 16px; }
        .errors { background:#ffe6e6; border:1px solid #ffb3b3; padding:10px; margin-bottom:12px; }
        .success { background:#e6ffe6; border:1px solid #b3ffb3; padding:10px; margin-bottom:12px; }
        .preview { margin-top:12px; }
        img { max-width:100%; height:auto; border-radius:6px; }
    </style>
</head>
<body>
<h1>Ajouter une image</h1>

<?php if (!empty($errors)): ?>
    <div class="errors">
        <ul>
            <?php foreach ($errors as $err): ?>
                <li><?php echo htmlspecialchars($err, ENT_QUOTES, 'UTF-8'); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="success"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div>
<?php endif; ?>

<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" enctype="multipart/form-data">
    <label for="name">Nom :</label>
    <input id="name" name="name" type="text" required value="<?php echo isset($name) ? htmlspecialchars($name, ENT_QUOTES, 'UTF-8') : ''; ?>">

    <label for="email">Email :</label>
    <input id="email" name="email" type="email" required value="<?php echo isset($email) ? htmlspecialchars($email, ENT_QUOTES, 'UTF-8') : ''; ?>">

    <label for="image">Image (jpg, png, gif, max 2MB) :</label>
    <input id="image" name="image" type="file" accept="image/*" required>

    <button type="submit">Envoyer</button>
</form>

<?php if ($uploadedFileUrl): ?>
    <div class="preview">
        <h2>Aperçu</h2>
        <p>Nom: <?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?> — Email: <?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?></p>
        <img src="<?php echo htmlspecialchars($uploadedFileUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="Image uploadée">
    </div>
<?php endif; ?>

</body>
</html>