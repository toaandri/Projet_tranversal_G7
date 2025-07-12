<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'etudiant') {
    header("Location: Authentification.php");
    exit();
}

if (!isset($_GET['id_cours']) && !isset($_GET['id_enseignant'])) {
    header("Location: etudiant.php");
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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['signaler'])) {
    $id_evaluation = $_POST['id_evaluation'];
    $sql = "UPDATE evaluation SET signale = 1 WHERE id_evaluation = :id_evaluation";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id_evaluation', $id_evaluation, PDO::PARAM_INT);
    $stmt->execute();
    $message = "Commentaire signalé avec succès. Il sera examiné par un administrateur.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['suivre_cours'])) {
    $id_cours = $_POST['id_cours'];
    $id_etudiant = $_SESSION['user_id'];
    $date_suivi = date('Y-m-d');

    $sql = "SELECT id_suivi FROM suivi_cours WHERE id_etudiant = :id_etudiant AND id_cours = :id_cours";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id_etudiant', $id_etudiant);
    $stmt->bindParam(':id_cours', $id_cours);
    $stmt->execute();
    if ($stmt->fetch()) {
        $message = "Vous suivez déjà ce cours.";
    } else {
        $sql = "INSERT INTO suivi_cours (id_etudiant, id_cours, date_suivi) VALUES (:id_etudiant, :id_cours, :date_suivi)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id_etudiant', $id_etudiant);
        $stmt->bindParam(':id_cours', $id_cours);
        $stmt->bindParam(':date_suivi', $date_suivi);
        if ($stmt->execute()) {
            $message = "Vous suivez maintenant ce cours.";
        } else {
            $error = "Erreur lors de l'inscription au cours.";
        }
    }
}

if (isset($_GET['id_cours'])) {
    $id_cours = $_GET['id_cours'];
    $sql = "
        SELECT c.nom_cours, c.filiere, c.semestre, c.horaire, e.nom_enseignant, 
               COALESCE(AVG(ev.note), 0) as moyenne_note, 
               COALESCE(COUNT(ev.id_evaluation), 0) as nombre_evaluations
        FROM cours c
        JOIN enseignant e ON c.id_enseignant = e.id_enseignant
        LEFT JOIN evaluation ev ON c.id_cours = ev.id_cours AND ev.validee = 1
        WHERE c.id_cours = :id_cours
        GROUP BY c.id_cours, c.nom_cours, c.filiere, c.semestre, c.horaire, e.nom_enseignant
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id_cours', $id_cours);
    $stmt->execute();
    $cours = $stmt->fetch(PDO::FETCH_ASSOC);

    $sql = "
        SELECT ev.id_evaluation, ev.note, ev.commentaire, ev.date_evaluation, ev.signale
        FROM evaluation ev
        WHERE ev.id_cours = :id_cours AND ev.validee = 1
        ORDER BY ev.date_evaluation DESC
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id_cours', $id_cours);
    $stmt->execute();
    $commentaires = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $id_enseignant = $_GET['id_enseignant'];
    $sql = "
        SELECT e.nom_enseignant, COALESCE(AVG(ev.note), 0) as moyenne_note, 
               COALESCE(COUNT(ev.id_evaluation), 0) as nombre_evaluations
        FROM enseignant e
        LEFT JOIN evaluation ev ON e.id_enseignant = ev.id_enseignant AND ev.validee = 1
        WHERE e.id_enseignant = :id_enseignant
        GROUP BY e.id_enseignant, e.nom_enseignant
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id_enseignant', $id_enseignant);
    $stmt->execute();
    $enseignant = $stmt->fetch(PDO::FETCH_ASSOC);

    $sql = "
        SELECT c.id_cours, c.nom_cours, c.filiere, c.semestre, c.horaire, 
               COALESCE(AVG(ev.note), 0) as moyenne_note, 
               COALESCE(COUNT(ev.id_evaluation), 0) as nombre_evaluations
        FROM cours c
        LEFT JOIN evaluation ev ON c.id_cours = ev.id_cours AND ev.validee = 1
        WHERE c.id_enseignant = :id_enseignant
        GROUP BY c.id_cours, c.nom_cours, c.filiere, c.semestre, c.horaire
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id_enseignant', $id_enseignant);
    $stmt->execute();
    $cours_enseignant = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $sql = "
        SELECT ev.id_evaluation, ev.note, ev.commentaire, ev.date_evaluation, ev.signale, c.nom_cours
        FROM evaluation ev
        JOIN cours c ON ev.id_cours = c.id_cours
        WHERE ev.id_enseignant = :id_enseignant AND ev.validee = 1
        ORDER BY ev.date_evaluation DESC
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id_enseignant', $id_enseignant);
    $stmt->execute();
    $commentaires = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <title>Détails</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <h1 class="site-title">Portail de Recommandation Cours Étudiant</h1>
    <button class="logout-btn" onclick="window.location.href='details.php?<?php echo isset($id_cours) ? 'id_cours=' . htmlspecialchars($id_cours) : 'id_enseignant=' . htmlspecialchars($id_enseignant); ?>&logout=true'">Déconnexion</button>
    <div class="details-container">
        <h1>Détails</h1>
        <a href="etudiant.php" class="back-btn">Retour au tableau de bord</a>
        <?php if (isset($message)) { ?>
            <p class="message"><?php echo htmlspecialchars($message); ?></p>
        <?php } ?>
        <?php if (isset($error)) { ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php } ?>
        <?php if (isset($cours)) { ?>
            <h2><?php echo htmlspecialchars($cours['nom_cours']); ?></h2>
            <p><strong>Enseignant :</strong> <?php echo htmlspecialchars($cours['nom_enseignant']); ?></p>
            <p><strong>Filière :</strong> <?php echo htmlspecialchars($cours['filiere']); ?></p>
            <p><strong>Semestre :</strong> <?php echo htmlspecialchars($cours['semestre']); ?></p>
            <p><strong>Horaire :</strong> <?php echo htmlspecialchars($cours['horaire']); ?></p>
            <p><strong>Note moyenne :</strong> <?php echo $cours['moyenne_note'] > 0 ? number_format($cours['moyenne_note'], 2) . '/5' : 'N/A'; ?></p>
            <p><strong>Nombre d'évaluations :</strong> <?php echo $cours['nombre_evaluations']; ?></p>
            <form action="details.php?id_cours=<?php echo htmlspecialchars($id_cours); ?>" method="POST">
                <input type="hidden" name="id_cours" value="<?php echo htmlspecialchars($id_cours); ?>">
                <input type="hidden" name="suivre_cours" value="1">
                <input type="submit" class="suivre-btn" value="Suivre ce cours">
            </form>
        <?php } else { ?>
            <h2><?php echo htmlspecialchars($enseignant['nom_enseignant']); ?></h2>
            <p><strong>Note moyenne :</strong> <?php echo $enseignant['moyenne_note'] > 0 ? number_format($enseignant['moyenne_note'], 2) . '/5' : 'N/A'; ?></p>
            <p><strong>Nombre d'évaluations :</strong> <?php echo $enseignant['nombre_evaluations']; ?></p>
            <h3>Suivre un cours de cet enseignant</h3>
            <?php if (count($cours_enseignant) > 0) { ?>
                <form action="details.php?id_enseignant=<?php echo htmlspecialchars($id_enseignant); ?>" method="POST">
                    <select name="id_cours" required>
                        <option value="">Sélectionner un cours</option>
                        <?php foreach ($cours_enseignant as $c) { ?>
                            <option value="<?php echo htmlspecialchars($c['id_cours']); ?>"><?php echo htmlspecialchars($c['nom_cours']); ?></option>
                        <?php } ?>
                    </select>
                    <input type="hidden" name="suivre_cours" value="1">
                    <input type="submit" class="suivre-btn" value="Suivre ce cours">
                </form>
            <?php } else { ?>
                <p>Aucun cours disponible pour cet enseignant.</p>
            <?php } ?>
            <h3>Cours enseignés</h3>
            <?php if (count($cours_enseignant) > 0) { ?>
                <table>
                    <thead>
                        <tr>
                            <th>Cours</th>
                            <th>Filière</th>
                            <th>Semestre</th>
                            <th>Horaire</th>
                            <th>Note moyenne</th>
                            <th>Évaluations</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cours_enseignant as $c) { ?>
                            <tr>
                                <td><a href="details.php?id_cours=<?php echo htmlspecialchars($c['id_cours']); ?>"><?php echo htmlspecialchars($c['nom_cours']); ?></a></td>
                                <td><?php echo htmlspecialchars($c['filiere']); ?></td>
                                <td><?php echo htmlspecialchars($c['semestre']); ?></td>
                                <td><?php echo htmlspecialchars($c['horaire']); ?></td>
                                <td><?php echo $c['moyenne_note'] > 0 ? number_format($c['moyenne_note'], 2) . '/5' : 'N/A'; ?></td>
                                <td><?php echo $c['nombre_evaluations']; ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            <?php } else { ?>
                <p>Aucun cours enseigné par cet enseignant.</p>
            <?php } ?>
        <?php } ?>
        <h3>Commentaires</h3>
        <p class="debug">Nombre de commentaires : <?php echo count($commentaires); ?></p>
        <table>
            <thead>
                <tr>
                    <th>Note</th>
                    <th>Commentaire</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($commentaires) > 0) { ?>
                    <?php foreach ($commentaires as $comment) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($comment['note']); ?>/5</td>
                            <td><?php echo htmlspecialchars($comment['commentaire'] ?: 'Aucun commentaire'); ?></td>
                            <td><?php echo htmlspecialchars($comment['date_evaluation']); ?></td>
                            <td>
                                <?php if (!$comment['signale']) { ?>
                                    <form action="details.php?<?php echo isset($id_cours) ? 'id_cours=' . htmlspecialchars($id_cours) : 'id_enseignant=' . htmlspecialchars($id_enseignant); ?>" method="POST">
                                        <input type="hidden" name="id_evaluation" value="<?php echo htmlspecialchars($comment['id_evaluation']); ?>">
                                        <input type="hidden" name="signaler" value="1">
                                        <input type="submit" class="signal-btn" value="Signaler">
                                    </form>
                                <?php } else { ?>
                                    <span>Signalé</span>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="4">Aucun commentaire validé pour cette ressource.</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <a href="etudiant.php" class="back-btn">Retour au tableau de bord</a>
    </div>
    <script src="assets/js/scripts.js"></script>
</body>
</html>
