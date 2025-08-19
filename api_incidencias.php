<?php
include 'config.php';
$conn = conectarDB();

$where = "WHERE estado = 'aprobado' AND fecha >= DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
$params = [];
$types = '';

if (isset($_GET['provincia']) && $_GET['provincia']) {
    $where .= " AND provincia_id = ?";
    $params[] = $_GET['provincia'];
    $types .= 'i';
}
if (isset($_GET['tipo']) && $_GET['tipo']) {
    $where .= " AND EXISTS (SELECT 1 FROM incidencia_tipos it WHERE it.incidencia_id = i.id AND it.tipo_id = ?)";
    $params[] = $_GET['tipo'];
    $types .= 'i';
}
if (isset($_GET['desde']) && $_GET['desde']) {
    $where .= " AND fecha >= ?";
    $params[] = $_GET['desde'];
    $types .= 's';
}
if (isset($_GET['hasta']) && $_GET['hasta']) {
    $where .= " AND fecha <= ?";
    $params[] = $_GET['hasta'];
    $types .= 's';
}

$sql = "SELECT i.*, GROUP_CONCAT(t.nombre SEPARATOR ', ') as tipos 
        FROM incidencias i
        LEFT JOIN incidencia_tipos it ON it.incidencia_id = i.id
        LEFT JOIN tipos_incidencias t ON t.id = it.tipo_id
        $where GROUP BY i.id";
$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$data = [];
while ($row = $result->fetch_assoc()) {
    $row['tipos'] = explode(', ', $row['tipos']);
    $data[] = $row;
}
$conn->close();
header('Content-Type: application/json');
echo json_encode($data);
?>