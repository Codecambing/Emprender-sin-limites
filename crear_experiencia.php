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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['agregar_experiencia'])) {
    $experiencia = mysqli_real_escape_string($conn, $_POST['experiencia']);
    $tipo_nivel_educacional = mysqli_real_escape_string($conn, $_POST['tipo_nivel_educacional']);
    $fecha_creacion = date("Y-m-d H:i:s");

    if (!empty($experiencia) && !empty($tipo_nivel_educacional)) {
        // Insertar los datos en la tabla de experiencias
        $exp_query = "INSERT INTO experiencias (tipo_nivel_educacional_id_tipo_nivel_educacional, experiencia_profesional, fecha_creacion, fecha_baja, usuarios_id_usuario)
                      VALUES ('$tipo_nivel_educacional', '$experiencia', '$fecha_creacion', NULL, '$id_usuario')";
        if (mysqli_query($conn, $exp_query)) {
            echo "<script>alert('Experiencia agregada correctamente');</script>";
            header("Location: perfil.php");
            exit();
        } else {
            echo "<script>alert('Error al agregar la experiencia');</script>";
        }
    } else {
        echo "<script>alert('Por favor completa todos los campos.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Experiencia</title>
    <link rel="stylesheet" href="css/crear_experiencia.css">
</head>
<body>
    <form action="perfil.php" method="get">
        <button type="submit" class="cancel-button">Cancelar y volver al perfil</button>
    </form>
    <div class="content">
        <h2>Crear Experiencia</h2>
        <form action="crear_experiencia.php" method="POST">
            <!-- Nivel Educacional -->
            <label for="tipo_nivel_educacional">Nivel Educacional:</label>
            <select name="tipo_nivel_educacional" id="tipo_nivel_educacional" required>
                <option value="">Selecciona un nivel</option>
                <?php
                // Obtener niveles educacionales desde la base de datos
                $niveles_query = "SELECT id_tipo_nivel_educacional, nombre_nivel_educacional FROM tipo_nivel_educacional";
                $niveles_result = mysqli_query($conn, $niveles_query);
                while ($nivel = mysqli_fetch_assoc($niveles_result)) {
                    echo "<option value='" . htmlspecialchars($nivel['id_tipo_nivel_educacional']) . "'>" . htmlspecialchars($nivel['nombre_nivel_educacional']) . "</option>";
                }
                ?>
            </select>

            <!-- Descripción de la experiencia -->
            <label for="experiencia">Descripción de la experiencia:</label>
            <textarea name="experiencia" id="experiencia" rows="5" placeholder="Describe tu experiencia profesional" required></textarea>

            <!-- Botón de Agregar Experiencia -->
            <button type="submit" name="agregar_experiencia">Guardar Experiencia</button>
        </form>
    </div>
</body>
</html>
