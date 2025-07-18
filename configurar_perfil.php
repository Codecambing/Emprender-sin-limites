<?php
session_start();
include_once "navbar.php";
include_once "servicios/conexion.php";

// Verificar que el usuario haya iniciado sesión
if (!isset($_SESSION['id_usuario'])) {
    echo "<script>alert('Por favor inicie sesion');</script>";
    header("Location: login.php");
    exit();
}

if (!$conn) {
    die("Error en la conexión a la base de datos.");
}
$id_usuario = $_SESSION['id_usuario'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Comprobar si se envió el formulario para actualizar información del perfil
    if (isset($_POST['guardar_info'])) {
        $nombre_usuario = mysqli_real_escape_string($conn, $_POST['nombre_usuario']);
        $correo = mysqli_real_escape_string($conn, $_POST['correo']);
        $telefono = mysqli_real_escape_string($conn, $_POST['telefono']);

        $update_query = "UPDATE usuarios SET correo='$correo', nombre_usuario='$nombre_usuario', telefono='$telefono' WHERE id_usuario='$id_usuario'";
        if (mysqli_query($conn, $update_query)) {
            echo "<script>alert('Información actualizada correctamente');</script>";
        } else {
            echo "<script>alert('Error al actualizar la información');</script>";
        }
    }

    // Comprobar si se envió el formulario para subir la foto de perfil
    if (isset($_POST['subir_foto']) && isset($_FILES['foto_perfil'])) {
        $foto = $_FILES['foto_perfil'];

        if ($foto['error'] == 0) {
            $ext = strtolower(pathinfo($foto['name'], PATHINFO_EXTENSION));
            $permitidas = ['jpg', 'jpeg', 'png'];

            if (in_array($ext, $permitidas)) {
                if ($foto['size'] <= 2 * 1024 * 1024) {
                    $nombreArchivo = "perfil_" . $_SESSION['nombre'] . "." . $ext;
                    $rutaDestino = "pfp/" . $nombreArchivo;

                    if (move_uploaded_file($foto['tmp_name'], $rutaDestino)) {
                        $query = "UPDATE usuarios SET fotoperfil = '$rutaDestino' WHERE id_usuario = '$id_usuario'";
                        $res = mysqli_query($conn, $query);

                        if ($res) {
                            echo "<script>alert('Foto de perfil actualizada con éxito');</script>";
                        } else {
                            echo "Error al actualizar la foto de perfil.";
                        }
                    } else {
                        echo "Error al mover el archivo al servidor.";
                    }
                } else {
                    echo "El archivo es demasiado grande. El tamaño máximo permitido es 2 MB.";
                }
            } else {
                echo "Solo se permiten archivos JPG, JPEG y PNG.";
            }
        } else {
            echo "Hubo un error al subir la imagen.";
        }
    }
}
// Obtener los datos del usuario
$query = "SELECT correo, nombre, nombre_usuario, telefono, fotoperfil FROM usuarios WHERE id_usuario = '$id_usuario'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de Usuario</title>
    <link rel="stylesheet" href="css/configurar_perfil.css">
</head>
<body>
    <div class="profile-container">
        <h1>Perfil de <?php echo htmlspecialchars($user['nombre']); ?></h1>

        <!-- Foto de perfil -->
        <div class="profile-photo-container">
            <img src="<?php echo htmlspecialchars($user['fotoperfil']) ? $user['fotoperfil'] : 'pfp/default-profile.jpg'; ?>" alt="Foto de perfil" class="profile-photo">
            <form action="perfil.php" method="POST" enctype="multipart/form-data">
                <label for="foto_perfil">Selecciona una foto de perfil:</label>
                <input type="file" name="foto_perfil" id="foto_perfil" accept="image/jpeg, image/png, image/jpg">
                <button type="submit" name="subir_foto">Subir Foto</button>
            </form>
        </div>

        <!-- Información del usuario -->
        <form action="" method="POST" class="profile-form">
            <label>Nombre de Usuario:</label>
            <input type="text" name="nombre_usuario" value="<?php echo htmlspecialchars($user['nombre_usuario']); ?>" required readonly>

            <label>Correo:</label>
            <input type="email" name="correo" value="<?php echo htmlspecialchars($user['correo']); ?>" required readonly>

            <label>Teléfono:</label>
            <input type="text" name="telefono" value="<?php echo htmlspecialchars($user['telefono']); ?>" required readonly>

            <div class="buttons">
                <button type="button" id="edit-button">Editar</button>
                <button type="submit" name="guardar_info" id="save-button" style="display: none;">Guardar</button>
                <button type="button" id="cancel-button" style="display: none;">Cancelar</button>
            </div>
        </form>
    </div>

    <script>
        // JavaScript para habilitar/deshabilitar el modo de edición
        const editButton = document.getElementById("edit-button");
        const saveButton = document.getElementById("save-button");
        const cancelButton = document.getElementById("cancel-button");
        const inputs = document.querySelectorAll(".profile-form input");

        editButton.addEventListener("click", () => {
            inputs.forEach(input => input.removeAttribute("readonly"));
            editButton.style.display = "none";
            saveButton.style.display = "inline";
            cancelButton.style.display = "inline";
        });

        cancelButton.addEventListener("click", () => {
            inputs.forEach(input => input.setAttribute("readonly", true));
            editButton.style.display = "inline";
            saveButton.style.display = "none";
            cancelButton.style.display = "none";
        });
    </script>
</body>
</html>

<?php
mysqli_close($conn);
?>
