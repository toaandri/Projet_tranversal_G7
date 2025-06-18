<?php 
    include 'includes/db.php';
$id = $_GET['id'];
$stmt = $conn->prepare("SELECT nom FROM cours WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$course = $result->fetch_assoc();
echo "Cours: {$course['nom']}\n";

$stmt = $conn->prepare("SELECT note, commentaire FROM evaluations WHERE cours_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    echo "Note: {$row['note']} - Commentaire: {$row['commentaire']}\n";
}
?>
