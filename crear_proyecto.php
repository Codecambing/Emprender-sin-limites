<?php 
session_start();
include_once "navbar.php";
include_once "servicios/conexion.php";

if (!$conn) {
    die("Error en la conexión a la base de datos.");
}

// Verificar si el usuario está autenticado
if (!isset($_SESSION['id_usuario'])) {
    echo "<script>alert('Por favor inicie sesión');</script>";
    header("Location: login.php");
    exit();
}

// Obtener tipos de proyecto desde la base de datos
$sql = "SELECT id_tipo_proyecto, nombre_id_tipo_proyecto FROM tipo_proyecto";
$result = $conn->query($sql);

if ($result === false) {
    echo "Error en la consulta: " . $conn->error;
    exit;
} elseif ($result->num_rows === 0) {
    echo "No se encontraron tipos de proyecto.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre_proyecto = $_POST['nombre_proyecto'];
    $descripcion = $_POST['descripcion'];
    $fecha_creacion = date("Y-m-d");
    $fecha_limite = $_POST['fecha_limite'];
    $meta_financiamiento = $_POST['meta_financiamiento'];
    $meta_financiamiento_comision = $_POST['meta_financiamiento_comision']; // Nuevo campo
    $usuarios_id_usuario = $_SESSION['id_usuario'];
    $tipo_proyecto_id = $_POST['tipo_proyecto'];

    // Manejo del archivo de imagen
    $target_dir = "banners/";
    $banner_filename = "banner_" . preg_replace("/[^a-zA-Z0-9]/", "_", $nombre_proyecto) . "." . strtolower(pathinfo($_FILES["banner"]["name"], PATHINFO_EXTENSION));
    $target_file = $target_dir . $banner_filename;
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    $check = getimagesize($_FILES["banner"]["tmp_name"]);
    if ($check !== false) {
        $uploadOk = 1;
    } else {
        echo "<script>alert('El archivo no es una imagen válida.');</script>";
        $uploadOk = 0;
    }

    if ($_FILES["banner"]["size"] > 8000000) {
        echo "<script>alert('El archivo es demasiado grande. Máximo 8MB.');</script>";
        $uploadOk = 0;
    }

    if ($imageFileType != "jpg" && $imageFileType != "jpeg" && $imageFileType != "png") {
        echo "<script>alert('Solo se permiten archivos JPG, JPEG y PNG.');</script>";
        $uploadOk = 0;
    }

    $dimensions = getimagesize($_FILES["banner"]["tmp_name"]);
    if ($dimensions[0] > 1000 || $dimensions[1] > 500) {
        echo "<script>alert('Las dimensiones de la imagen deben ser máximo 1000x500.');</script>";
        $uploadOk = 0;
    }

    if ($uploadOk == 1) {
        if (move_uploaded_file($_FILES["banner"]["tmp_name"], $target_file)) {
            $sql = "INSERT INTO proyectos 
                        (nombre_proyecto, descripcion, fecha_creacion, fecha_limite, meta_financiamiento, meta_financiamiento_comision, usuarios_id_usuario, tipo_proyecto_id_tipo_proyecto, banner) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "ssssddiis",
                $nombre_proyecto, 
                $descripcion, 
                $fecha_creacion, 
                $fecha_limite, 
                $meta_financiamiento, 
                $meta_financiamiento_comision, 
                $usuarios_id_usuario, 
                $tipo_proyecto_id, 
                $banner_filename
            );
    
                if ($stmt->execute()) {
                    // Obtener el ID del proyecto recién creado
                    $id_proyecto = $stmt->insert_id;

                    // Redirigir a la publicación del proyecto
                    echo "<script>alert('El proyecto ha sido creado exitosamente.'); window.location.href='publicacion_proyecto.php?id=$id_proyecto';</script>";
                } else {
                    echo "<script>alert('Error al crear el proyecto: " . $conn->error . "');</script>";
            }
        } else {
            echo "<script>alert('Hubo un error al subir el archivo.');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Proyecto</title>
    <link rel="stylesheet" href="css/crear_proyecto.css">
</head>
<body>
    <form action="crear_proyecto.php" method="POST" enctype="multipart/form-data">
        <h1>Crear Nuevo Proyecto</h1>
        <label for="nombre_proyecto">Nombre del Proyecto:</label>
        <input type="text" id="nombre_proyecto" name="nombre_proyecto" required><br><br>

        <label for="descripcion">Descripción:</label>
        <textarea id="descripcion" name="descripcion" required></textarea><br><br>

        <label for="banner">Banner (JPG, JPEG o PNG, máx 1000x500):</label>
        <input type="file" id="banner" name="banner" accept="image/jpeg, image/png, image/jpg" required><br><br>

        <label for="meta_financiamiento">Meta Financiera:</label>
        <input type="number" id="meta_financiamiento" name="meta_financiamiento" oninput="calcularMetaConComision()" required><br><br>

        <label for="meta_financiamiento_comision">Monto rescatable, Comisión (3%):</label>
        <input type="number" id="meta_financiamiento_comision" name="meta_financiamiento_comision" readonly><br><br>

        <script>
            function calcularMetaConComision() {
                const metaUsuario = parseFloat(document.getElementById("meta_financiamiento").value) || 0;
                const comision = metaUsuario * 0.03; // Calcular 3% de comisión
                const metaConComision = metaUsuario - comision;
                document.getElementById("meta_financiamiento_comision").value = metaConComision.toFixed(2);
            }
        </script>

        <label for="fecha_limite">Fecha Límite:</label>
        <input type="date" id="fecha_limite" name="fecha_limite" required><br><br>

        <label for="tipo_proyecto">Tipo de Proyecto:</label>
        <select id="tipo_proyecto" name="tipo_proyecto" required>
            <option value="">Seleccione un tipo</option>
            <?php while ($row = $result->fetch_assoc()) : ?>
                <option value="<?= $row['id_tipo_proyecto'] ?>"><?= $row['nombre_id_tipo_proyecto'] ?></option>
            <?php endwhile; ?>
        </select><br><br>

        <button type="submit">Crear Proyecto</button>
    </form>
</body>
</html>
