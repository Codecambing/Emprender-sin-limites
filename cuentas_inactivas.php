<?php
session_start();
include_once "navbar.php";
include_once "servicios/conexion.php";

// Verificar si el usuario está logueado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

// Obtener el ID del usuario desde la sesión
$id_usuario = intval($_SESSION['id_usuario']);

// Consultar los privilegios del usuario
$sql_privilegios = "SELECT privilegio FROM usuarios WHERE id_usuario = ?";
$stmt = $conn->prepare($sql_privilegios);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Usuario no encontrado en la base de datos.");
}

// **Filtrar solo cuentas desactivadas (`dado_de_baja = 'si')**
$sql = "SELECT id_usuario, nombre, fotoperfil FROM usuarios WHERE dado_de_baja = 'si'";
$result = $conn->query($sql);

// Verificar si se obtuvieron usuarios desactivados
$usuarios = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $usuarios[] = $row;
    }
} else {
    echo "No hay cuentas desactivadas disponibles.";
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cuentas Desactivadas</title>
    <link rel="stylesheet" href="css/cuentas_inactivas.css">
</head>
<body>
    <div class="user-list-container">
        <h1>Cuentas Desactivadas</h1>
        <div class="user-list">
    <?php if (!empty($usuarios)): ?>
        <?php foreach ($usuarios as $user): ?>
            <div class="user-item">
                <img src="<?= htmlspecialchars($user['fotoperfil']) ?: 'pfp/default-profile.jpg'; ?>" alt="Foto de perfil de <?= htmlspecialchars($user['nombre']); ?>" class="user-photo">
                <p class="user-name"><?= htmlspecialchars($user['nombre']); ?></p>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="user-item">
            <p class="user-name">No hay cuentas desactivadas.</p>
        </div>
    <?php endif; ?>
</div>
    </div>
</body>
</html>
