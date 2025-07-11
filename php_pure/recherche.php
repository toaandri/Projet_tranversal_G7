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

$filiere = $_SESSION['filiere'];
$semestre = $_SESSION['semestre'];

$sql = "
    SELECT c.id_cours, c.nom_cours, c.horaire, e.nom_enseignant, 
           COALESCE(AVG(ev.note), 0) as moyenne_note, 
           COALESCE(COUNT(ev.id_evaluation), 0) as nombre_evaluations
    FROM cours c
    JOIN enseignant e ON c.id_enseignant = e.id_enseignant
    LEFT JOIN evaluation ev ON c.id_cours = ev.id_cours AND ev.validee = 1
    WHERE c.filiere = :filiere AND c.semestre = :semestre
    GROUP BY c.id_cours, c.nom_cours, c.horaire, e.nom_enseignant
    ORDER BY nombre_evaluations DESC, moyenne_note DESC
    LIMIT 5
";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':filiere', $filiere);
$stmt->bindParam(':semestre', $semestre);
$stmt->execute();
$recommandations = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: Authentification.php");
    exit();
}

$conn = null;
?>

<?php if (count($recommandations) > 0) { ?>
            <table>
                <thead>
                    <tr>
                        <th>Cours</th>
                        <th>Enseignant</th>
                        <th>Horaire</th>
                        <th>Note moyenne</th>
                        <th>Évaluations</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recommandations as $rec) { ?>
                        <tr>
                            <td><a href="details.php?id_cours=<?php echo $rec['id_cours']; ?>"><?php echo htmlspecialchars($rec['nom_cours']); ?></a></td>
                            <td><?php echo htmlspecialchars($rec['nom_enseignant']); ?></td>
                            <td><?php echo htmlspecialchars($rec['horaire']); ?></td>
                            <td><?php echo number_format($rec['moyenne_note'], 2); ?>/5</td>
                            <td><?php echo $rec['nombre_evaluations']; ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <p>Aucune recommandation disponible pour votre filière et semestre.</p>
        <?php } ?>
