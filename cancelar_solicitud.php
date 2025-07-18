<?php
session_start();
include_once "servicios/conexion.php";

// Verificar si el usuario está logueado
if (!isset($_SESSION['id_usuario'])) {
    echo "<script>alert('Por favor, inicia sesión para continuar.');</script>";
    header("Location: login.php");
    exit();
}

// Verificar si se ha recibido el id_solicitud
if (!isset($_GET['id_solicitud'])) {
    die("Solicitud no válida.");
}

$id_solicitud = $_GET['id_solicitud'];

// Actualizar el estado de la solicitud a "rechazado"
$query = "UPDATE solicitudes_retiro SET estado = 'rechazado' WHERE id_solicitud_retiro = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_solicitud);

if ($stmt->execute()) {
    echo "<script>alert('Solicitud rechazada correctamente.'); window.location.href = 'revision_solicitudes.php';</script>";
} else {
    echo "<script>alert('Error al rechazar la solicitud.'); window.location.href = 'revision_solicitudes.php';</script>";
}

$stmt->close();
$conn->close();
?>
