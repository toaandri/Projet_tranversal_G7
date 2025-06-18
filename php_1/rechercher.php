<?php 
    include 'includes/db.php';
if (isset($_GET['q'])) {
    $q = "%" . $_GET['q'] . "%";
    $stmt = $conn->prepare("SELECT cours.id, cours.nom, AVG(evaluations.note) as moyenne
                             FROM cours
                             LEFT JOIN evaluations ON cours.id = evaluations.cours_id
                             WHERE cours.nom LIKE ?
                             GROUP BY cours.id");
    $stmt->bind_param("s", $q);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        echo "Cours: {$row['nom']} - Moyenne: " . round($row['moyenne'], 2) . "/5\n";
    }
}
?>
