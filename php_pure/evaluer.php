<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'etudiant') {
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

$sql = "
    SELECT e.id_enseignant, e.nom_enseignant, c.id_cours, c.nom_cours
    FROM enseignant e
    JOIN cours c ON e.id_enseignant = c.id_enseignant
    ORDER BY e.nom_enseignant, c.nom_cours
";
$stmt = $conn->prepare($sql);
$stmt->execute();
$enseignant_cours = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $enseignant_cours = $_POST['enseignant_cours'];
    list($id_enseignant, $id_cours) = explode(':', $enseignant_cours);
    $note = $_POST['note'];
    $commentaire = $_POST['commentaire'];
    $date_evaluation = date('Y-m-d');

    $sql = "
        INSERT INTO evaluation (id_enseignant, id_cours, note, commentaire, date_evaluation, validee) 
        VALUES (:id_enseignant, :id_cours, :note, :commentaire, :date_evaluation, 0)
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id_enseignant', $id_enseignant);
    $stmt->bindParam(':id_cours', $id_cours);
    $stmt->bindParam(':note', $note);
    $stmt->bindParam(':commentaire', $commentaire);
    $stmt->bindParam(':date_evaluation', $date_evaluation);
    if ($stmt->execute()) {
        $message = "Évaluation soumise avec succès. Elle sera validée par un administrateur.";
    } else {
        $error = "Erreur lors de la soumission de l'évaluation.";
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: Authentification.php");
    exit();
}

$conn = null;
?>


<?php if (isset($message)) { ?>
            <p class="message"><?php echo htmlspecialchars($message); ?></p>
        <?php } ?>
        <?php if (isset($error)) { ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php } ?>
        <form method="POST" action="evaluer.php">
            <select name="enseignant_cours" required>
                <option value="">Sélectionner un enseignant et son cours</option>
                <?php foreach ($enseignant_cours as $ec) { ?>
                    <option value="<?php echo htmlspecialchars($ec['id_enseignant'] . ':' . $ec['id_cours']); ?>">
                        <?php echo htmlspecialchars($ec['nom_enseignant'] . ' - ' . $ec['nom_cours']); ?>
                    </option>
                <?php } ?>
