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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['agregar_experiencia'])) {
    // Escapar entradas del formulario para evitar inyección SQL
    $experiencia = mysqli_real_escape_string($conn, $_POST['experiencia']);
    $tipo_nivel_educacional = mysqli_real_escape_string($conn, $_POST['tipo_nivel_educacional']); // Valor del nivel educacional

    // Verificar que los campos no estén vacíos
    if (!empty($experiencia) && !empty($tipo_nivel_educacional)) {
        // Generar fecha de creación
        $fecha_creacion = date("Y-m-d H:i:s");

        // Consulta para insertar en la tabla `experiencias`
        $exp_query = "INSERT INTO experiencias (tipo_nivel_educacional_id_tipo_nivel_educacional, experiencia_profesional, fecha_creacion, fecha_baja, usuarios_id_usuario)
                      VALUES ('$tipo_nivel_educacional', '$experiencia', '$fecha_creacion', NULL, '$id_usuario')";
        if (mysqli_query($conn, $exp_query)) {
            echo "<script>alert('Experiencia agregada correctamente');</script>";
            // Redirigir al usuario para que vea la experiencia agregada
            header("Location: perfil.php");
            exit();
        } else {
            echo "<script>alert('Error al agregar la experiencia');</script>";
        }
    } else {
        echo "<script>alert('Por favor completa todos los campos.');</script>";
    }
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

// Consulta para obtener los proyectos del usuario logueado
$proyectos_query = "
    SELECT id_proyectos, nombre_proyecto, descripcion, fecha_creacion, meta_financiamiento
    FROM proyectos
    WHERE usuarios_id_usuario = '$id_usuario'";
$proyectos_result = mysqli_query($conn, $proyectos_query);

// Verificar si la consulta es válida
if (!$proyectos_result) {
    die("Error en la consulta de proyectos: " . mysqli_error($conn));
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de Usuario</title>
    <link rel="stylesheet" href="css/perfil.css">
</head>
<body>
    <div class="sidebar">
        <h1>Perfil de <?php echo htmlspecialchars($user['nombre']); ?></h1>
        <div class="profile-photo-container">
            <img src="<?php echo htmlspecialchars($user['fotoperfil']) ? $user['fotoperfil'] : 'pfp/default-profile.jpg'; ?>" alt="Foto de perfil" class="profile-photo">
            <form action="perfil.php" method="POST" enctype="multipart/form-data">
                <label for="foto_perfil">Selecciona una foto de perfil:</label>
                <input type="file" name="foto_perfil" id="foto_perfil" accept="image/jpeg, image/png, image/jpg">
                <button type="submit" name="subir_foto">Subir Foto</button>
            </form>
        </div>
        <form action="" method="POST" class="profile-form">
            <label>Nombre de Usuario:</label>
            <span id="nombre_usuario_span"><?php echo htmlspecialchars($user['nombre_usuario']); ?></span>
            <input type="text" name="nombre_usuario" id="nombre_usuario_input" value="<?php echo htmlspecialchars($user['nombre_usuario']); ?>" style="display: none;">

            <label>Correo:</label>
            <span id="correo_span"><?php echo htmlspecialchars($user['correo']); ?></span>
            <input type="email" name="correo" id="correo_input" value="<?php echo htmlspecialchars($user['correo']); ?>" style="display: none;">

            <label>Teléfono:</label>
            <span id="telefono_span"><?php echo htmlspecialchars($user['telefono']); ?></span>
            <input type="text" name="telefono" id="telefono_input" value="<?php echo htmlspecialchars($user['telefono']); ?>" style="display: none;">

            <div class="buttons">
                <button type="button" id="edit-button">Editar</button>
                <button type="submit" name="guardar_info" id="save-button" style="display: none;">Guardar</button>
                <button type="button" id="cancel-button" style="display: none;">Cancelar</button>
            </div>
        </form>
    </div>

    <div class="content">
    <div class="header-experiences">
    <h2>Mis Experiencias</h2>
    <?php
    // Consulta para contar las experiencias del usuario
    $count_query = "SELECT COUNT(*) AS total FROM experiencias WHERE usuarios_id_usuario = '$id_usuario'";
    $count_result = mysqli_query($conn, $count_query);

    if ($count_result) {
        $count_row = mysqli_fetch_assoc($count_result);
        $total_experiencias = $count_row['total'];

        // Mostrar el botón solo si hay más de una experiencia
        if ($total_experiencias >= 0) {
            echo '<a href="crear_experiencia.php" class="btn-add-experience">Añadir Nueva Experiencia</a>';
        }
    }
    ?>
</div>


    <?php
// Verificar si la experiencia está dada de baja (si tiene fecha_baja)
$exp_query = "SELECT 
                  experiencias.id_experiencias,
                  experiencias.experiencia_profesional, 
                  experiencias.fecha_creacion, 
                  experiencias.fecha_baja,
                  tipo_nivel_educacional.nombre_nivel_educacional AS nombre
              FROM 
                  experiencias
              JOIN 
                  tipo_nivel_educacional 
              ON 
                  experiencias.tipo_nivel_educacional_id_tipo_nivel_educacional = tipo_nivel_educacional.id_tipo_nivel_educacional
              WHERE 
                  experiencias.usuarios_id_usuario = '$id_usuario' AND (experiencias.fecha_baja IS NULL OR experiencias.fecha_baja = '')";




$exp_result = mysqli_query($conn, $exp_query);

// Verificar si la consulta es válida
if (!$exp_result) {
    die("Error en la consulta SQL: " . mysqli_error($conn));
}

// Verificar si hay experiencias
if (mysqli_num_rows($exp_result) > 0) {
    echo "<ul class='experiencia-list'>";
    while ($row = mysqli_fetch_assoc($exp_result)) {
        echo "<li>";
        echo "<strong>Nivel:</strong> " . htmlspecialchars($row['nombre']) . "<br>";
        echo "<strong>Experiencia:</strong> " . htmlspecialchars($row['experiencia_profesional']) . "<br>";

        // Botón para editar
        echo "<a href='editar_experiencia.php?id_experiencia=" . urlencode($row['id_experiencias']) . "' class='btn editar'>Editar</a> ";

        // Dentro del bucle que recorre las experiencias
        echo "<a href='baja_experiencia.php?id_experiencia=" . urlencode($row['id_experiencias']) . "' class='btn eliminar' onclick='return confirm(\"¿Estás seguro de que deseas dar de baja esta experiencia?\");'>Dar de baja</a>";

        echo "</li>";
    }
    echo "</ul>";
} else {
    echo "<p>No has guardado experiencias.";
}

?>
</div>
    <script>
        // JavaScript para habilitar/deshabilitar el modo de edición
        const editButton = document.getElementById("edit-button");
        const saveButton = document.getElementById("save-button");
        const cancelButton = document.getElementById("cancel-button");

        const correoSpan = document.getElementById("correo_span");
        const correoInput = document.getElementById("correo_input");

        const telefonoSpan = document.getElementById("telefono_span");
        const telefonoInput = document.getElementById("telefono_input");

        editButton.addEventListener("click", () => {
            // Cambiar texto a campos editables

            correoSpan.style.display = "none";
            correoInput.style.display = "inline-block";

            telefonoSpan.style.display = "none";
            telefonoInput.style.display = "inline-block";

            // Mostrar botones correspondientes
            editButton.style.display = "none";
            saveButton.style.display = "inline-block";
            cancelButton.style.display = "inline-block";
        });

cancelButton.addEventListener("click", () => {
    // Cambiar campos editables de vuelta a texto

    correoSpan.style.display = "inline-block";
    correoInput.style.display = "none";

    telefonoSpan.style.display = "inline-block";
    telefonoInput.style.display = "none";

    // Restaurar los valores originales
    correoInput.value = correoSpan.textContent;
    telefonoInput.value = telefonoSpan.textContent;

    // Mostrar botón de editar y ocultar otros
    editButton.style.display = "inline-block";
    saveButton.style.display = "none";
    cancelButton.style.display = "none";
});

    </script>
</body> 
</html>

<?php
mysqli_close($conn);
?>
