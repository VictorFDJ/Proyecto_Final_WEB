
<?php
include 'config.php';
if (!isLoggedIn() || $_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: index.php');
    exit;
}
$conn = conectarDB();
$sql = "INSERT INTO correcciones (incidencia_id, usuario_id, campo, nuevo_valor) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param('iiss', $_POST['inc_id'], $_SESSION['user_id'], $_POST['campo'], $_POST['nuevo_valor']);
$stmt->execute();
$conn->close();
header('Location: index.php');
?>
