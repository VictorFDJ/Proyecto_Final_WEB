
<?php
include 'config.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = conectarDB();
    $email = $_POST['email'];
    $stmt = $conn->prepare("INSERT INTO usuarios (email, rol) VALUES (?, 'reportero')");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $conn->close();
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <h2>Registro como Reportero</h2>
        <form method="POST">
            <div class="mb-3">
                <label>Email (Gmail u Office)</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Registrar</button>
        </form>
        <p class="mt-3">En producción, usa OAuth para verificar.</p>
        <a href="login.php" class="btn btn-outline-primary">Volver al Login</a>
    </div>
</body>
</html>
