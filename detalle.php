
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

echo "<h5>" . htmlspecialchars($inc['titulo']) . "</h5>";
echo "<p><strong>Fecha:</strong> " . htmlspecialchars($inc['fecha']) . "</p>";
echo "<p><strong>Tipos:</strong> " . htmlspecialchars($inc['tipos']) . "</p>";
echo "<p><strong>Descripción:</strong> " . htmlspecialchars($inc['descripcion']) . "</p>";
echo "<p><strong>Ubicación:</strong> " . htmlspecialchars("{$inc['provincia']}, {$inc['municipio']}, {$inc['barrio']}") . "</p>";
echo "<p><strong>Coordenadas:</strong> " . htmlspecialchars("{$inc['latitud']}, {$inc['longitud']}") . "</p>";
echo "<p><strong>Muertos:</strong> " . (int)$inc['muertos'] . "</p>";
echo "<p><strong>Heridos:</strong> " . (int)$inc['heridos'] . "</p>";
echo "<p><strong>Pérdida:</strong> " . number_format($inc['perdida'], 2) . " RD$</p>";
if ($inc['link_redes']) {
    echo "<p><strong>Link:</strong> <a href='" . htmlspecialchars($inc['link_redes']) . "'>Ver</a></p>";
}
if ($inc['foto']) {
    echo "<img src='" . htmlspecialchars($inc['foto']) . "' class='img-fluid' alt='Foto'>";
}

echo "<h6>Comentarios</h6>";
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
