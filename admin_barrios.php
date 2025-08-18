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
        $municipio_id = (int)$_POST['municipio_id'];
        $stmt = $conn->prepare("INSERT INTO barrios (nombre, municipio_id) VALUES (?, ?)");
        $stmt->bind_param('si', $nombre, $municipio_id);
        $stmt->execute();
    } elseif (isset($_POST['edit'])) {
        $id = (int)$_POST['id'];
        $nombre = $_POST['nombre'];
        $municipio_id = (int)$_POST['municipio_id'];
        $stmt = $conn->prepare("UPDATE barrios SET nombre = ?, municipio_id = ? WHERE id = ?");
        $stmt->bind_param('sii', $nombre, $municipio_id, $id);
        $stmt->execute();
    } elseif (isset($_POST['delete'])) {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("DELETE FROM barrios WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Admin Barrios</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <h2>Gestión de Barrios</h2>
        <form method="POST" class="mb-4">
            <div class="row">
                <div class="col-md-6">
                    <input type="text" name="nombre" placeholder="Nombre del Barrio" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <select name="municipio_id" id="municipio" class="form-control" required>
                        <option value="">Seleccione Municipio</option>
                        <?php
                        $stmt = $conn->prepare("SELECT * FROM municipios");
                        $stmt->execute();
                        $result = $stmt->get_result();
                        while ($row = $result->fetch_assoc()) {
                            echo "<option value='{$row['id']}'>" . htmlspecialchars($row['nombre']) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button name="add" class="btn btn-primary">Agregar</button>
                </div>
            </div>
        </form>
        <table class="table">
            <thead><tr><th>ID</th><th>Nombre</th><th>Municipio</th><th>Acciones</th></tr></thead>
            <tbody>
                <?php
                $stmt = $conn->prepare("SELECT b.*, m.nombre as municipio FROM barrios b JOIN municipios m ON m.id = b.municipio_id");
                $stmt->execute();
                $result = $stmt->get_result();
                while ($row = $result->fetch_assoc()) {
                    echo "<tr><td>{$row['id']}</td><td>" . htmlspecialchars($row['nombre']) . "</td><td>" . htmlspecialchars($row['municipio']) . "</td><td>
                            <form method='POST' class='d-inline'>
                                <input type='hidden' name='id' value='{$row['id']}'>
                                <input type='text' name='nombre' value='" . htmlspecialchars($row['nombre']) . "' class='form-control d-inline-block w-auto' required>
                                <select name='municipio_id' class='form-control d-inline-block w-auto' required>
                                    <option value=''>Seleccione</option>";
                    $mun_stmt = $conn->prepare("SELECT * FROM municipios");
                    $mun_stmt->execute();
                    $mun_result = $mun_stmt->get_result();
                    while ($mun = $mun_result->fetch_assoc()) {
                        $selected = $mun['id'] == $row['municipio_id'] ? 'selected' : '';
                        echo "<option value='{$mun['id']}' $selected>" . htmlspecialchars($mun['nombre']) . "</option>";
                    }
                    echo "</select>
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
