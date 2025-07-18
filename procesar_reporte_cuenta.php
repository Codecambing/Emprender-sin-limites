<?php
session_start();
include_once "servicios/conexion.php";

// Verificar que el usuario haya iniciado sesión
if (!isset($_SESSION['id_usuario'])) {
    echo "<script>alert('Por favor, inicia sesión para reportar un usuario.');</script>";
    header("Location: login.php");
    exit();
}

// Verificar si se enviaron los datos necesarios
if (isset($_POST['usuario_reportado'], $_POST['motivo'], $_POST['usuario_que_reporta'])) {
    $usuario_reportado = intval($_POST['usuario_reportado']);
    $motivo = mysqli_real_escape_string($conn, $_POST['motivo']);
    $usuario_que_reporta = intval($_POST['usuario_que_reporta']);
    $fecha_reporte = date("Y-m-d H:i:s");

    // Obtener el nombre del usuario reportado desde la base de datos
    $query = "SELECT nombre FROM usuarios WHERE id_usuario = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $usuario_reportado);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $nombre_usuario_reportado = $row['nombre'];

        // Insertar el reporte en la base de datos
        $sql = "INSERT INTO reportes_usuarios (usuario_reportado, nombre_usuario_reportado, motivo, usuario_que_reporta, fecha_reporte)
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issis", $usuario_reportado, $nombre_usuario_reportado, $motivo, $usuario_que_reporta, $fecha_reporte);

        if ($stmt->execute()) {
            echo "<script>alert('El reporte se ha enviado correctamente.');</script>";
        } else {
            echo "<script>alert('Error al enviar el reporte.');</script>";
        }
    } else {
        echo "<script>alert('El usuario reportado no existe.');</script>";
    }
} else {
    echo "<script>alert('Faltan datos para realizar el reporte.');</script>";
}

header("Location: lista_usuarios.php");
exit();
?>
