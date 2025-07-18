<?php
session_start();
include_once "servicios/conexion.php";

// Verificar que el usuario haya iniciado sesión
if (!isset($_SESSION['id_usuario'])) {
    echo "<script>alert('Por favor inicie sesión');</script>";
    header("Location: login.php");
    exit();
}

if (isset($_GET['id_comentario'])) {
    $id_comentario = $_GET['id_comentario'];
    $id_usuario = $_SESSION['id_usuario'];

    // Consultar si el comentario pertenece al usuario
    $check_query = "SELECT id_valoracion FROM valoraciones WHERE id_valoracion = '$id_comentario' AND usuarios_id_usuario = '$id_usuario'";
    $check_result = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        // Actualizar la columna "ocultado" a 1 en lugar de eliminar el comentario
        $update_query = "UPDATE valoraciones SET ocultado = 1 WHERE id_valoracion = '$id_comentario'";

        if (mysqli_query($conn, $update_query)) {
            echo "<script>alert('El comentario ha sido ocultado correctamente.');</script>";
        } else {
            echo "<script>alert('Error al ocultar el comentario.');</script>";
        }
    } else {
        echo "<script>alert('Este comentario no te pertenece o no existe.');</script>";
    }
} else {
    echo "<script>alert('No se ha especificado el comentario.');</script>";
}

header("Location: publicacion_proyecto.php?id=" . $_GET['id_proyecto']);  // Redirigir a la página del proyecto
exit();
?>
