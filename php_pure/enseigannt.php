<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'enseignant') {
    header("Location: Authentification.php");
    exit();
}

$id_enseignant = $_SESSION['user_id'];

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
    SELECT c.nom_cours, ev.note, ev.commentaire, ev.date_evaluation
    FROM evaluation ev
    JOIN cours c ON ev.id_cours = c.id_cours
    WHERE ev.id_enseignant = :id_enseignant AND ev.validee = 1
    ORDER BY ev.date_evaluation DESC
";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':id_enseignant', $id_enseignant);
$stmt->execute();
$evaluations = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sql = "
    SELECT sc.id_etudiant, c.nom_cours, sc.date_suivi
    FROM suivi_cours sc
    JOIN cours c ON sc.id_cours = c.id_cours
    WHERE c.id_enseignant = :id_enseignant
    ORDER BY sc.date_suivi DESC
";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':id_enseignant', $id_enseignant);
$stmt->execute();
$suivis = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: Authentification.php");
    exit();
}

$conn = null;
?>


<?php if (count($evaluations) > 0) { ?>
            <table>
                <thead>
                    <tr>
                        <th>Cours</th>
                        <th>Note</th>
                        <th>Commentaire</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($evaluations as $eval) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($eval['nom_cours']); ?></td>
                            <td><?php echo $eval['note']; ?>/5</td>
                            <td><?php echo htmlspecialchars($eval['commentaire'] ?: 'Aucun commentaire'); ?></td>
                            <td><?php echo $eval['date_evaluation']; ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <p>Aucune évaluation validée pour le moment.</p>
        <?php } ?>
        <h2>Notifications de suivi</h2>
        <?php if (count($suivis) > 0) { ?>
            <table>
                <thead>
                    <tr>
                        <th>Étudiant</th>
                        <th>Cours</th>
                        <th>Date de suivi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php for ($i = 0; $i < min(5, count($suivis)); $i++) { $suivi = $suivis[$i]; ?>
                        <tr>
                            <td><?php echo htmlspecialchars($suivi['id_etudiant']); ?></td>
                            <td><?php echo htmlspecialchars($suivi['nom_cours']); ?></td>
                            <td><?php echo $suivi['date_suivi']; ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <p>Aucun suivi de cours pour le moment.</p>
        <?php } ?>
