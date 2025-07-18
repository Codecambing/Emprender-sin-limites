<?php
session_start();
include_once "navbar.php";
include_once "servicios/conexion.php"; // Conexión a la base de datos

// Verificar si el usuario está logueado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

$id_usuario_actual = $_SESSION['id_usuario']; // ID del usuario logueado

// Consultar las inversiones realizadas por el usuario
$sql = "
    SELECT 
        p.nombre_proyecto, 
        u.nombre AS creador_proyecto, 
        ip.monto, 
        ip.fecha_contribucion, 
        ip.tipo_transaccion_id_tipo_transaccion, 
        ip.numero_transaccion, 
        ip.recompensa, 
        ip.id_inversion,
        ip.fecha_anulacion,
        ip.estado
    FROM inversion_proyectos ip
    JOIN proyectos p ON ip.proyectos_id_proyectos = p.id_proyectos  
    JOIN usuarios u ON p.usuarios_id_usuario = u.id_usuario
    WHERE ip.usuarios_id_usuario = ?
    ORDER BY ip.fecha_contribucion DESC";



$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario_actual);
$stmt->execute();
$result = $stmt->get_result();

// Verificar si hay resultados
if ($result->num_rows == 0) {
    $mensaje = "No has realizado inversiones.";
} else {
    $inversiones = $result->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Inversiones</title>
    <link rel="stylesheet" href="css/inversiones_personales.css">
</head>
<body>
<div class="investments-container">
    <h1>Mis Inversiones</h1>
    <?php if (isset($mensaje)): ?>
        <p><?= htmlspecialchars($mensaje); ?></p>
    <?php else: ?>
        <table class="investment-table">
            <thead>
                <tr>
                    <th>Nombre del Proyecto</th>
                    <th>Creador</th>
                    <th>Monto</th>
                    <th>Fecha de Contribución</th>
                    <th>Tipo de Transacción</th>
                    <th>Código de Transacción</th>
                    <th>Recompensa</th>
                    <th>Estado</th>
                    <th>Fecha de Anulación</th>
                    <th>Anular</th>
                </tr>
            </thead>
            <tbody>
    <?php foreach ($inversiones as $inversion): ?>
        <tr>
            <td><?= htmlspecialchars($inversion['nombre_proyecto']); ?></td>
            <td><?= htmlspecialchars($inversion['creador_proyecto']); ?></td>
            <td><?= number_format($inversion['monto']); ?>$</td>
            <td><?= date("d/m/Y", strtotime($inversion['fecha_contribucion'])); ?></td>
            <td>
                <?php 
                    if ($inversion['tipo_transaccion_id_tipo_transaccion'] == 1) {
                        echo "Transferencia Bancaria";
                    } else if ($inversion['tipo_transaccion_id_tipo_transaccion'] == 2) {
                        echo "PayPal";
                    } else {
                        echo "Desconocido";
                    }
                ?>
            </td>
            <td><?= htmlspecialchars($inversion['numero_transaccion']); ?></td>
            <td><?= htmlspecialchars($inversion['recompensa'] ?? "Sin recompensa"); ?></td>
            <td>
                <?= htmlspecialchars($inversion['estado'] ?? "Desconocido"); ?>
            </td>
            <td>
                <?= $inversion['fecha_anulacion'] ? date("d/m/Y", strtotime($inversion['fecha_anulacion'])) : ""; ?>
            </td>
            <td>
                <?php if (!$inversion['fecha_anulacion'] && $inversion['estado'] == "ACTIVO"): ?>
                    <button class="cancel-button" onclick="anularInversion(<?= $inversion['id_inversion']; ?>)">Anular</button>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
</tbody>

        </table>
    <?php endif; ?>
</div>

<script>
function anularInversion(idInversion) {
    if (confirm("¿Estás seguro de que deseas anular esta inversión?")) {
        window.location.href = `anular_inversion.php?id=${idInversion}`;
    }
}
</script>
</body>
</html>
