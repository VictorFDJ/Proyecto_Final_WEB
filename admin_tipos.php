<?php
include 'config.php';
if (!isLoggedIn() || getUserRole() != 'validador') {
    header('Location: login.php');
    exit;
}

$conn = conectarDB();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add'])) {
        $nombre = $_POST['nombre'];
        $stmt = $conn->prepare("INSERT INTO tipos_incidencias (nombre) VALUES (?)");
        $stmt->bind_param('s', $nombre);
        $stmt->execute();
    } elseif (isset($_POST['edit'])) {
        $id = (int)$_POST['id'];
        $nombre = $_POST['nombre'];
        $stmt = $conn->prepare("UPDATE tipos_incidencias SET nombre = ? WHERE id = ?");
        $stmt->bind_param('si', $nombre, $id);
        $stmt->execute();
    } elseif (isset($_POST['delete'])) {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("DELETE FROM tipos_incidencias WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Admin Tipos de Incidencias</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <h2>Gestión de Tipos de Incidencias</h2>