<?php 
session_start();
include 'config.php'; 
$conn = conectarDB();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Incidencias</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />
    <style>
        #map { height: 500px; }
    </style>
</head>
<body>
    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="index.php">Incidencias RD</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="report.php">Reportar Incidencia</a></li>
                    <li class="nav-item"><a class="nav-link" href="lista.php">Vista Lista</a></li>
                </ul>
                <?php if (isLoggedIn()): ?>
                    <span class="navbar-text">Bienvenido, <?php echo htmlspecialchars($_SESSION['email']); ?></span>
                    <a href="logout.php" class="btn btn-outline-danger ms-3">Logout</a>
                    <?php if (getUserRole() == 'validador'): ?>
                        <a href="super.php" class="btn btn-outline-primary ms-3">Panel Validador</a>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline-primary">Login</a>
                    <a href="register.php" class="btn btn-outline-success ms-3">Registro</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- MAPA Y FILTROS -->
    <div class="container mt-4">
        <h2>Mapa de Incidencias (Últimas 24 horas)</h2>
        <div id="map"></div>
        <form id="filtros" class="mt-4">
            <div class="row">
                <div class="col-md-3">
                    <label>Provincia</label>
                    <select name="provincia" class="form-control">
                        <option value="">Todas</option>
                        <?php
                        $result = $conn->query("SELECT DISTINCT provincia FROM incidencias ORDER BY provincia ASC");
                        while ($row = $result->fetch_assoc()) {
                            echo "<option value='" . htmlspecialchars($row['provincia']) . "'>" . htmlspecialchars($row['provincia']) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Tipo</label>
                    <select name="tipo" class="form-control">
                        <option value="">Todos</option>
                        <?php
                        $result = $conn->query("SELECT * FROM tipos_incidencias ORDER BY nombre ASC");
                        while ($row = $result->fetch_assoc()) {
                            echo "<option value='{$row['id']}'>" . htmlspecialchars($row['nombre']) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Desde</label>
                    <input type="date" name="desde" class="form-control">
                </div>
                <div class="col-md-3">
                    <label>Hasta</label>
                    <input type="date" name="hasta" class="form-control">
                </div>
            </div>
            <button type="button" onclick="cargarMapa()" class="btn btn-primary mt-3">Aplicar Filtros</button>
        </form>
    </div>

    <!-- MODAL DETALLES -->
    <div class="modal fade" id="detalleModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detalles de Incidencia</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="modalBody"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- SCRIPTS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
    <script>
        // Inicializar mapa
        let map = L.map('map').setView([18.7357, -70.1627], 8);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
        let markers = L.markerClusterGroup();

        // Íconos personalizados
        function getIconByType(tipo) {
            if (tipo === "Accidente") return "icons/accidente.png";
            if (tipo === "Robo") return "icons/robo.png";
            if (tipo === "Pelea") return "icons/pelea.png";
            if (tipo === "Desastre") return "icons/desastre.png";
            return "https://cdn-icons-png.flaticon.com/512/854/854878.png";
        }

        // Cargar incidencias
        function cargarMapa() {
            markers.clearLayers();
            let formData = new FormData(document.getElementById('filtros'));
            let params = new URLSearchParams(formData);

            fetch('get_incidencias.php?' + params.toString())
                .then(response => response.json())
                .then(data => {
                    console.log("DATA RECIBIDA:", data); // debug
                    if (!data.length) {
                        alert("No se encontraron incidencias con esos filtros.");
                        return;
                    }
                    data.forEach(inc => {
                        let tipo = inc.tipos.length > 0 ? inc.tipos[0] : "Otro";
                        let icon = L.icon({
                            iconUrl: getIconByType(tipo),
                            iconSize: [30, 40],
                            iconAnchor: [15, 40],
                            popupAnchor: [0, -35]
                        });

                        let marker = L.marker([inc.latitud, inc.longitud], {icon: icon});
                        
                        // Popup básico
                        marker.bindPopup(`
                            <b>${tipo}</b><br>
                            <b>${inc.titulo}</b><br>
                            ${inc.descripcion}<br>
                            <small>${inc.fecha}</small>
                        `);
                        
                        // Click abre modal
                        marker.on('click', () => mostrarDetalles(inc.id));
                        markers.addLayer(marker);
                    });
                    map.addLayer(markers);
                })
                .catch(err => console.error("Error cargando incidencias:", err));
        }

        // Mostrar detalles en modal
        function mostrarDetalles(id) {
            fetch('detalle.php?id=' + id)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('modalBody').innerHTML = html;
                    let modal = new bootstrap.Modal(document.getElementById('detalleModal'));
                    modal.show();
                });
        }

        // Inicializar mapa
        cargarMapa();
    </script>
</body>
</html>
