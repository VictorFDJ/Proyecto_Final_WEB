
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
        $stmt = $conn->prepare("INSERT INTO provincias (nombre) VALUES (?)");
        $stmt->bind_param('s', $nombre);
        $stmt->execute();
    } elseif (isset($_POST['edit'])) {
        $id = (int)$_POST['id'];
        $nombre = $_POST['nombre'];
        $stmt = $conn->prepare("UPDATE provincias SET nombre = ? WHERE id = ?");
        $stmt->bind_param('si', $nombre, $id);
        $stmt->execute();
    } elseif (isset($_POST['delete'])) {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("DELETE FROM provincias WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Admin Provincias</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <h2>Gestión de Provincias</h2>
        <form method="POST" class="mb-4">
            <div class="row">
                <div class="col-md-6">
                    <input type="text" name="nombre" placeholder="Nombre de la Provincia" class="form-control" required>
                </div>
                <div class="col-md-2">
                    <button name="add" class="btn btn-primary">Agregar</button>
                </div>
            </div>
        </form>
        <table class="table">
            <thead><tr><th>ID</th><th>Nombre</th><th>Acciones</th></tr></thead>
            <tbody>
                <?php
                $stmt = $conn->prepare("SELECT * FROM provincias");
                $stmt->execute();
                $result = $stmt->get_result();
                while ($row = $result->fetch_assoc()) {
                    echo "<tr><td>{$row['id']}</td><td>" . htmlspecialchars($row['nombre']) . "</td><td>
                            <form method='POST' class='d-inline'>
                                <input type='hidden' name='id' value='{$row['id']}'>
                                <input type='text' name='nombre' value='" . htmlspecialchars($row['nombre']) . "' class='form-control d-inline-block w-auto' required>
                                <button name='edit' class='btn btn-warning btn-sm'>Editar</button>
                            </form>
                            <form method='POST' class='d-inline'>
                                <input type='hidden' name='id' value='{$row['id']}'>
                                <button name='delete' class='btn btn-danger btn-sm'>Eliminar</button>
                            </form>
                          </td></tr>";
                }
                $conn->close();
                ?>
            </tbody>
        </table>
        <a href="super.php" class="btn btn-secondary">Volver</a>
    </div>
</body>
</html>
