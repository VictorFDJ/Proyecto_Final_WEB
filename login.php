<?php
require_once 'config.php'; // aquí ya se inicia la sesión de forma segura

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = conectarDB();
    $email = $_POST['email'];

    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user) {
        if ($user['rol'] == 'validador' && isset($_POST['password']) && md5($_POST['password']) === $user['password']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['rol'] = $user['rol'];
            header('Location: index.php');
            exit;
        } elseif ($user['rol'] == 'reportero') {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['rol'] = $user['rol'];
            header('Location: index.php');
            exit;
        } else {
            $error = "Credenciales inválidas";
        }
    } else {
        $error = "Usuario no encontrado";
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <h2>Login</h2>
        <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
        <form method="POST">
            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Password (solo para validadores)</label>
                <input type="password" name="password" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
        </form>
        <p class="mt-3">Para reporteros, usa email registrado. En producción, considera OAuth (Google/Microsoft).</p>
        <a href="register.php" class="btn btn-outline-success">Registrarse</a>
    </div>
</body>
</html>
