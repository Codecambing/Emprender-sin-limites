<?php
session_start();
include_once "servicios/conexion.php";

// Verificar que el usuario haya iniciado sesión
if (!isset($_SESSION['id_usuario'])) {
    echo "<script>alert('Por favor inicie sesión');</script>";
    header("Location: login.php");
    exit();
}

if (isset($_GET['id_experiencia'])) {
    $id_experiencia = $_GET['id_experiencia'];
    $id_usuario = $_SESSION['id_usuario'];

    // Consultar si la experiencia pertenece al usuario
    $check_query = "SELECT id_experiencias FROM experiencias WHERE id_experiencias = '$id_experiencia' AND usuarios_id_usuario = '$id_usuario'";
    $check_result = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        // Actualizar la fecha de baja en lugar de eliminar la experiencia
        $fecha_baja = date("Y-m-d H:i:s");
        $update_query = "UPDATE experiencias SET fecha_baja = '$fecha_baja' WHERE id_experiencias = '$id_experiencia'";

        if (mysqli_query($conn, $update_query)) {
            echo "<script>alert('La experiencia ha sido dada de baja correctamente.');</script>";
        } else {
            echo "<script>alert('Error al dar de baja la experiencia.');</script>";
        }
    } else {
        echo "<script>alert('Esta experiencia no te pertenece o no existe.');</script>";
    }
} else {
    echo "<script>alert('No se ha especificado la experiencia.');</script>";
}

header("Location: perfil.php");  // Redirigir al perfil
exit();
?>
