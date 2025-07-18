<?php
session_start();
include_once "servicios/conexion.php";

// Verificar si el usuario está logueado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

if (isset($_POST['eliminar_comentario'])) {
    $comentario_id = intval($_POST['comentario_id']);
    $id_usuario_actual = $_SESSION['id_usuario']; // ID del usuario logueado
    
    // Primero, obtener la ID del proyecto asociado al comentario
    $sql_proyecto = "SELECT proyectos_id_proyectos FROM valoraciones WHERE id_valoracion = ?";
    $stmt_proyecto = $conn->prepare($sql_proyecto);
    $stmt_proyecto->bind_param("i", $comentario_id);
    $stmt_proyecto->execute();
    $result_proyecto = $stmt_proyecto->get_result();
    
    // Verificar que el comentario existe
    if ($result_proyecto->num_rows > 0) {
        // Obtener la ID del proyecto
        $row_proyecto = $result_proyecto->fetch_assoc();
        $id_proyecto = $row_proyecto['proyectos_id_proyectos'];

        // Actualizar el estado del comentario a 'ocultado' y registrar la fecha de baja
        $sql = "UPDATE valoraciones 
                SET ocultado = 1, fecha_baja = NOW() 
                WHERE id_valoracion = ? AND usuarios_id_usuario = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $comentario_id, $id_usuario_actual);
        
        if ($stmt->execute()) {
            // Redirigir a la página del proyecto
            echo "<script>alert('Comentario eliminado con éxito.'); window.location.href = 'publicacion_proyecto.php?id=" . $id_proyecto . "';</script>";
        } else {
            echo "<script>alert('Error al eliminar el comentario.'); window.location.href = 'publicacion_proyecto.php?id=" . $id_proyecto . "';</script>";
        }
    } else {
        // Si no se encuentra el comentario
        echo "<script>alert('Comentario no encontrado.'); window.location.href = 'publicacion_proyecto.php';</script>";
    }
}
?>
