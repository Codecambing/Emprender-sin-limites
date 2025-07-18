<?php
session_start();
include_once "servicios/conexion.php";

date_default_timezone_set('America/Santiago');

// Verificar si el usuario está logueado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$id_proyecto = intval($_POST['id_proyecto']);
$calificacion = intval($_POST['calificacion']);
$comentario = trim($_POST['comentario']);

// Formato d-m-Y H:i:s
$fecha_valoracion = date("Y-m-d H:i:s");

if (empty($calificacion) || empty($comentario)) {
    die("Todos los campos son obligatorios.");
}

// Consulta para insertar el comentario
$sql = "INSERT INTO valoraciones 
        (usuarios_id_usuario, proyectos_id_proyectos, calificacion, comentario, fecha_valoracion) 
        VALUES (?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iiiss", $id_usuario, $id_proyecto, $calificacion, $comentario, $fecha_valoracion);

if ($stmt->execute()) {
    echo "<script>alert('Comentario agregado con éxito.'); window.location.href = 'publicacion_proyecto.php?id=" . $id_proyecto . "';</script>";
    exit();
} else {
    echo "Error al agregar el comentario: " . $stmt->error;
}
?>
