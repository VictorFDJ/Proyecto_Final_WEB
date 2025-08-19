<?php
include 'config.php';
$conn = conectarDB();

$provincia = $_GET['provincia'] ?? '';
$tipo = $_GET['tipo'] ?? '';
$desde = $_GET['desde'] ?? '';
$hasta = $_GET['hasta'] ?? '';

// Consulta principal con JOINs
$query = "SELECT i.id, i.titulo, i.descripcion, i.latitud, i.longitud, i.fecha,
                 GROUP_CONCAT(t.nombre) AS tipos
          FROM incidencias i
          LEFT JOIN incidencia_tipos it ON i.id = it.incidencia_id
          LEFT JOIN tipos_incidencias t ON it.tipo_id = t.id
          WHERE i.estado = 'aprobado'"; // solo incidencias aprobadas

if ($provincia != '') {
    $query .= " AND i.provincia = '" . $conn->real_escape_string($provincia) . "'";
}
if ($tipo != '') {
    $query .= " AND i.id IN (
                    SELECT incidencia_id FROM incidencia_tipos WHERE tipo_id = " . intval($tipo) . "
                )";
}
if ($desde != '') {
    $query .= " AND i.fecha >= '" . $conn->real_escape_string($desde) . " 00:00:00'";
}
if ($hasta != '') {
    $query .= " AND i.fecha <= '" . $conn->real_escape_string($hasta) . " 23:59:59'";
}

$query .= " GROUP BY i.id";

$result = $conn->query($query);
$incidencias = [];

while ($row = $result->fetch_assoc()) {
    // Convertir tipos en array
    $row['tipos'] = $row['tipos'] ? explode(",", $row['tipos']) : [];
    $incidencias[] = $row;
}

header('Content-Type: application/json');
echo json_encode($incidencias);
$conn->close();

