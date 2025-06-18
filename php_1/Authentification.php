<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include 'includes/db.php';
    $identifiant = $_POST['identifiant'];

    $stmt = $conn->prepare("SELECT * FROM utilisateurs WHERE identifiant = ?");
    $stmt->bind_param("s", $identifiant);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        $_SESSION['id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        if ($user['role'] === 'admin') header("Location: admin.php");
        elseif ($user['role'] === 'rp') header("Location: rp.php");
        else header("Location: index.php");
        exit();
    } else {
        echo "Identifiant invalide\n";
    }
}
?>
