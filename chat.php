<?php
session_start();
include_once "navbar.php";
include_once "servicios/conexion.php";

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

$id_usuario_actual = $_SESSION['id_usuario'];

// Obtener los mensajes entre el usuario actual y el receptor
$sql = "SELECT m.id_mensaje, m.remitente_id, m.destinatario_id, m.mensaje, m.fecha_envio, u.nombre_usuario, u.fotoperfil
        FROM mensajes AS m
        INNER JOIN usuarios AS u ON m.remitente_id = u.id_usuario
        WHERE (m.remitente_id = ? AND m.destinatario_id = ?)
           OR (m.remitente_id = ? AND m.destinatario_id = ?)
        ORDER BY m.fecha_envio ASC";

$receptor_id = isset($_GET['remitente_id']) ? intval($_GET['remitente_id']) : 0;
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiii", $id_usuario_actual, $receptor_id, $receptor_id, $id_usuario_actual);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat</title>
    <link rel="stylesheet" href="css/chat.css">
</head>
<body>
    <div class="chat-wrapper">
        <div class="chat-container">
            <div class="chat-header">
                <h2>Conversación con <?= htmlspecialchars($receptor_id); ?></h2>
            </div>

            <div class="chat-messages">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="message <?= $row['remitente_id'] == $id_usuario_actual ? 'sent' : 'received'; ?>">
                        <div class="message-header">
                            <img src="fotos_perfil/<?= htmlspecialchars($row['fotoperfil']); ?>" 
                                 alt="<?= htmlspecialchars($row['nombre_usuario']); ?>" 
                                 class="profile-picture">
                            <strong><?= htmlspecialchars($row['nombre_usuario']); ?></strong>
                            <span class="timestamp"><?= date("d/m/Y H:i", strtotime($row['fecha_envio'])); ?></span>
                        </div>
                        <div class="message-content">
                            <p><?= htmlspecialchars($row['mensaje']); ?></p>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
            
            <form action="enviar_mensaje.php" method="POST" class="chat-form">
                <input type="hidden" name="receptor" value="<?= $receptor_id; ?>">
                <textarea name="contenido" rows="3" placeholder="Escribe un mensaje..." required></textarea>
                <button type="submit">Responder</button>
            </form>
        </div>
    </div>
</body>
</html>
