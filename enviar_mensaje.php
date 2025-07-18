<?php
session_start();
include_once "servicios/conexion.php";

// Verificar si el usuario está autenticado
if (!isset($_SESSION['id_usuario'])) {
    echo "<script>alert('Por favor inicie sesión');</script>";
    header("Location: login.php");
    exit();
}

// Obtener el id del usuario actual
$id_usuario_actual = $_SESSION['id_usuario'];

// Verificar si se ha enviado el mensaje
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener el receptor del mensaje y el contenido
    $receptor_id = isset($_POST['receptor']) ? intval($_POST['receptor']) : 0;
    $contenido = isset($_POST['contenido']) ? trim($_POST['contenido']) : '';

    // Validar los datos
    if (empty($contenido)) {
        echo "<script>alert('El contenido del mensaje no puede estar vacío');</script>";
    } else {
        // Insertar el mensaje en la base de datos
        $sql = "INSERT INTO mensajes (remitente_id, destinatario_id, mensaje, fecha_envio) 
                VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iis", $id_usuario_actual, $receptor_id, $contenido);
        
        if ($stmt->execute()) {
            // Redirigir a la conversación con el receptor después de enviar el mensaje
            header("Location: chat.php?receptor=" . $receptor_id);
            exit();
        } else {
            echo "<script>alert('Error al enviar el mensaje');</script>";
        }
    }
}
?>
