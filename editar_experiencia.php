<?php
session_start();
include_once "navbar.php";
include_once "servicios/conexion.php";

// Verificar si el usuario está logueado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$id_experiencia = $_GET['id_experiencia'] ?? null;

if (!$id_experiencia) {
    echo "<script>alert('ID de experiencia no válida'); window.location.href = 'perfil.php';</script>";
    exit();
}

// Obtener la experiencia actual
$query = "SELECT experiencia_profesional FROM experiencias WHERE id_experiencias = '$id_experiencia' AND usuarios_id_usuario = '$id_usuario'";
$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    echo "<script>alert('Experiencia no encontrada'); window.location.href = 'perfil.php';</script>";
    exit();
}

$row = mysqli_fetch_assoc($result);
$descripcion_actual = $row['experiencia_profesional'];

// Actualizar experiencia
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['editar_experiencia'])) {
    $nueva_descripcion = mysqli_real_escape_string($conn, $_POST['experiencia']);
    
    if (!empty($nueva_descripcion)) {
        $update_query = "UPDATE experiencias SET experiencia_profesional = '$nueva_descripcion' WHERE id_experiencias = '$id_experiencia' AND usuarios_id_usuario = '$id_usuario'";
        if (mysqli_query($conn, $update_query)) {
            echo "<script>alert('Experiencia actualizada correctamente'); window.location.href = 'perfil.php';</script>";
        } else {
            echo "<script>alert('Error al actualizar la experiencia');</script>";
        }
    } else {
        echo "<script>alert('La descripción no puede estar vacía');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Experiencia</title>
    <link rel="stylesheet" href="css/editar_experiencia.css">
</head>
<body>
    <div class="content">
        <h2>Editar Experiencia</h2>
        <form action="" method="POST">
            <label for="experiencia">Descripción de la experiencia:</label>
            <textarea name="experiencia" id="experiencia" rows="3" required><?php echo htmlspecialchars($descripcion_actual); ?></textarea>
            <button type="submit" name="editar_experiencia">Guardar cambios</button>
        </form>
        <form action="perfil.php" method="get">
            <button type="submit" class="cancel-button">Cancelar</button>
        </form>
    </div>
</body>
</html>
