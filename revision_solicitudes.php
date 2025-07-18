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

// Consultar solicitudes pendientes
$sql_pendientes = "
    SELECT sr.id_solicitud_retiro, 
           p.nombre_proyecto, 
           u.nombre AS nombre_usuario, 
           sr.rut_transferencia, 
           sr.nombre_transferencia, 
           sr.correo_transferencia, 
           b.nombre_banco, 
           sr.numero_cuenta_transferencia, 
           sr.monto, 
           tc.nombre_tipo_cuenta_bancaria, 
           sr.fecha_solicitud, 
           sr.estado
    FROM solicitudes_retiro sr
    JOIN proyectos p ON sr.proyectos_id_proyecto = p.id_proyectos
    JOIN usuarios u ON sr.usuarios_id_usuario = u.id_usuario
    JOIN bancos_retiro b ON sr.banco_transferencia = b.id_bancos_solicitud
    JOIN tipo_cuenta_bancaria tc ON sr.nombre_tipo_cuenta_bancaria = tc.id_tipo_cuenta_bancaria
    WHERE sr.estado = 'pendiente';
";
$result_pendientes = $conn->query($sql_pendientes);

// Consultar solicitudes realizadas
$sql_realizadas = "
    SELECT sr.id_solicitud_retiro, 
           p.nombre_proyecto, 
           u.nombre AS nombre_usuario, 
           sr.rut_transferencia, 
           sr.nombre_transferencia, 
           sr.correo_transferencia, 
           b.nombre_banco, 
           sr.numero_cuenta_transferencia, 
           sr.monto, 
           tc.nombre_tipo_cuenta_bancaria, 
           sr.fecha_solicitud, 
           sr.estado
    FROM solicitudes_retiro sr
    JOIN proyectos p ON sr.proyectos_id_proyecto = p.id_proyectos
    JOIN usuarios u ON sr.usuarios_id_usuario = u.id_usuario
    JOIN bancos_retiro b ON sr.banco_transferencia = b.id_bancos_solicitud
    JOIN tipo_cuenta_bancaria tc ON sr.nombre_tipo_cuenta_bancaria = tc.id_tipo_cuenta_bancaria
    WHERE sr.estado IN ('completado', 'rechazado');
";
$result_realizadas = $conn->query($sql_realizadas);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revisión de Solicitudes</title>
    <link rel="stylesheet" href="css/revision_solicitudes.css">
</head>
<body>
    <div class="solicitudes-container">
        <h1 class="solicitudes-title">Revisión de Solicitudes de fondos</h1>
        <div class="solicitudes-tabs">
            <div id="tab-pendientes" class="solicitudes-tab active" onclick="showTab('pendientes')">Solicitudes Pendientes</div>
            <div id="tab-realizadas" class="solicitudes-tab" onclick="showTab('realizadas')">Solicitudes Realizadas</div>
        </div>
        <div id="content-pendientes" class="solicitudes-tab-content active">
    <h2>Solicitudes Pendientes</h2>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Proyecto</th>
            <th>Usuario</th>
            <th>RUT</th>
            <th>Nombre Transferencia</th>
            <th>Correo</th>
            <th>Banco</th>
            <th>Número de Cuenta</th>
            <th>Monto</th>
            <th>Tipo de Cuenta</th>
            <th>Fecha de Solicitud</th>
            <th>Estado</th>
            <th>Acciones</th> <!-- Nueva columna para los botones -->
        </tr>
        <?php while ($row = $result_pendientes->fetch_assoc()): ?>
        <tr>
            <td><?php echo $row['id_solicitud_retiro']; ?></td>
            <td><?php echo $row['nombre_proyecto']; ?></td>
            <td><?php echo $row['nombre_usuario']; ?></td>
            <td><?php echo $row['rut_transferencia']; ?></td>
            <td><?php echo $row['nombre_transferencia']; ?></td>
            <td><?php echo $row['correo_transferencia']; ?></td>
            <td><?php echo $row['nombre_banco']; ?></td>
            <td><?php echo $row['numero_cuenta_transferencia']; ?></td>
            <td><?php echo $row['monto']; ?>$</td>
            <td><?php echo $row['nombre_tipo_cuenta_bancaria']; ?></td>
            <td><?php echo $row['fecha_solicitud']; ?></td>
            <td><?php echo $row['estado']; ?></td>
            <td>
                <!-- Botones para enviar o cancelar -->
                <a href="aceptar_solicitud.php?id=<?php echo $row['id_solicitud_retiro']; ?>" class="btn-enviar">Enviar</a>
                
                <a href="cancelar_solicitud.php?id_solicitud=<?php echo $row['id_solicitud_retiro']; ?>" class="btn-cancelar">Cancelar</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>


        <div id="content-realizadas" class="solicitudes-tab-content">
            <h2>Solicitudes Realizadas</h2>
            <table border="1">
                <tr>
                    <th>ID</th>
                    <th>Proyecto</th>
                    <th>Usuario</th>
                    <th>RUT</th>
                    <th>Nombre Transferencia</th>
                    <th>Correo</th>
                    <th>Banco</th>
                    <th>Número de Cuenta</th>
                    <th>Monto</th>
                    <th>Tipo de Cuenta</th>
                    <th>Fecha de Solicitud</th>
                    <th>Estado</th>
                </tr>
                <?php while ($row = $result_realizadas->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id_solicitud_retiro']; ?></td>
                    <td><?php echo $row['nombre_proyecto']; ?></td>
                    <td><?php echo $row['nombre_usuario']; ?></td>
                    <td><?php echo $row['rut_transferencia']; ?></td>
                    <td><?php echo $row['nombre_transferencia']; ?></td>
                    <td><?php echo $row['correo_transferencia']; ?></td>
                    <td><?php echo $row['nombre_banco']; ?></td>
                    <td><?php echo $row['numero_cuenta_transferencia']; ?></td>
                    <td><?php echo $row['monto']; ?></td>
                    <td><?php echo $row['nombre_tipo_cuenta_bancaria']; ?></td>
                    <td><?php echo $row['fecha_solicitud']; ?></td>
                    <td><?php echo $row['estado']; ?></td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </div>

    <script>
        function showTab(tab) {
            const tabs = document.querySelectorAll('.solicitudes-tab');
            const contents = document.querySelectorAll('.solicitudes-tab-content');

            // Desactivar todas las pestañas y ocultar todos los contenidos
            tabs.forEach(t => t.classList.remove('active'));
            contents.forEach(c => c.classList.remove('active'));

            // Activar la pestaña y contenido seleccionado
            document.getElementById('tab-' + tab).classList.add('active');
            document.getElementById('content-' + tab).classList.add('active');
        }
    </script>
</body>
</html>

