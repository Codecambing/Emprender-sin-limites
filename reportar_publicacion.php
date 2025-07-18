<?php
session_start();
include_once "navbar.php";
include_once "servicios/conexion.php";

// Verificar que el usuario haya iniciado sesión
if (!isset($_SESSION['id_usuario'])) {
    echo "<script>alert('Por favor, inicia sesión para reportar una publicación.');</script>";
    header("Location: login.php");
    exit();
}

// Obtener el ID del proyecto desde la URL
if (!isset($_GET['id_proyecto']) || !is_numeric($_GET['id_proyecto'])) {
    echo "<script>alert('ID de proyecto no válido.');</script>";
    header("Location: lista_proyectos.php");
    exit();
}

$id_proyecto = intval($_GET['id_proyecto']);

// Consultar los datos del proyecto
$sql_proyecto = "SELECT p.nombre_proyecto, p.banner, u.nombre AS nombre_usuario
                 FROM proyectos p
                 JOIN usuarios u ON p.usuarios_id_usuario = u.id_usuario
                 WHERE p.id_proyectos = ?";
$stmt_proyecto = $conn->prepare($sql_proyecto);
$stmt_proyecto->bind_param("i", $id_proyecto);
$stmt_proyecto->execute();
$result_proyecto = $stmt_proyecto->get_result();

if ($result_proyecto->num_rows == 0) {
    echo "<script>alert('El proyecto no existe.');</script>";
    header("Location: lista_proyectos.php");
    exit();
}

$proyecto = $result_proyecto->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportar Publicación</title>
    <link rel="stylesheet" href="css/reportar_publicacion.css">
</head>
<body>
    <div class="report-container">
        <h1>Reportar Publicación: <?php echo htmlspecialchars($proyecto['nombre_proyecto']); ?></h1>
        <p><strong>Publicado por:</strong> <?php echo htmlspecialchars($proyecto['nombre_usuario']); ?></p>
        <img src="imagenes/<?php echo htmlspecialchars($proyecto['banner']); ?>" alt="Banner del Proyecto" class="banner-image">
        
        <form action="procesar_reporte.php" method="POST">
            <input type="hidden" name="id_proyecto" value="<?php echo $id_proyecto; ?>">
            <input type="hidden" name="usuario_que_reporta" value="<?php echo $_SESSION['id_usuario']; ?>">
            <label for="motivo">Motivo del Reporte:</label>
            <textarea name="motivo" id="motivo" rows="5" required></textarea>
            <button type="submit" class="btn-submit">Enviar Reporte</button>
        </form>
    </div>
</body>
</html>
