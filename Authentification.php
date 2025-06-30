<?php
session_start();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include 'includes/db.php';
    $identifiant = $_POST['identifiant'];

    $stmt = $conn->prepare("SELECT * FROM admin WHERE login_admin = ?");
    $stmt->bind_param("s", $identifiant);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {

        $_SESSION['id'] = $user['id_admin'];
        $_SESSION['nom'] = $user['nom_admin'];

        $_SESSION['role'] = 'admin';
        header("Location: admin.php");
        exit();
    } else {
        $error = "Identifiant invalide";
    }
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authentification</title>
    <link rel="stylesheet" href="Authentification.css">
</head>
<body>
    <div class="container">
        <h2>Connexion</h2>
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
            <label for="identifiant">Identifiant:</label>
            <input type="text" id="identifiant" name="identifiant" required>
            <!-- Add password field if needed -->
            <!-- <label for="password">Mot de passe:</label> -->
            <!-- <input type="password" id="password" name="password" required> -->
            <button type="submit">Se connecter</button>
        </form>
        <?php if ($error): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
    </div>
</body>
</html>