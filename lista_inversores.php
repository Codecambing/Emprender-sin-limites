<?php
session_start();
include_once "navbar.php";
include_once "servicios/conexion.php";

// Verificar si el usuario está logueado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

// Verificar si se recibió un ID de proyecto
if (!isset($_GET['id'])) {
    die("ID de proyecto no proporcionado.");
}

$id_proyecto = intval($_GET['id']);

// Consultar los datos del proyecto
$sql_proyecto = "SELECT nombre_proyecto FROM proyectos WHERE id_proyectos = ?";
$stmt_proyecto = $conn->prepare($sql_proyecto);
$stmt_proyecto->bind_param("i", $id_proyecto);
$stmt_proyecto->execute();
$result_proyecto = $stmt_proyecto->get_result();

if ($result_proyecto->num_rows === 0) {
    die("El proyecto no existe.");
}

$proyecto = $result_proyecto->fetch_assoc();

// Consultar los inversores del proyecto
$sql_inversores = "
    SELECT 
        u.nombre AS nombre_inversor,
        u.fotoperfil,
        u.correo,
        ip.monto,
        ip.recompensa,
        ip.fecha_contribucion
    FROM inversion_proyectos ip
    JOIN usuarios u ON ip.usuarios_id_usuario = u.id_usuario
    WHERE ip.proyectos_id_proyectos = ?
    ORDER BY ip.fecha_contribucion DESC";

$stmt_inversores = $conn->prepare($sql_inversores);
$stmt_inversores->bind_param("i", $id_proyecto);
$stmt_inversores->execute();
$result_inversores = $stmt_inversores->get_result();

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Inversores - <?= htmlspecialchars($proyecto['nombre_proyecto']); ?></title>
    <link rel="stylesheet" href="css/lista_inversores.css">
</head>
<body>
<div class="investor-list-container">
    <h1>Inversores del Proyecto: <?= htmlspecialchars($proyecto['nombre_proyecto']); ?></h1>
    <?php if ($result_inversores->num_rows > 0): ?>
        <table class="investor-list-table">
            <thead>
                <tr>
                    <th>Foto de Perfil</th>
                    <th>Nombre</th>
                    <th>Correo</th>
                    <th>Monto Invertido</th>
                    <th>Monto con Comisión (3%)</th>
                    <th>Recompensa</th>
                    <th>Fecha de Inversión</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($inversor = $result_inversores->fetch_assoc()): 
                    $monto_con_comision = $inversor['monto'] * 0.97; // Calcular monto con comisión
                ?>
                    <tr>
                        <td>
                            <img src="<?= htmlspecialchars($inversor['fotoperfil'] ?? 'pfp/default-profile.jpg'); ?>" alt="Foto de Perfil" class="profile-pic">
                        </td>
                        <td><?= htmlspecialchars($inversor['nombre_inversor']); ?></td>
                        <td><?= htmlspecialchars($inversor['correo']); ?></td>
                        <td><?= number_format($inversor['monto'], 0, ',', '.'); ?>$</td>
                        <td><?= number_format($monto_con_comision, 0, ',', '.'); ?>$</td>
                        <td><?= htmlspecialchars($inversor['recompensa'] ?? 'Sin recompensa'); ?></td>
                        <td><?= date("d/m/Y", strtotime($inversor['fecha_contribucion'])); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No hay inversores en este proyecto aún.</p>
    <?php endif; ?>
</div>
</body>
</html>
