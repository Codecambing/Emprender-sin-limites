<?php
session_start();
include_once "navbar.php";
include_once "servicios/conexion.php";

// Verificar si el usuario está autenticado
if (!isset($_SESSION['id_usuario'])) {
    echo "<script>alert('Por favor inicie sesión');</script>";
    header("Location: login.php");
    exit();
}

$idUsuario = $_SESSION['id_usuario'];

// Obtener mensajes recibidos
$sql = "SELECT mensajes.id_mensaje, mensajes.mensaje, mensajes.fecha_envio, usuarios.nombre_usuario AS remitente_id, usuarios.fotoperfil 
        FROM mensajes
        INNER JOIN usuarios ON mensajes.remitente_id = usuarios.id_usuario
        WHERE mensajes.destinatario_id = ?
        ORDER BY mensajes.fecha_envio DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idUsuario);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bandeja de Mensajes</title>
    <link rel="stylesheet" href="css/bandeja_mensajes.css">
</head>
<body>
    <div class="contenedor">
        <h1>Bandeja de Entrada</h1>
        <?php if ($result->num_rows > 0): ?>
            <ul class="lista-mensajes">
                <?php while ($mensaje = $result->fetch_assoc()): ?>
                    <li class="mensaje-item">
                        <div class="mensaje-header">
                            <img src="<?= htmlspecialchars($mensaje['fotoperfil'] ?? 'pfp/default-profile.jpg'); ?>" alt="Foto de perfil"  class="foto-perfil">
                            <h3>De: <?= htmlspecialchars($mensaje['remitente_id']) ?></h3>
                        </div>
                        <p><?= htmlspecialchars($mensaje['mensaje']) ?></p>
                        <small>Enviado el: <?= date("d/m/Y H:i", strtotime($mensaje['fecha_envio'])) ?></small>
                        <a href="chat.php?receptor=<?= $mensaje['remitente_id'] ?>" class="btn-responder">Responder</a>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p>No tienes mensajes en tu bandeja de entrada.</p>
            <a href="crear_mensaje.php" class="btn-crear-mensaje">Crear Mensaje</a>
        <?php endif; ?>
    </div>
</body>
</html>
