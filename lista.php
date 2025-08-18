
<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de Incidencias</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <h2>Lista de Incidencias</h2>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Título</th>
                    <th>Fecha</th>
                    <th>Tipos</th>
                    <th>Ubicación</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $conn = conectarDB();
                $stmt = $conn->prepare("SELECT i.id, i.titulo, i.fecha, GROUP_CONCAT(t.nombre SEPARATOR ', ') as tipos, i.provincia
                                        FROM incidencias i 
                                        LEFT JOIN incidencia_tipos it ON it.incidencia_id = i.id
                                        LEFT JOIN tipos_incidencias t ON t.id = it.tipo_id
                                        WHERE estado = 'aprobado' GROUP BY i.id");
                $stmt->execute();
                $result = $stmt->get_result();
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td>" . htmlspecialchars($row['titulo']) . "</td>
                            <td>" . htmlspecialchars($row['fecha']) . "</td>
                            <td>" . htmlspecialchars($row['tipos']) . "</td>
                            <td>" . htmlspecialchars($row['provincia']) . "</td>
                            <td><a href='detalle_completo.php?id={$row['id']}' class='btn btn-info btn-sm'>Ver Detalles</a></td>
                          </tr>";
                }
                $conn->close();
                ?>
            </tbody>
        </table>
        <a href="index.php" class="btn btn-secondary">Volver</a>
    </div>
</body>
</html>
