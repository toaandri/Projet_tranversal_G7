<?php
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

        $sql = "SELECT * FROM $table WHERE $id_field = :id AND mot_de_passe = :mot_de_passe";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':mot_de_passe', $mot_de_passe);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $_SESSION['user_id'] = $user[$id_field];
            $_SESSION['role'] = $role;
            if ($role == 'etudiant') {
                $_SESSION['filiere'] = $user['filiere'];
                $_SESSION['semestre'] = $user['semestre'];
            }
            header("Location: $redirect");
            exit();
        } else {
            $error = "Identifiants incorrects.";
        }
    }
}

$conn = null;
?>


<?php if ($error) { ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php } ?>
