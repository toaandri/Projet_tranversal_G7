<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: Authentification.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "portail_recommandations";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->exec("SET NAMES utf8mb4");
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

$error = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $mot_de_passe = $_POST['mot_de_passe'];
    $role = $_POST['role'];

    if (empty($id) || empty($mot_de_passe) || empty($role)) {
        $error = "Tous les champs sont obligatoires.";
    } else {
        $table = '';
        $id_field = '';
        $redirect = '';
        if ($role == 'etudiant') {
            $table = 'etudiant';
            $id_field = 'id_etudiant';
            $redirect = 'etudiant.php';
        } elseif ($role == 'admin') {
            $table = 'admin';
            $id_field = 'id_admin';
            $redirect = 'admin.php';
        } elseif ($role == 'enseignant') {
            $table = 'enseignant';
            $id_field = 'id_enseignant';
            $redirect = 'enseignant.php';
        } elseif ($role == 'rp') {
            $table = 'rp';
            $id_field = 'id_rp';
            $redirect = 'rp.php';
        }

        if (!empty($table) && !empty($id_field)) {
            $sql = "SELECT * FROM $table WHERE $id_field = :id AND mot_de_passe = :mot_de_passe";
            echo "<pre>SQL: $sql\nTable: $table, ID Field: $id_field, ID: $id, Mot de passe: $mot_de_passe\n</pre>";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':mot_de_passe', $mot_de_passe);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $_SESSION['user_id'] = $user[$id_field];
                $_SESSION['role'] = $role;
                header("Location: $redirect");
                exit();
            } else {
                $error = "Identifiants incorrects.";
            }
        } else {
            $error = "Rôle non reconnu.";
        }
    }
}

$conn = null;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authentification</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <h1 class="site-title animate-on-load">Portail de Recommandation Cours Étudiant</h1>
    <div class="auth-container animate-on-load">
        <h1>Connexion</h1>
        <?php if ($error) { ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php } ?>
        <form method="POST" action="Authentification.php">
            <select name="role" required>
                <option value="">Sélectionner un rôle</option>
                <option value="etudiant">Étudiant</option>
                <option value="admin">Administrateur</option>
                <option value="enseignant">Enseignant</option>
                <option value="rp">Responsable Pédagogique</option>
            </select>
            <input type="text" name="id" placeholder="Identifiant" required>
            <input type="password" name="mot_de_passe" placeholder="Mot de passe" required>
            <input type="submit" value="Se connecter">
        </form>
    </div>
    <script src="assets/js/scripts.js"></script>
</body>
</html>
