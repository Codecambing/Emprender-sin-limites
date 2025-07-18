<?php
$servername = "localhost"; // Cambia según tu configuración
$username = "root";        // Cambia según tu configuración
$password_db = "";            // Cambia según tu configuración
$dbname = "emprender"; // Cambia según tu configuración

// Crear la conexión
$conn = new mysqli($servername, $username, $password_db, $dbname);

// Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
?>