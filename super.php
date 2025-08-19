
<?php
include 'config.php';
if (!isLoggedIn() || getUserRole() != 'validador') {
    header('Location: login.php');
    exit;
}

$conn = conectarDB();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['accion'])) {
        $id = (int)$_POST['id'];
        if ($_POST['accion'] == 'aprobar') {
            $stmt = $conn->prepare("UPDATE incidencias SET estado = 'aprobado' WHERE id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
        } elseif ($_POST['accion'] == 'rechazar') {
            $stmt = $conn->prepare("UPDATE incidencias SET estado = 'rechazado' WHERE id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
        } elseif ($_POST['accion'] == 'aprobar_correccion') {
            $cor_id = (int)$_POST['cor_id'];
            $stmt = $conn->prepare("SELECT * FROM correcciones WHERE id = ?");
            $stmt->bind_param('i', $cor_id);
            $stmt->execute();
            $cor = $stmt->get_result()->fetch_assoc();
            if ($cor['campo'] == 'coordenadas') {
                list($lat, $lng) = explode(',', $cor['nuevo_valor']);
                $stmt = $conn->prepare("UPDATE incidencias SET latitud = ?, longitud = ? WHERE id = ?");
                $stmt->bind_param('ddi', $lat, $lng, $cor['incidencia_id']);
            } else {
                $campo = $cor['campo'];
                $stmt = $conn->prepare("UPDATE incidencias SET $campo = ? WHERE id = ?");
                $stmt->bind_param('si', $cor['nuevo_valor'], $cor['incidencia_id']);
            }
            $stmt->execute();
            $stmt = $conn->prepare("UPDATE correcciones SET estado = 'aprobado' WHERE id = ?");
            $stmt->bind_param('i', $cor_id);
            $stmt->execute();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Validador</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container mt-4">
        <h2>Panel Validador</h2>
        
        <h4>Reportes Pendientes</h4>
        <table class="table">
            <thead><tr><th>Título</th><th>Fecha</th><th>Acciones</th></tr></thead>
            <tbody>
                <?php
                $stmt = $conn->prepare("SELECT * FROM incidencias WHERE estado = 'pendiente'");
                $stmt->execute();
                $result = $stmt->get_result();
                while ($row = $result->fetch_assoc()) {
                    echo "<tr><td>" . htmlspecialchars($row['titulo']) . "</td><td>" . htmlspecialchars($row['fecha']) . "</td><td>
                            <form method='POST'>
                                <input type='hidden' name='id' value='{$row['id']}'>
                                <button name='accion' value='aprobar' class='btn btn-success btn-sm'>Aprobar</button>
                                <button name='accion' value='rechazar' class='btn btn-danger btn-sm'>Rechazar</button>
                            </form>
                          </td></tr>";
                }
                ?>
            </tbody>
        </table>
        
        <h4>Correcciones Pendientes</h4>
        <table class="table">
            <thead><tr><th>Incidencia</th><th>Campo</th><th>Nuevo Valor</th><th>Acciones</th></tr></thead>
            <tbody>
                <?php
                $stmt = $conn->prepare("SELECT c.*, i.titulo FROM correcciones c JOIN incidencias i ON i.id = c.incidencia_id WHERE c.estado = 'pendiente'");
                $stmt->execute();
                $result = $stmt->get_result();
                while ($row = $result->fetch_assoc()) {
                    echo "<tr><td>" . htmlspecialchars($row['titulo']) . "</td><td>" . htmlspecialchars($row['campo']) . "</td><td>" . htmlspecialchars($row['nuevo_valor']) . "</td><td>
                            <form method='POST'>
                                <input type='hidden' name='cor_id' value='{$row['id']}'>
                                <button name='accion' value='aprobar_correccion' class='btn btn-success btn-sm'>Aprobar</button>
                            </form>
                          </td></tr>";
                }
                ?>
            </tbody>
        </table>
        
        <h4>Estadísticas</h4>
        <canvas id="chart" width="400" height="200"></canvas>
        <script>
            <?php
            $stmt = $conn->prepare("SELECT t.nombre, COUNT(it.tipo_id) as count FROM tipos_incidencias t 
                                    LEFT JOIN incidencia_tipos it ON it.tipo_id = t.id 
                                    GROUP BY t.id");
            $stmt->execute();
            $result = $stmt->get_result();
            $labels = [];
            $data = [];
            while ($row = $result->fetch_assoc()) {
                $labels[] = $row['nombre'];
                $data[] = $row['count'];
            }
            ?>
            new Chart(document.getElementById('chart'), {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($labels); ?>,
                    datasets: [{ label: 'Incidencias por Tipo', data: <?php echo json_encode($data); ?>, backgroundColor: 'rgba(75, 192, 192, 0.2)' }]
                }
            });
        </script>
        
        <h4>Gestión de Catálogos</h4>
        <a href="admin_municipios.php" class="btn btn-secondary">Municipios</a>
        <a href="admin_barrios.php" class="btn btn-secondary">Barrios</a>
        <a href="admin_tipos.php" class="btn btn-secondary">Tipos de Incidencias</a>
        <a href="index.php" class="btn btn-secondary mt-3">Volver</a>
    </div>
    <?php $conn->close(); ?>
</body>
</html>
