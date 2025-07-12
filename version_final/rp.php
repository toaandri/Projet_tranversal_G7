<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'rp') {
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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajouter_cours'])) {
    $id_cours = $_POST['id_cours'];
    $nom_cours = $_POST['nom_cours'];
    $id_enseignant = $_POST['id_enseignant'];
    $filiere = $_POST['filiere'];
    $semestre = $_POST['semestre'];
    $horaire = $_POST['horaire'];

    $sql = "INSERT INTO cours (id_cours, nom_cours, id_enseignant, filiere, semestre, horaire) 
            VALUES (:id_cours, :nom_cours, :id_enseignant, :filiere, :semestre, :horaire)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id_cours', $id_cours);
    $stmt->bindParam(':nom_cours', $nom_cours);
    $stmt->bindParam(':id_enseignant', $id_enseignant);
    $stmt->bindParam(':filiere', $filiere);
    $stmt->bindParam(':semestre', $semestre);
    $stmt->bindParam(':horaire', $horaire);
    if ($stmt->execute()) {
        $message = "Cours ajouté avec succès.";
    } else {
        $error = "Erreur lors de l'ajout du cours.";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['supprimer_cours'])) {
    $id_cours = $_POST['id_cours'];
    $sql = "DELETE FROM cours WHERE id_cours = :id_cours";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id_cours', $id_cours);
    if ($stmt->execute()) {
        $message = "Cours supprimé avec succès.";
    } else {
        $error = "Erreur lors de la suppression du cours.";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajouter_enseignant'])) {
    $id_enseignant = $_POST['id_enseignant'];
    $nom_enseignant = $_POST['nom_enseignant'];
    $mot_de_passe = $_POST['mot_de_passe'];

    $sql = "INSERT INTO utilisateur (id_utilisateur, mot_de_passe, role) VALUES (:id_enseignant, :mot_de_passe, 'enseignant')";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id_enseignant', $id_enseignant);
    $stmt->bindParam(':mot_de_passe', $mot_de_passe);
    $stmt->execute();

    $sql = "INSERT INTO enseignant (id_enseignant, nom_enseignant) VALUES (:id_enseignant, :nom_enseignant)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id_enseignant', $id_enseignant);
    $stmt->bindParam(':nom_enseignant', $nom_enseignant);
    if ($stmt->execute()) {
        $message = "Enseignant ajouté avec succès.";
    } else {
        $error = "Erreur lors de l'ajout de l'enseignant.";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['supprimer_enseignant'])) {
    $id_enseignant = $_POST['id_enseignant'];
    $sql = "DELETE FROM enseignant WHERE id_enseignant = :id_enseignant";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id_enseignant', $id_enseignant);
    if ($stmt->execute()) {
        $sql = "DELETE FROM utilisateur WHERE id_utilisateur = :id_enseignant";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id_enseignant', $id_enseignant);
        $stmt->execute();
        $message = "Enseignant supprimé avec succès.";
    } else {
        $error = "Erreur lors de la suppression de l'enseignant.";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['proposer_reunion'])) {
    $id_enseignant = $_POST['id_enseignant'];
    $message_reunion = $_POST['message_reunion'];
    $date_proposition = date('Y-m-d');

    $sql = "INSERT INTO reunion (id_enseignant, date_proposition, message, statut) 
            VALUES (:id_enseignant, :date_proposition, :message, 'proposee')";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id_enseignant', $id_enseignant);
    $stmt->bindParam(':date_proposition', $date_proposition);
    $stmt->bindParam(':message', $message_reunion);
    if ($stmt->execute()) {
        $message = "Réunion proposée avec succès.";
    } else {
        $error = "Erreur lors de la proposition de réunion.";
    }
}

$sql = "
    SELECT e.id_enseignant, e.nom_enseignant, COALESCE(AVG(ev.note), 0) as moyenne_note, 
           COALESCE(COUNT(ev.id_evaluation), 0) as nombre_evaluations
    FROM enseignant e
    LEFT JOIN evaluation ev ON e.id_enseignant = ev.id_enseignant AND ev.validee = 1
    GROUP BY e.id_enseignant, e.nom_enseignant
    ORDER BY moyenne_note ASC
";
$stmt = $conn->prepare($sql);
$stmt->execute();
$enseignants = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sql = "SELECT c.id_cours, c.nom_cours, c.filiere, c.semestre, c.horaire, e.nom_enseignant 
        FROM cours c 
        JOIN enseignant e ON c.id_enseignant = e.id_enseignant";
$stmt = $conn->prepare($sql);
$stmt->execute();
$cours = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <title>Interface Responsable Pédagogique</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <h1 class="site-title">Portail de Recommandation Cours Étudiant</h1>
    <button class="logout-btn" onclick="window.location.href='rp.php?logout=true'">Déconnexion</button>
    <div class="container">
        <h1>Interface Responsable Pédagogique</h1>
        <?php if (isset($message)) { ?>
            <p class="message"><?php echo htmlspecialchars($message); ?></p>
        <?php } ?>
        <?php if (isset($error)) { ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php } ?>
        
        <h2>Gestion des enseignants</h2>
        <h3>Enseignants et leurs notes moyennes</h3>
        <table>
            <thead>
                <tr>
                    <th>Enseignant</th>
                    <th>Note moyenne</th>
                    <th>Évaluations</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($enseignants as $enseignant) { ?>
                    <tr <?php echo $enseignant['moyenne_note'] > 0 && $enseignant['moyenne_note'] < 3 ? 'class="low-rating"' : ''; ?>>
                        <td><?php echo htmlspecialchars($enseignant['nom_enseignant']); ?></td>
                        <td><?php echo $enseignant['moyenne_note'] > 0 ? number_format($enseignant['moyenne_note'], 2) . '/5' : 'N/A'; ?></td>
                        <td><?php echo $enseignant['nombre_evaluations']; ?></td>
                        <td>
                            <form action="rp.php" method="POST">
                                <input type="hidden" name="id_enseignant" value="<?php echo htmlspecialchars($enseignant['id_enseignant']); ?>">
                                <input type="hidden" name="supprimer_enseignant" value="1">
                                <input type="submit" class="btn" value="Supprimer">
                            </form>
                            <form action="rp.php" method="POST">
                                <input type="hidden" name="id_enseignant" value="<?php echo htmlspecialchars($enseignant['id_enseignant']); ?>">
                                <textarea name="message_reunion" placeholder="Raison de la réunion" required></textarea>
                                <input type="hidden" name="proposer_reunion" value="1">
                                <input type="submit" class="btn" value="Proposer une réunion">
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <h3>Ajouter un enseignant</h3>
        <form action="rp.php" method="POST">
            <input type="text" name="id_enseignant" placeholder="ID Enseignant" required>
            <input type="text" name="nom_enseignant" placeholder="Nom de l'enseignant" required>
            <input type="password" name="mot_de_passe" placeholder="Mot de passe" required>
            <input type="hidden" name="ajouter_enseignant" value="1">
            <input type="submit" class="btn" value="Ajouter l'enseignant">
        </form>

        <h2>Gestion des cours</h2>
        <table>
            <thead>
                <tr>
                    <th>Cours</th>
                    <th>Enseignant</th>
                    <th>Filière</th>
                    <th>Semestre</th>
                    <th>Horaire</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cours as $c) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($c['nom_cours']); ?></td>
                        <td><?php echo htmlspecialchars($c['nom_enseignant']); ?></td>
                        <td><?php echo htmlspecialchars($c['filiere']); ?></td>
                        <td><?php echo htmlspecialchars($c['semestre']); ?></td>
                        <td><?php echo htmlspecialchars($c['horaire']); ?></td>
                        <td>
                            <form action="rp.php" method="POST">
                                <input type="hidden" name="id_cours" value="<?php echo htmlspecialchars($c['id_cours']); ?>">
                                <input type="hidden" name="supprimer_cours" value="1">
                                <input type="submit" class="btn" value="Supprimer">
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <h3>Ajouter un cours</h3>
        <form action="rp.php" method="POST">
            <input type="text" name="id_cours" placeholder="ID Cours" required>
            <input type="text" name="nom_cours" placeholder="Nom du cours" required>
            <select name="id_enseignant" required>
                <option value="">Sélectionner un enseignant</option>
                <?php foreach ($enseignants as $enseignant) { ?>
                    <option value="<?php echo htmlspecialchars($enseignant['id_enseignant']); ?>">
                        <?php echo htmlspecialchars($enseignant['nom_enseignant']); ?>
                    </option>
                <?php } ?>
            </select>
            <input type="text" name="filiere" placeholder="Filière" required>
            <input type="text" name="semestre" placeholder="Semestre" required>
            <input type="text" name="horaire" placeholder="Horaire" required>
            <input type="hidden" name="ajouter_cours" value="1">
            <input type="submit" class="btn" value="Ajouter le cours">
        </form>

        <a href="etudiant.php" class="back-btn">Retour au tableau de bord</a>
    </div>
    <script src="assets/js/scripts.js"></script>
</body>
</html>
