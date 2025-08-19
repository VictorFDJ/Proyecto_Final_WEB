
<?php
include 'config.php';
if (!isLoggedIn() || getUserRole() != 'reportero') {
    header('Location: login.php');
    exit;
}

$conn = conectarDB();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $foto = '';
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $target = 'uploads/' . basename($_FILES['foto']['name']);
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $target)) {
            $foto = $target;
        }
    }

    $sql = "INSERT INTO incidencias (titulo, descripcion, provincia, municipio, barrio, latitud, longitud, fecha, muertos, heridos, perdida, link_redes, foto, reportero_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssssddsiidsis', $_POST['titulo'], $_POST['descripcion'], $_POST['provincia'], $_POST['municipio'], $_POST['barrio'], $_POST['lat'], $_POST['lng'], $_POST['fecha'], $_POST['muertos'], $_POST['heridos'], $_POST['perdida'], $_POST['link'], $foto, $_SESSION['user_id']);
    $stmt->execute();
    $inc_id = $stmt->insert_id;

    if (isset($_POST['tipos'])) {
        $stmt = $conn->prepare("INSERT INTO incidencia_tipos (incidencia_id, tipo_id) VALUES (?, ?)");
        foreach ($_POST['tipos'] as $tipo) {
            $stmt->bind_param('ii', $inc_id, $tipo);
            $stmt->execute();
        }
    }

    $conn->close();
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reportar Incidencia</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
</head>
<body>
    <div class="container mt-4">
        <h2>Reportar Incidencia</h2>
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label>Título</label>
                <input type="text" name="titulo" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Fecha</label>
                <input type="date" name="fecha" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Tipos</label>
                <?php
                $result = $conn->query("SELECT * FROM tipos_incidencias");
                while ($row = $result->fetch_assoc()) {
                    echo "<div class='form-check'><input type='checkbox' name='tipos[]' value='{$row['id']}' class='form-check-input'> " . htmlspecialchars($row['nombre']) . "</div>";
                }
                $conn->close();
                ?>
            </div>
            <div class="mb-3">
                <label>Descripción</label>
                <textarea name="descripcion" class="form-control" required></textarea>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label>Provincia</label>
                    <input type="text" name="provincia" class="form-control" required placeholder="Ej. Santo Domingo">
                </div>
                <div class="col-md-4 mb-3">
                    <label>Municipio</label>
                    <input type="text" name="municipio" class="form-control" required placeholder="Ej. Distrito Nacional">
                </div>
                <div class="col-md-4 mb-3">
                    <label>Barrio</label>
                    <input type="text" name="barrio" class="form-control" required placeholder="Ej. Zona Colonial">
                </div>
            </div>
            <div class="mb-3">
                <label>Coordenadas (Seleccione en el mapa)</label>
                <div id="map" style="height: 300px;"></div>
                <input type="hidden" name="lat" id="lat">
                <input type="hidden" name="lng" id="lng">
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label>Muertos</label>
                    <input type="number" name="muertos" class="form-control" value="0">
                </div>
                <div class="col-md-4 mb-3">
                    <label>Heridos</label>
                    <input type="number" name="heridos" class="form-control" value="0">
                </div>
                <div class="col-md-4 mb-3">
                    <label>Pérdida (RD$)</label>
                    <input type="number" name="perdida" class="form-control" step="0.01" value="0.00">
                </div>
            </div>
            <div class="mb-3">
                <label>Link a redes</label>
                <input type="url" name="link" class="form-control">
            </div>
            <div class="mb-3">
                <label>Foto</label>
                <input type="file" name="foto" class="form-control" accept="image/*">
            </div>
            <button type="submit" class="btn btn-primary">Reportar</button>
        </form>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        let map = L.map('map').setView([18.7357, -70.1627], 8);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
        let marker;
        map.on('click', e => {
            if (marker) map.removeLayer(marker);
            marker = L.marker(e.latlng).addTo(map);
            document.getElementById('lat').value = e.latlng.lat;
            document.getElementById('lng').value = e.latlng.lng;
        });
    </script>
</body>
</html>
