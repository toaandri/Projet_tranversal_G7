<?php 
    include 'includes/db.php';
$result = $conn->query("SELECT cours.nom, enseignants.nom AS enseignant, AVG(evaluations.note) AS moyenne
                         FROM cours
                         JOIN enseignants ON cours.enseignant_id = enseignants.id
                         JOIN evaluations ON cours.id = evaluations.cours_id
                         GROUP BY cours.id
                         HAVING moyenne < 3");
while ($row = $result->fetch_assoc()) {
    echo "Cours: {$row['nom']} - Enseignant: {$row['enseignant']} - Moyenne: " . round($row['moyenne'], 2) . "\n";
}
?>
