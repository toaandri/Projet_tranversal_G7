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

$sql = "SELECT id_enseignant, nom_enseignant FROM enseignant";
$stmt = $conn->prepare($sql);
$stmt->execute();
$enseignants = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['planifier'])) {
    $id_enseignant = $_POST['id_enseignant'];
    $id_rp = $_SESSION['user_id'];
    $date_reunion = $_POST['date_reunion'];
    $heure_reunion = $_POST['heure_reunion'];
    $lieu = $_POST['lieu'];

    $sql = "INSERT INTO reunions (id_enseignant, id_rp, date_reunion, heure_reunion, lieu) 
            VALUES (:id_enseignant, :id_rp, :date_reunion, :heure_reunion, :lieu)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id_enseignant', $id_enseignant);
    $stmt->bindParam(':id_rp', $id_rp);
    $stmt->bindParam(':date_reunion', $date_reunion);
    $stmt->bindParam(':heure_reunion', $heure_reunion);
    $stmt->bindParam(':lieu', $lieu);
    if ($stmt->execute()) {
        $message = "Réunion planifiée avec succès.";
    } else {
        $error = "Erreur lors de la planification de la réunion.";
    }
}

$sql = "
    SELECT r.id_reunion, r.date_reunion, r.heure_reunion, r.lieu, e.nom_enseignant
    FROM reunions r
    JOIN enseignant e ON r.id_enseignant = e.id_enseignant
    WHERE r.id_rp = :id_rp
    ORDER BY r.date_reunion DESC
";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':id_rp', $_SESSION['user_id']);
$stmt->execute();
$reunions = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
        <form method="POST" action="rp.php">
            <select name="id_enseignant" required>
                <option value="">Sélectionner un enseignant</option>
                <?php foreach ($enseignants as $enseignant) { ?>
                    <option value="<?php echo $enseignant['id_enseignant']; ?>"><?php echo htmlspecialchars($enseignant['nom_enseignant']); ?></option>
                <?php } ?>
            </select>
            <input type="date" name="date_reunion" required>
            <input type="time" name="heure_reunion" required>
            <input type="text" name="lieu" placeholder="Lieu de la réunion" required>
            <input type="hidden" name="planifier" value="1">
            <input type="submit" value="Planifier">
        </form>
        <h2>Réunions planifiées</h2>
        <?php if (count($reunions) > 0) { ?>
            <table>
                <thead>
                    <tr>
                        <th>Enseignant</th>
                        <th>Date</th>
                        <th>Heure</th>
                        <th>Lieu</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reunions as $reunion) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($reunion['nom_enseignant']); ?></td>
                            <td><?php echo $reunion['date_reunion']; ?></td>
                            <td><?php echo $reunion['heure_reunion']; ?></td>
                            <td><?php echo htmlspecialchars($reunion['lieu']); ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <p>Aucune réunion planifiée pour le moment.</p>
        <?php } ?>
