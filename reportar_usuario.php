<?php
session_start();
include_once "navbar.php";
include_once "servicios/conexion.php";

// Verificar que el usuario haya iniciado sesión
if (!isset($_SESSION['id_usuario'])) {
    echo "<script>alert('Por favor, inicia sesión para reportar un usuario.');</script>";
    header("Location: login.php");
    exit();
}

// Obtener datos del formulario
if (!isset($_GET['usuario_reportado'], $_GET['nombre_usuario_reportado'])) {
    echo "<script>alert('Usuario a reportar no especificado.');</script>";
    header("Location: lista_usuarios.php");
    exit();
}

$usuario_reportado = intval($_GET['usuario_reportado']);
$nombre_usuario_reportado = htmlspecialchars($_GET['nombre_usuario_reportado']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportar Usuario</title>
    <link rel="stylesheet" href="css/reportar_usuario.css">
</head>
<body>
    <div class="report-container">
        <h1>Reportar Usuario: <?php echo $nombre_usuario_reportado; ?></h1>
        <form action="procesar_reporte_cuenta.php" method="POST">
            <input type="hidden" name="usuario_reportado" value="<?php echo $usuario_reportado; ?>">
            <input type="hidden" name="usuario_que_reporta" value="<?php echo $_SESSION['id_usuario']; ?>">
            <label for="motivo">Motivo del Reporte:</label>
            <textarea name="motivo" id="motivo" rows="5" required></textarea>
            <button type="submit" class="btn-submit">Enviar Reporte</button>
        </form>
    </div>
</body>
</html>
