<?php
session_start();
include_once "servicios/conexion.php";

// Verificar si el usuario está logueado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

// Verificar si se recibió un ID de proyecto
if (!isset($_GET['id'])) {
    echo "Error: No se proporcionó el ID del proyecto.";
    exit();
}

$id_proyecto = $_GET['id'];
$id_usuario = $_SESSION['id_usuario'];

// Verificar si el proyecto pertenece al usuario logueado
$sql_verificar = "SELECT * FROM proyectos WHERE id_proyectos = ? AND usuarios_id_usuario = ?";
$stmt_verificar = $conn->prepare($sql_verificar);
$stmt_verificar->bind_param("ii", $id_proyecto, $id_usuario);
$stmt_verificar->execute();
$result_verificar = $stmt_verificar->get_result();

if ($result_verificar->num_rows === 0) {
    echo "Error: No tienes permiso para eliminar este proyecto o no existe.";
    exit();
}

// Eliminar el proyecto
$sql_eliminar = "DELETE FROM proyectos WHERE id_proyectos = ?";
$stmt_eliminar = $conn->prepare($sql_eliminar);
$stmt_eliminar->bind_param("i", $id_proyecto);

if ($stmt_eliminar->execute()) {
    header("Location: lista_proyectos.php?mensaje=proyecto_eliminado");
    exit();
} else {
    echo "Error al eliminar el proyecto.";
}
?>