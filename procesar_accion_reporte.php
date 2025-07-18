<?php
session_start();
include_once "servicios/conexion.php";

// Verificar si los datos necesarios están presentes
if (!isset($_POST['id_reporte'], $_POST['accion'])) {
    header("Location: reportes_de_cuenta.php");
    exit();
}

$id_reporte = intval($_POST['id_reporte']);
$accion = $_POST['accion'];

if ($accion === 'dar_baja') {
    // Actualizar el estado del reporte a 'confirmado'
    $query_actualizar_reporte = "UPDATE reportes_usuarios SET estado = 'confirmado' WHERE id_reportes_usuarios = ?";
    $stmt_reporte = $conn->prepare($query_actualizar_reporte);
    $stmt_reporte->bind_param("i", $id_reporte);
    $stmt_reporte->execute();

    // Obtener el id_usuario_reportado
    $query_usuario_reportado = "SELECT usuario_reportado FROM reportes_usuarios WHERE id_reportes_usuarios = ?";
    $stmt_usuario = $conn->prepare($query_usuario_reportado);
    $stmt_usuario->bind_param("i", $id_reporte);
    $stmt_usuario->execute();
    $result_usuario = $stmt_usuario->get_result();
    
    if ($result_usuario->num_rows > 0) {
        $row = $result_usuario->fetch_assoc();
        $id_usuario_reportado = $row['usuario_reportado'];

        // Actualizar el estado del usuario reportado a 'dado de baja' y registrar la fecha
        $fecha_baja = date("Y-m-d H:i:s");
        $query_actualizar_usuario = "UPDATE usuarios SET dado_de_baja = 'SI', fecha_baja = ? WHERE id_usuario = ?";
        $stmt_usuario_baja = $conn->prepare($query_actualizar_usuario);
        $stmt_usuario_baja->bind_param("si", $fecha_baja, $id_usuario_reportado);
        $stmt_usuario_baja->execute();
    }
} elseif ($accion === 'cancelar_reporte') {
    // Cancelar el reporte, estableciendo el estado como 'cancelado'
    $query_cancelar_reporte = "UPDATE reportes_usuarios SET estado = 'cancelado' WHERE id_reportes_usuarios = ?";
    $stmt_cancelar = $conn->prepare($query_cancelar_reporte);
    $stmt_cancelar->bind_param("i", $id_reporte);
    $stmt_cancelar->execute();
}

header("Location: reportes_de_cuentas.php");
exit();
?>
