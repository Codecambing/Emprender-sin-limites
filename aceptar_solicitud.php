<?php
session_start();
include_once "navbar.php";
include_once "servicios/conexion.php";

$id_solicitud = $_GET['id'] ?? null;
if (!$id_solicitud) {
    die("Solicitud no especificada.");
}

// Obtener información del usuario y proyecto asociado a la solicitud
$query = "
    SELECT u.correo, p.nombre_proyecto 
    FROM solicitudes_retiro sr
    JOIN usuarios u ON sr.usuarios_id_usuario = u.id_usuario
    JOIN proyectos p ON sr.proyectos_id_proyecto = p.id_proyectos
    WHERE sr.id_solicitud_retiro = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_solicitud);
$stmt->execute();
$result = $stmt->get_result();
$solicitud = $result->fetch_assoc();

if (!$solicitud) {
    die("Solicitud no encontrada.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/aceptar_solicitud.css">
    <title>Subir Comprobante</title>
</head>
<body>
    <div class="form-container">
        <h1 class="titulo">Subir Comprobante</h1>
        <form action="procesar_comprobante.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id_solicitud" value="<?php echo $id_solicitud; ?>">
            <label for="comprobante">Sube el comprobante (JPEG o PNG, máx. 8MB):</label>
            <input type="file" name="comprobante" id="comprobante" accept=".jpeg,.jpg,.png" required>
            <button type="submit">Confirmar Pago</button>
        </form>
    </div>
</body>
</html>
