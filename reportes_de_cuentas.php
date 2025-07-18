<?php
session_start();
include_once "navbar.php";
include_once "servicios/conexion.php";

// Verificar que el usuario haya iniciado sesión
if (!isset($_SESSION['id_usuario'])) {
    echo "<script>alert('Por favor, inicia sesión.'); window.location.href='login.php';</script>";
    exit();
}

// Verificar que el usuario sea administrador
$id_usuario_logueado = $_SESSION['id_usuario'];
$query_admin = "SELECT privilegio FROM usuarios WHERE id_usuario = ?";
$stmt = $conn->prepare($query_admin);
$stmt->bind_param("i", $id_usuario_logueado);
$stmt->execute();
$result_admin = $stmt->get_result();
$logged_user = $result_admin->fetch_assoc();

if (!$logged_user || $logged_user['privilegio'] !== 'Administrador') {
    echo "<script>alert('No tienes permiso para acceder a esta página, volveras a la pagina de inicio.'); window.location.href='inicio.php';</script>";
    exit();
}

$query_pendientes = "
    SELECT r.id_reportes_usuarios, r.nombre_usuario_reportado, r.motivo, u.nombre AS usuario_que_reporta, r.fecha_reporte
    FROM reportes_usuarios r
    JOIN usuarios u ON r.usuario_que_reporta = u.id_usuario
    WHERE r.estado = 'pendiente'
";
$result_pendientes = mysqli_query($conn, $query_pendientes);

// Obtener los reportes confirmados con el nombre del usuario que reportó
$query_confirmados = "
    SELECT r.id_reportes_usuarios, r.nombre_usuario_reportado, r.motivo, u.nombre AS usuario_que_reporta, r.fecha_reporte
    FROM reportes_usuarios r
    JOIN usuarios u ON r.usuario_que_reporta = u.id_usuario
    WHERE r.estado = 'confirmado'
";
$result_confirmados = mysqli_query($conn, $query_confirmados);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Reportes</title>
    <link rel="stylesheet" href="css/reportes_de_cuenta.css">
    <script>
        function cambiarPestaña(pestaña) {
            document.querySelectorAll('.contenido-pestaña').forEach(content => {
                content.style.display = 'none';
            });
            document.getElementById(pestaña).style.display = 'block';

            document.querySelectorAll('.boton-pestaña').forEach(button => {
                button.classList.remove('activo');
            });
            document.querySelector(`[data-tab="${pestaña}"]`).classList.add('activo');
        }
    </script>
</head>
<body>
    <div class="contenedor-reportes">
        <h1 class="titulo-reportes">Gestión de Reportes de Cuentas</h1>
        <div class="pestañas">
            <button class="boton-pestaña activo" data-tab="pendientes" onclick="cambiarPestaña('pendientes')">Pendientes</button>
            <button class="boton-pestaña" data-tab="confirmados" onclick="cambiarPestaña('confirmados')">Confirmados</button>
        </div>
        <div id="pendientes" class="contenido-pestaña" style="display: block;">
            <h2 class="subtitulo-reportes">Reportes Pendientes</h2>
            <?php if (mysqli_num_rows($result_pendientes) > 0): ?>
                <table class="tabla-reportes">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuario Reportado</th>
                            <th>Motivo</th>
                            <th>Usuario que Reporta</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($reporte = mysqli_fetch_assoc($result_pendientes)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($reporte['id_reportes_usuarios']); ?></td>
                                <td><?php echo htmlspecialchars($reporte['nombre_usuario_reportado']); ?></td>
                                <td><?php echo htmlspecialchars($reporte['motivo']); ?></td>
                                <td><?php echo htmlspecialchars($reporte['usuario_que_reporta']); ?></td>
                                <td><?php echo htmlspecialchars($reporte['fecha_reporte']); ?></td>
                                <td>
                                <form method="POST" action="procesar_accion_reporte.php" style="display:inline-block;" onsubmit="return confirmarBaja();">
                                    <input type="hidden" name="id_reporte" value="<?php echo $reporte['id_reportes_usuarios']; ?>">
                                    <input type="hidden" name="accion" value="dar_baja">
                                    <button type="submit" class="boton-accion boton-baja">Dar de Baja</button>
                                </form>
                                <form method="POST" action="procesar_accion_reporte.php" style="display:inline-block;" onsubmit="return confirmarCancelacion();">
                                    <input type="hidden" name="id_reporte" value="<?php echo $reporte['id_reportes_usuarios']; ?>">
                                    <input type="hidden" name="accion" value="cancelar_reporte">
                                    <button type="submit" class="boton-accion boton-cancelar">Cancelar</button>
                                </form>

<script>
function confirmarBaja() {
    return confirm("¿Estás seguro de que deseas dar de baja al usuario?");
}

function confirmarCancelacion() {
    return confirm("¿Estás seguro de que deseas cancelar el reporte?");
}
</script>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No hay reportes pendientes.</p>
            <?php endif; ?>
        </div>
        <div id="confirmados" class="contenido-pestaña" style="display: none;">
            <h2 class="subtitulo-reportes">Reportes Confirmados</h2>
            <?php if (mysqli_num_rows($result_confirmados) > 0): ?>
                <table class="tabla-reportes">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuario Reportado</th>
                            <th>Motivo</th>
                            <th>Usuario que Reporta</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($reporte = mysqli_fetch_assoc($result_confirmados)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($reporte['id_reportes_usuarios']); ?></td>
                                <td><?php echo htmlspecialchars($reporte['nombre_usuario_reportado']); ?></td>
                                <td><?php echo htmlspecialchars($reporte['motivo']); ?></td>
                                <td><?php echo htmlspecialchars($reporte['usuario_que_reporta']); ?></td>
                                <td><?php echo htmlspecialchars($reporte['fecha_reporte']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No hay reportes confirmados.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>


