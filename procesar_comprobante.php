<?php
include_once "servicios/conexion.php";

$id_solicitud = $_POST['id_solicitud'];
$comprobante = $_FILES['comprobante'];

if ($comprobante['error'] !== UPLOAD_ERR_OK) {
    die("Error al subir el archivo.");
}

$allowed_types = ['image/jpeg', 'image/png'];
$max_size = 8 * 1024 * 1024;

if (!in_array($comprobante['type'], $allowed_types) || $comprobante['size'] > $max_size) {
    die("Archivo inválido. Asegúrate de subir un JPEG/PNG menor a 8MB.");
}

// Crear carpeta comprobantes si no existe
if (!is_dir('comprobantes')) {
    mkdir('comprobantes', 0777, true);
}

// Guardar comprobante en la carpeta "comprobantes"
$destino = "comprobantes/" . uniqid() . "_" . $comprobante['name'];
if (!move_uploaded_file($comprobante['tmp_name'], $destino)) {
    die("Error al mover el archivo al destino.");
}

// Obtener información de la solicitud
$query_solicitud = "
    SELECT sr.proyectos_id_proyecto, p.nombre_proyecto
    FROM solicitudes_retiro sr
    JOIN proyectos p ON sr.proyectos_id_proyecto = p.id_proyectos
    WHERE sr.id_solicitud_retiro = ?";
$stmt_solicitud = $conn->prepare($query_solicitud);
$stmt_solicitud->bind_param("i", $id_solicitud);
$stmt_solicitud->execute();
$result_solicitud = $stmt_solicitud->get_result();
$solicitud = $result_solicitud->fetch_assoc();

if (!$solicitud) {
    die("No se encontró la solicitud.");
}
$proyecto_id = $solicitud['proyectos_id_proyectos'];
$proyectos_id = $solicitud['proyectos_id_proyecto'];
$nombre_proyecto = $solicitud['nombre_proyecto'];

// Obtener información de los inversionistas del proyecto
$query_inversionistas = "
    SELECT u.nombre, u.correo, ip.monto, ip.recompensa
    FROM inversion_proyectos ip
    JOIN usuarios u ON ip.usuarios_id_usuario = u.id_usuario
    WHERE ip.proyectos_id_proyectos = ?";
$stmt_inv = $conn->prepare($query_inversionistas);
$stmt_inv->bind_param("i", $proyectos_id);
$stmt_inv->execute();
$inversionistas = $stmt_inv->get_result();

// Crear archivo TXT con los datos de los inversionistas
$archivo_txt = "comprobantes/inversionistas_proyecto_" . $proyectos_id . ".txt";
$contenido_txt = "Inversionistas del proyecto: $nombre_proyecto\n";
$contenido_txt .= "--------------------------------------------------\n";
$contenido_txt .= "Nombre\tCorreo\tMonto\tRecompensa\n";

while ($row = $inversionistas->fetch_assoc()) {
    $contenido_txt .= "{$row['nombre']}\t{$row['correo']}\t{$row['monto']}\t{$row['recompensa']}\n";
}

if (file_put_contents($archivo_txt, $contenido_txt) === false) {
    die("Error al generar el archivo de texto.");
}

// Actualizar la solicitud en la base de datos
$estado = "completado";
$fecha_aceptacion = date("Y-m-d H:i:s");
$query_update = "
    UPDATE solicitudes_retiro
    SET estado = ?, fecha_aceptacion = ?, comprobante = ?
    WHERE id_solicitud_retiro = ?";
$stmt_update = $conn->prepare($query_update);
$stmt_update->bind_param("sssi", $estado, $fecha_aceptacion, $destino, $id_solicitud);

if (!$stmt_update->execute()) {
    die("Error al actualizar la solicitud: " . $stmt_update->error);
}

$query_update_inversionistas = "
    UPDATE inversion_proyectos
    SET estado = 'COMPLETADO'
    WHERE proyectos_id_proyectos = ?";
$stmt_update_inversionistas = $conn->prepare($query_update_inversionistas);
$stmt_update_inversionistas->bind_param("i", $proyecto_id);

if (!$stmt_update_inversionistas->execute()) {
    die("Error al actualizar el estado de los inversionistas: " . $stmt_update_inversionistas->error);
}

// Confirmar la operación y redirigir
echo "<script>
        alert('El comprobante ha sido guardado correctamente, el archivo TXT generado y la solicitud actualizada.');
        window.location.href = 'revision_solicitudes.php?status=success';
    </script>";
?>
