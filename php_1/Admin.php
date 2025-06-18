<?php 
    include 'includes/db.php';
$result = $conn->query("SELECT evaluations.id, cours.nom AS cours, commentaire
                         FROM evaluations
                         JOIN cours ON evaluations.cours_id = cours.id
                         WHERE commentaire LIKE '%nul%' OR commentaire LIKE '%injure%'");
while ($row = $result->fetch_assoc()) {
    echo "Cours: {$row['cours']} - Commentaire: {$row['commentaire']}\n";
}
?>
