<?php
session_start();
include_once "navbar.php";
include_once "servicios/conexion.php"; // Conexión a la base de datos

// Verificar si el usuario está autenticado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];

// Consultar la lista de usuarios disponibles (excepto el usuario actual)
$sql = "SELECT id_usuario, nombre, fotoperfil 
        FROM usuarios 
        WHERE dado_de_baja = 'no' AND id_usuario != ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario_actual);
$stmt->execute();
$result = $stmt->get_result();

// Manejo del envío del formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $destinatario_id = intval($_POST['destinatario']);
    $mensaje = trim($_POST['mensaje']);
    $fecha_envio = date("Y-m-d H:i:s");

    if (!empty($destinatario_id) && !empty($mensaje)) {
        $sql_insert = "INSERT INTO mensajes (remitente_id, destinatario_id, mensaje, fecha_envio) VALUES (?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("iiss", $id_usuario, $destinatario_id, $mensaje, $fecha_envio);

        if ($stmt_insert->execute()) {
            echo "<script>alert('Mensaje enviado exitosamente.'); window.location.href='bandeja_mensajes.php';</script>";
        } else {
            echo "<script>alert('Hubo un error al enviar el mensaje.');</script>";
        }
    } else {
        echo "<script>alert('Por favor, complete todos los campos.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Mensaje</title>
    <link rel="stylesheet" href="css/crear_mensaje.css">
</head>
<body>
<div class="contenedor">
    <h1>Enviar Mensaje</h1>
    <form action="enviar_mensaje.php" method="POST">
        <label for="receptor">Selecciona un usuario:</label>
        <select name="receptor" id="receptor" required>
            <option value="">-- Seleccionar Usuario --</option>
            <?php while ($row = $result->fetch_assoc()): ?>
                <option value="<?= $row['id_usuario']; ?>">
                    <div class="user-option">
                        <img src="fotos_perfil/<?= htmlspecialchars($row['fotoperfil']); ?>" 
                             alt="<?= htmlspecialchars($row['nombre']); ?>" 
                             class="profile-picture">
                        <?= htmlspecialchars($row['nombre']); ?>
                    </div>
                </option>
            <?php endwhile; ?>
        </select>
        
        <label for="contenido">Mensaje:</label>
        <textarea name="contenido" id="contenido" rows="5" required></textarea>
        
        <button type="submit">Enviar</button>
    </form>
</div>
</body>
</html>
