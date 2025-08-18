
<?php
include 'config.php';
if (!isLoggedIn() || $_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: index.php');
    exit;
}
$conn = conectarDB();
$sql = "INSERT INTO comentarios (incidencia_id, usuario_id, comentario) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param('iis', $_POST['inc_id'], $_SESSION['user_id'], $_POST['comentario']);
$stmt->execute();
$conn->close();
header('Location: index.php');
?>
