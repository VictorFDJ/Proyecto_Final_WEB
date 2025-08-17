<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '1234@AA.');
define('DB_NAME', 'incidencias_db');

function conectarDB() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die("Conexión fallida: " . $conn->connect_error);
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}

// Inicia sesión solo si no está iniciada aún
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getUserRole() {
    return isset($_SESSION['rol']) ? $_SESSION['rol'] : null;
}
?>
