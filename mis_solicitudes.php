<?php
session_start();
include_once "navbar.php";
include_once "servicios/conexion.php";

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['id_usuario'])) {
    die("Por favor, inicia sesión para ver tus solicitudes.");
}

$id_usuario = $_SESSION['id_usuario'];

// Consultar las solicitudes de retiro de los proyectos creados por el usuario
$query = "
    SELECT sr.id_solicitud_retiro, sr.fecha_solicitud, sr.estado, p.nombre_proyecto, sr.fecha_aceptacion, sr.comprobante, p.id_proyectos
    FROM solicitudes_retiro sr
    JOIN proyectos p ON sr.proyectos_id_proyecto = p.id_proyectos
    WHERE p.usuarios_id_usuario = ?
    ORDER BY sr.fecha_solicitud DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();

// Generar la vista
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Solicitudes de Retiro</title>
    <link rel="stylesheet" href="css/mis_solicitudes.css"> <!-- Agregar el archivo CSS aquí -->
</head>
<body>
    <div class="container">
        <h1 class="titulo">Mis Solicitudes de Retiro</h1>
        <?php if ($result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID Solicitud</th>
                        <th>Proyecto</th>
                        <th>Fecha de Solicitud</th>
                        <th>Estado</th>
                        <th>Fecha de Aceptación</th>
                        <th>Comprobante</th>
                        <th>Inversores</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id_solicitud_retiro']; ?></td>
                            <td><?php echo htmlspecialchars($row['nombre_proyecto']); ?></td>
                            <td><?php echo $row['fecha_solicitud']; ?></td>
                            <td><?php echo ucfirst($row['estado']); ?></td>
                            <td><?php echo $row['fecha_aceptacion'] ?? 'Pendiente'; ?></td>
                            <td>
                                <?php if ($row['comprobante']): ?>
                                    <a href="<?php echo htmlspecialchars($row['comprobante']); ?>" target="_blank">Ver comprobante</a>
                                <?php else: ?>
                                    No disponible
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                // Ruta del archivo .txt con la lista de inversionistas
                                $archivo_txt = "comprobantes/inversionistas_proyecto_" . $row['id_proyectos'] . ".txt";
                                if (file_exists($archivo_txt)): ?>
                                    <a href="<?php echo $archivo_txt; ?>" download>Descargar .txt</a>
                                <?php else: ?>
                                    No disponible
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-solicitudes">No tienes solicitudes de retiro.</div>
        <?php endif; ?>
    </div>
</body>
</html>
