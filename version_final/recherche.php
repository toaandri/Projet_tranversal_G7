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

$filiere = isset($_POST['filiere']) ? $_POST['filiere'] : '';
$semestre = isset($_POST['semestre']) ? $_POST['semestre'] : '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['rechercher'])) {
    $sql = "
        SELECT c.id_cours, c.nom_cours, c.horaire, e.nom_enseignant, c.filiere, c.semestre,
               COALESCE(AVG(ev.note), 0) as moyenne_note, 
               COALESCE(COUNT(ev.id_evaluation), 0) as nombre_evaluations
        FROM cours c
        JOIN enseignant e ON c.id_enseignant = e.id_enseignant
        LEFT JOIN evaluation ev ON c.id_cours = ev.id_cours AND ev.validee = 1
        WHERE c.filiere = :filiere AND c.semestre = :semestre
        GROUP BY c.id_cours, c.nom_cours, c.horaire, e.nom_enseignant, c.filiere, c.semestre
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':filiere', $filiere);
    $stmt->bindParam(':semestre', $semestre);
    $stmt->execute();
    $recommandations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $sql = "
        SELECT c.id_cours, c.nom_cours, c.horaire, e.nom_enseignant, c.filiere, c.semestre,
               COALESCE(AVG(ev.note), 0) as moyenne_note, 
               COALESCE(COUNT(ev.id_evaluation), 0) as nombre_evaluations
        FROM cours c
        JOIN enseignant e ON c.id_enseignant = e.id_enseignant
        LEFT JOIN evaluation ev ON c.id_cours = ev.id_cours AND ev.validee = 1
        GROUP BY c.id_cours, c.nom_cours, c.horaire, e.nom_enseignant, c.filiere, c.semestre
    ";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $recommandations = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: Authentification.php");
    exit();
}

$conn = null;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recherche et Recommandations</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <h1 class="site-title animate-on-load">Portail de Recommandation Cours Étudiant</h1>
    <button class="logout-btn" onclick="window.location.href='recherche.php?logout=true'">Déconnexion</button>
    <div class="recherche-container animate-on-load">
        <h1>Recherche et Recommandations</h1>
        <form method="POST" action="recherche.php">
            <select name="filiere" required>
                <option value="">Sélectionner une filière</option>
                <option value="Informatique" <?php echo $filiere == 'Informatique' ? 'selected' : ''; ?>>Informatique</option>
                <option value="Mathématiques" <?php echo $filiere == 'Mathématiques' ? 'selected' : ''; ?>>Mathématiques</option>
                <option value="Physique" <?php echo $filiere == 'Physique' ? 'selected' : ''; ?>>Physique</option>
            </select>
            <select name="semestre" required>
                <option value="">Sélectionner un semestre</option>
                <option value="S1" <?php echo $semestre == 'S1' ? 'selected' : ''; ?>>S1</option>
                <option value="S2" <?php echo $semestre == 'S2' ? 'selected' : ''; ?>>S2</option>
                <option value="S3" <?php echo $semestre == 'S3' ? 'selected' : ''; ?>>S3</option>
            </select>
            <input type="submit" name="rechercher" value="Rechercher">
        </form>
        <input type="text" id="searchInput" placeholder="Rechercher un cours, enseignant, filière, semestre ou horaire..." class="search-input">
        <h2>Recommandations</h2>
        <?php if (count($recommandations) > 0) { ?>
            <table id="recommandationsTable" class="animate-on-load">
                <thead>
                    <tr>
                        <th>Cours</th>
                        <th>Enseignant</th>
                        <th>Horaire</th>
                        <th>Filière</th>
                        <th>Semestre</th>
                        <th>Note moyenne</th>
                        <th>Évaluations</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recommandations as $rec) { ?>
                        <tr data-search="<?php echo htmlspecialchars(strtolower($rec['nom_cours'] . ' ' . $rec['nom_enseignant'] . ' ' . $rec['filiere'] . ' ' . $rec['semestre'] . ' ' . $rec['horaire'])); ?>">
                            <td><a href="details.php?id_cours=<?php echo $rec['id_cours']; ?>"><?php echo htmlspecialchars($rec['nom_cours']); ?></a></td>
                            <td><?php echo htmlspecialchars($rec['nom_enseignant']); ?></td>
                            <td><?php echo htmlspecialchars($rec['horaire']); ?></td>
                            <td><?php echo htmlspecialchars($rec['filiere']); ?></td>
                            <td><?php echo htmlspecialchars($rec['semestre']); ?></td>
                            <td><?php echo number_format($rec['moyenne_note'], 2); ?>/5</td>
                            <td><?php echo $rec['nombre_evaluations']; ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <p>Aucune recommandation disponible.</p>
        <?php } ?>
    </div>
    <script src="assets/js/scripts.js"></script>
</body>
</html>
