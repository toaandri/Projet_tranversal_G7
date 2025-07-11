<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $id_evaluation = $_POST['id_evaluation'];
    $action = $_POST['action'];
    
    if ($action == 'valider') {
        $sql = "UPDATE evaluation SET validee = 1, signale = 0 WHERE id_evaluation = :id_evaluation";
    } elseif ($action == 'rejeter') {
        $sql = "DELETE FROM evaluation WHERE id_evaluation = :id_evaluation";
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id_evaluation', $id_evaluation, PDO::PARAM_INT);
    $stmt->execute();
    $message = $action == 'valider' ? "Évaluation validée avec succès." : "Évaluation rejetée avec succès.";
}

$sql = "
    SELECT ev.id_evaluation, ev.note, ev.commentaire, ev.date_evaluation, ev.signale, 
           e.nom_enseignant, c.nom_cours
    FROM evaluation ev
    JOIN enseignant e ON ev.id_enseignant = e.id_enseignant
    JOIN cours c ON ev.id_cours = c.id_cours
    WHERE ev.validee = 0
    ORDER BY ev.date_evaluation DESC
";
$stmt = $conn->prepare($sql);
$stmt->execute();
$evaluations = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
        <?php if (count($evaluations) > 0) { ?>
            <table>
                <thead>
                    <tr>
                        <th>Enseignant</th>
                        <th>Cours</th>
                        <th>Note</th>
                        <th>Commentaire</th>
                        <th>Date</th>
                        <th>Signalé</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($evaluations as $eval) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($eval['nom_enseignant']); ?></td>
                            <td><?php echo htmlspecialchars($eval['nom_cours']); ?></td>
                            <td><?php echo $eval['note']; ?>/5</td>
                            <td><?php echo htmlspecialchars($eval['commentaire'] ?: 'Aucun commentaire'); ?></td>
                            <td><?php echo $eval['date_evaluation']; ?></td>
                            <td><?php echo $eval['signale'] ? 'Oui' : 'Non'; ?></td>
                            <td>
                                <form method="POST" action="admin.php" style="display:inline;">
                                    <input type="hidden" name="id_evaluation" value="<?php echo $eval['id_evaluation']; ?>">
                                    <input type="hidden" name="action" value="valider">
                                    <input type="submit" class="action-btn" value="Valider">
                                </form>
                                <form method="POST" action="admin.php" style="display:inline;">
                                    <input type="hidden" name="id_evaluation" value="<?php echo $eval['id_evaluation']; ?>">
                                    <input type="hidden" name="action" value="rejeter">
                                    <input type="submit" class="action-btn" value="Rejeter">
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <p>Aucune évaluation en attente de modération.</p>
        <?php } ?>
