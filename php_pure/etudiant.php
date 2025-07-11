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
    SELECT e.id_enseignant, e.nom_enseignant, 
           COALESCE(AVG(ev.note), 0) as moyenne_note, 
           COALESCE(COUNT(ev.id_evaluation), 0) as nombre_evaluations
    FROM enseignant e
    LEFT JOIN evaluation ev ON e.id_enseignant = ev.id_enseignant AND ev.validee = 1
    GROUP BY e.id_enseignant, e.nom_enseignant
    ORDER BY nombre_evaluations DESC, moyenne_note DESC
    LIMIT 5
";
$stmt = $conn->prepare($sql);
$stmt->execute();
$profs = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: Authentification.php");
    exit();
}

$conn = null;
?>


<?php if (count($profs) > 0) { ?>
            <table>
                <thead>
                    <tr>
                        <th>Nom de l'enseignant</th>
                        <th>Note moyenne</th>
                        <th>Nombre d'évaluations</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($profs as $prof) { ?>
                        <tr>
                            <td><a href="details.php?id_enseignant=<?php echo $prof['id_enseignant']; ?>"><?php echo htmlspecialchars($prof['nom_enseignant']); ?></a></td>
                            <td><?php echo number_format($prof['moyenne_note'], 2); ?>/5</td>
                            <td><?php echo $prof['nombre_evaluations']; ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <p>Aucune évaluation validée disponible pour le moment.</p>
        <?php } ?>
