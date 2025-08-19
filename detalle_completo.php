
<?php
include 'config.php';
if (!isset($_GET['id'])) die('ID no proporcionado');
$id = (int)$_GET['id'];
$conn = conectarDB();
$stmt = $conn->prepare("SELECT i.*, GROUP_CONCAT(t.nombre SEPARATOR ', ') as tipos 
                        FROM incidencias i 
                        LEFT JOIN incidencia_tipos it ON it.incidencia_id = i.id
                        LEFT JOIN tipos_incidencias t ON t.id = it.tipo_id
                        WHERE i.id = ? GROUP BY i.id");
$stmt->bind_param('i', $id);
$stmt->execute();
$inc = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalles de <?php echo htmlspecialchars($inc['titulo']); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>#map { height: 400px; }</style>
</head>
<body>
    <div class="container mt-4">
        <h2><?php echo htmlspecialchars($inc['titulo']); ?></h2>
        <div class="row">
            <div class="col-md-6">
                <p><strong>Fecha:</strong> <?php echo htmlspecialchars($inc['fecha']); ?></p>
                <p><strong>Tipos:</strong> <?php echo htmlspecialchars($inc['tipos']); ?></p>
                <p><strong>Descripción:</strong> <?php echo htmlspecialchars($inc['descripcion']); ?></p>
                <p><strong>Ubicación:</strong> <?php echo htmlspecialchars("{$inc['provincia']}, {$inc['municipio']}, {$inc['barrio']}"); ?></p>
                <p><strong>Coordenadas:</strong> <?php echo htmlspecialchars("{$inc['latitud']}, {$inc['longitud']}"); ?></p>
                <p><strong>Muertos:</strong> <?php echo (int)$inc['muertos']; ?></p>
                <p><strong>Heridos:</strong> <?php echo (int)$inc['heridos']; ?></p>
                <p><strong>Pérdida:</strong> <?php echo number_format($inc['perdida'], 2); ?> RD$</p>
                <?php if ($inc['link_redes']) echo "<p><strong>Link:</strong> <a href='" . htmlspecialchars($inc['link_redes']) . "'>Ver</a></p>"; ?>
                <?php if ($inc['foto']) echo "<img src='" . htmlspecialchars($inc['foto']) . "' class='img-fluid' alt='Foto'>"; ?>
            </div>
            <div class="col-md-6">
                <div id="map"></div>
            </div>
        </div>
        <h4>Comentarios</h4>
        <?php
        if (isLoggedIn()) {
            echo "<form method='POST' action='agregar_comentario.php'>
                    <input type='hidden' name='inc_id' value='$id'>
                    <textarea name='comentario' class='form-control mb-2' required></textarea>
                    <button type='submit' class='btn btn-primary'>Comentar</button>
                  </form>";
        }
        $stmt = $conn->prepare("SELECT c.*, u.email FROM comentarios c JOIN usuarios u ON u.id = c.usuario_id WHERE incidencia_id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($com = $result->fetch_assoc()) {
            echo "<div class='border p-2 mt-2'><strong>" . htmlspecialchars($com['email']) . ":</strong> " . htmlspecialchars($com['comentario']) . " <small>(" . htmlspecialchars($com['fecha']) . ")</small></div>";
        }
        if (isLoggedIn()) {
            echo "<h6>Sugerir Corrección</h6>";
            echo "<form method='POST' action='sugerir_correccion.php'>
                    <input type='hidden' name='inc_id' value='$id'>
                    <select name='campo' class='form-control mb-2'>
                        <option value='muertos'>Muertos</option>
                        <option value='heridos'>Heridos</option>
                        <option value='provincia'>Provincia</option>
                        <option value='municipio'>Municipio</option>
                        <option value='barrio'>Barrio</option>
                        <option value='perdida'>Pérdida</option>
                        <option value='coordenadas'>Coordenadas</option>
                    </select>
                    <input type='text' name='nuevo_valor' class='form-control mb-2' required>
                    <button type='submit' class='btn btn-warning'>Sugerir</button>
                  </form>";
        }
        $conn->close();
        ?>
        <a href="lista.php" class="btn btn-secondary mt-3">Volver</a>
    </div>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        let map = L.map('map').setView([<?php echo $inc['latitud']; ?>, <?php echo $inc['longitud']; ?>], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
        L.marker([<?php echo $inc['latitud']; ?>, <?php echo $inc['longitud']; ?>]).addTo(map);
    </script>
</body>
</html>
