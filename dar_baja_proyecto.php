<?php
session_start();
include_once "servicios/conexion.php"; // Conexión a la base de datos

// Verificar si el usuario está logueado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

$id_usuario_actual = $_SESSION['id_usuario'];

// Verificar si se recibió el ID del proyecto
if (!isset($_GET['id'])) {
    die("No se especificó el ID del proyecto.");
}

$id_proyecto = intval($_GET['id']);

// Validar si el usuario actual es el propietario del proyecto
$sql_validar = "SELECT id_proyectos FROM proyectos WHERE id_proyectos = ? AND usuarios_id_usuario = ?";
$stmt_validar = $conn->prepare($sql_validar);
$stmt_validar->bind_param("ii", $id_proyecto, $id_usuario_actual);
$stmt_validar->execute();
$result_validar = $stmt_validar->get_result();

if ($result_validar->num_rows === 0) {
    die("No tienes permiso para dar de baja este proyecto.");
}

// Actualizar el estado del proyecto en la base de datos
$sql_dar_baja = "UPDATE proyectos SET dado_de_baja = 'SI', fecha_baja = NOW() WHERE id_proyectos = ?";
$stmt_dar_baja = $conn->prepare($sql_dar_baja);
$stmt_dar_baja->bind_param("i", $id_proyecto);

if ($stmt_dar_baja->execute()) {
    echo "El proyecto ha sido dado de baja con éxito.";
    header("Location: mis_proyectos.php"); // Redirigir a una página de listado de proyectos
    exit();
} else {
    echo "Ocurrió un error al intentar dar de baja el proyecto.";
}
?>
