<?php 
    include 'includes/db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cours_id = $_POST['cours_id'];
    $note = $_POST['note'];
    $commentaire = $_POST['commentaire'];

    $stmt = $conn->prepare("INSERT INTO evaluations (cours_id, note, commentaire) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $cours_id, $note, $commentaire);
    $stmt->execute();
    if (isset($_POST['confirmer_commentaire']) && $_POST['confirmer_commentaire'] == 'oui') {
        // Le commentaire a déjà été inséré avec l'évaluation
    } else {
        // Mettre à jour l'évaluation pour supprimer le commentaire si non confirmé
        $last_id = $conn->insert_id;
        $stmt_update = $conn->prepare("UPDATE evaluations SET commentaire = NULL WHERE id = ?");
        $stmt_update->bind_param("i", $last_id);
        $stmt_update->execute();
        $stmt_update->close();
    }
    echo "Évaluation envoyée avec succès !\n";
}
?>
