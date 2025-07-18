<?php
session_start();
include_once "servicios/conexion.php";

// Verificar si el usuario está logueado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

// Verificar si se proporcionaron todos los datos requeridos
if (!isset($_POST['id_proyecto'], $_POST['nombre_proyecto'], $_POST['descripcion'], $_POST['detalle'], $_POST['meta_financiamiento'])) {
    die("Error: Faltan datos del formulario.");
}

$id_proyecto = intval($_POST['id_proyecto']);
$nombre_proyecto = $_POST['nombre_proyecto'];
$descripcion = $_POST['descripcion'];
$detalle = $_POST['detalle'];
$meta_financiamiento = floatval($_POST['meta_financiamiento']);

// Inicializar las recompensas como NULL si no se activaron
$recompensa_menor = isset($_POST['recompensa_menor']) ? floatval($_POST['recompensa_menor']) : null;
$info_recompensa_menor = $_POST['info_recompensa_menor'] ?? null;
$recompensa_media = isset($_POST['recompensa_media']) ? floatval($_POST['recompensa_media']) : null;
$info_recompensa_media = $_POST['info_recompensa_media'] ?? null;
$recompensa_mayor = isset($_POST['recompensa_mayor']) ? floatval($_POST['recompensa_mayor']) : null;
$info_recompensa_mayor = $_POST['info_recompensa_mayor'] ?? null;

// Actualizar el proyecto con las recompensas
$sql = "
    UPDATE proyectos 
    SET nombre_proyecto = ?, 
        descripcion = ?, 
        detalle = ?, 
        meta_financiamiento = ?, 
        recompensa_menor = ?, 
        info_recompensa_menor = ?, 
        recompensa_media = ?, 
        info_recompensa_media = ?, 
        recompensa_mayor = ?, 
        info_recompensa_mayor = ?
    WHERE id_proyectos = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sssdssssssi", $nombre_proyecto, $descripcion, $detalle, $meta_financiamiento, $recompensa_menor, $info_recompensa_menor, $recompensa_media, $info_recompensa_media, $recompensa_mayor, $info_recompensa_mayor, $id_proyecto);

if ($stmt->execute()) {
    echo "<script>
                alert('¡Publicación editada exitosamente!');
                window.location.href = 'publicacion_proyecto.php?id=$id_proyecto';
              </script>";
} else {
    echo "Error al actualizar el proyecto: " . $conn->error;
}
?>
