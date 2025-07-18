<?php
session_start();
include_once "navbar.php";
include_once "servicios/conexion.php"; // Incluir archivo de conexión

// Verificar si el usuario está logueado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

$id_usuario_actual = $_SESSION['id_usuario']; // ID del usuario logueado

// Verificar si se recibió un ID de proyecto
if (!isset($_GET['id'])) {
    die("ID de proyecto no proporcionado.");
}

$id_proyecto = intval($_GET['id']);

// Función para convertir BBCode a HTML
function bbcodeToHtml($bbcode) {
    $search = [
        '/\[b\](.*?)\[\/b\]/s',                     // Negrita
        '/\[i\](.*?)\[\/i\]/s',                     // Cursiva
        '/\[u\](.*?)\[\/u\]/s',                     // Subrayado
        '/\[url\](.*?)\[\/url\]/s',                 // Enlace simple
        '/\[url=(.*?)\](.*?)\[\/url\]/s',           // Enlace con texto
        '/\[img\](.*?)\[\/img\]/s',                 // Imagen
        '/\[left\](.*?)\[\/left\]/s',               // Alineación izquierda
        '/\[center\](.*?)\[\/center\]/s',           // Alineación centro
        '/\[right\](.*?)\[\/right\]/s',             // Alineación derecha
        '/\[color=(.*?)\](.*?)\[\/color\]/s',       // Color de texto
        '/\[size=(.*?)\](.*?)\[\/size\]/s'          // Tamaño de texto
    ];
    $replace = [
        '<strong>$1</strong>',
        '<em>$1</em>',
        '<u>$1</u>',
        '<a href="$1" target="_blank">$1</a>',
        '<a href="$1" target="_blank">$2</a>',
        '<img src="$1" alt="Imagen" style="max-width:100%;"/>',
        '<div style="text-align:left;">$1</div>',
        '<div style="text-align:center;">$1</div>',
        '<div style="text-align:right;">$1</div>',
        '<span style="color:$1;">$2</span>',
        '<span style="font-size:$1px;">$2</span>'
    ];
    return preg_replace($search, $replace, $bbcode);
}

// Consultar los datos del proyecto en la base de datos
$sql = "SELECT 
            p.nombre_proyecto, 
            p.descripcion, 
            p.detalle, 
            p.fecha_creacion,
            p.fecha_limite,
            p.meta_financiamiento, 
            p.banner, 
            tp.nombre_id_tipo_proyecto, 
            u.nombre,
            u.id_usuario AS id_propietario,
            p.dado_de_baja -- Agregar el estado de baja
        FROM proyectos p
        JOIN tipo_proyecto tp ON p.tipo_proyecto_id_tipo_proyecto = tp.id_tipo_proyecto
        JOIN usuarios u ON p.usuarios_id_usuario = u.id_usuario
        WHERE p.id_proyectos = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_proyecto);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("El proyecto solicitado no existe.");
}

$sql_inversiones = "SELECT SUM(ip.monto) AS total_invertido
                    FROM inversion_proyectos ip
                    WHERE ip.proyectos_id_proyectos = ? AND ip.fecha_anulacion IS NULL";


$stmt_inversiones = $conn->prepare($sql_inversiones);
$stmt_inversiones->bind_param("i", $id_proyecto);
$stmt_inversiones->execute();
$result_inversiones = $stmt_inversiones->get_result();
$row_inversiones = $result_inversiones->fetch_assoc();

$total_invertido = $row_inversiones['total_invertido'] ? $row_inversiones['total_invertido'] : 0;

// Actualizamos el valor de 'cantidad_invertida' en el proyecto (aunque este paso puede no ser necesario si ya se está manejando correctamente)
$sql_update = "UPDATE proyectos SET cantidad_invertida = ? WHERE id_proyectos = ?";
$stmt_update = $conn->prepare($sql_update);
$stmt_update->bind_param("di", $total_invertido, $id_proyecto);
$stmt_update->execute();

$proyecto = $result->fetch_assoc();

$sql_comentarios = "
    SELECT v.id_valoracion, v.comentario, v.calificacion, v.fecha_valoracion, u.nombre, u.fotoperfil, v.ocultado, v.usuarios_id_usuario
    FROM valoraciones v
    JOIN usuarios u ON v.usuarios_id_usuario = u.id_usuario
    WHERE v.proyectos_id_proyectos = ? AND v.ocultado = 0
    ORDER BY v.fecha_valoracion DESC";


$stmt_comentarios = $conn->prepare($sql_comentarios);
$stmt_comentarios->bind_param("i", $id_proyecto);
$stmt_comentarios->execute();
$result_comentarios = $stmt_comentarios->get_result();

$query = "SELECT nombre, fotoperfil, privilegio FROM usuarios WHERE id_usuario = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    $nombre_usuario = $user['nombre'] ?? 'Usuario';
    $foto_perfil = $user['fotoperfil'] ?? 'pfp/default-profile.jpg';

    if (isset($_POST['ocultar_comentario'])) {
        $comentario_id = intval($_POST['comentario_id']);
        
        // Actualizar la columna 'ocultado' en la tabla 'valoraciones'
        $sql = "UPDATE valoraciones SET ocultado = 1 WHERE id_valoracion = ? AND usuarios_id_usuario = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $comentario_id, $id_usuario_actual);
        
        if ($stmt->execute()) {
            echo "Comentario ocultado con éxito.";
        } else {
            echo "Error al ocultar el comentario.";
        }
    }

if (isset($_POST['reportar_comentario'])) {
    $comentario_id = intval($_POST['comentario_id']);
    $motivo = $_POST['motivo']; // El motivo lo puedes capturar con un formulario de texto
    $sql = "INSERT INTO reportes_comentarios (comentario_id, usuario_id, motivo) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $comentario_id, $id_usuario_actual, $motivo);
    if ($stmt->execute()) {
        echo "Comentario reportado con éxito";
    } else {
        echo "Error al reportar el comentario";
    }
}

// Consultar si el usuario actual tiene privilegios de administrador
$sql_privilegios = "SELECT privilegio FROM usuarios WHERE id_usuario = ?";
$stmt_privilegios = $conn->prepare($sql_privilegios);
$stmt_privilegios->bind_param("i", $id_usuario_actual);
$stmt_privilegios->execute();
$result_privilegios = $stmt_privilegios->get_result();
$row_privilegios = $result_privilegios->fetch_assoc();

// Privilegios: 1 es admin, 2 es usuario común
$es_admin = ($row_privilegios['privilegio'] == "Administrador");

$sql_retiro = "SELECT COUNT(*) AS existe_retiro 
               FROM solicitudes_retiro 
               WHERE proyectos_id_proyecto = ?";
$stmt_retiro = $conn->prepare($sql_retiro);
$stmt_retiro->bind_param("i", $id_proyecto);
$stmt_retiro->execute();
$result_retiro = $stmt_retiro->get_result();
$row_retiro = $result_retiro->fetch_assoc();

$retiro_solicitado = ($row_retiro['existe_retiro'] > 0); // Si existe retiro, será verdadero
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($proyecto['nombre_proyecto']); ?></title>
    <link rel="stylesheet" href="css/publicacion_proyecto.css">
</head>
<body>
<div class="project-publication">
    <h1><?= htmlspecialchars($proyecto['nombre_proyecto']); ?></h1>
    <img src="banners/<?= htmlspecialchars($proyecto['banner']); ?>" alt="Banner del proyecto" class="project-banner">
    
    <p><strong>Descripción:</strong> <?= htmlspecialchars($proyecto['descripcion']); ?></p>
    
    <!-- Mostrar detalles del proyecto -->
    <?= !empty($proyecto['detalle']) 
        ? bbcodeToHtml($proyecto['detalle']) 
        : "No hay detalles de la publicación del proyecto."; ?>
    </p>
    
    <p><strong>Tipo de Proyecto:</strong> <?= htmlspecialchars($proyecto['nombre_id_tipo_proyecto']); ?></p>
    <p><strong>Fecha de Creación:</strong> <?= date("d/m/Y", strtotime($proyecto['fecha_creacion'])); ?></p>
    <p><strong>Fecha Limite:</strong> <?= date("d/m/Y", strtotime($proyecto['fecha_limite'])); ?></p>
    <p><strong>Creado por:</strong> <?= htmlspecialchars($proyecto['nombre']); ?></p>

    <p><strong>Meta Financiera:</strong> <?= number_format($proyecto['meta_financiamiento'], 0, ',', '.'); ?>$</p>
    <p><strong>Contribuido:</strong> <?= number_format($total_invertido, 0, ',', '.'); ?>$</p>
    
    <!-- Botones de acciones -->
    <div class="action-buttons">
    <?php if ($id_usuario_actual === $proyecto['id_propietario']|| $es_admin): ?>
        <button class="edit-button" onclick="window.location.href='editar_proyecto.php?id=<?= $id_proyecto; ?>'">Editar</button>
        <button class="list-button" onclick="window.location.href='lista_inversores.php?id=<?= $id_proyecto; ?>'">Lista Inversores</button>
    <?php if ($proyecto['dado_de_baja'] === 'NO'|| $es_admin): ?>
        <button class="delete-button" onclick="darDeBaja(<?= $id_proyecto; ?>)">Dar de Baja</button>
    <?php endif; ?>
    <?php if ($total_invertido >= $proyecto['meta_financiamiento'] && !$retiro_solicitado): ?>
    <button class="withdraw-button" onclick="window.location.href='solicitar_fondos.php?id=<?= $id_proyecto; ?>'">Solicitud de Retiro</button>
    <?php endif; ?>
    <?php else: ?>
        <button class="invest-button" onclick="window.location.href='invertir_proyecto.php?id=<?= $id_proyecto; ?>'">Invertir</button>
    <?php endif; ?>
    <?php if ($id_usuario_actual !== $proyecto['id_propietario']): ?>
    <button class="report-button" onclick="window.location.href='reportar_publicacion.php?id=<?= $id_proyecto; ?>'">Reportar Publicación</button>
    <?php endif; ?>
    </div>
</div>

<script>
function darDeBaja(idProyecto) {
    if (confirm("¿Estás seguro de que deseas dar de baja este proyecto?")) {
        window.location.href = `dar_baja_proyecto.php?id=${idProyecto}`;
    }
}
</script>
<div class="existing-comments">
    <h2>Comentarios</h2>
    <?php if ($result_comentarios->num_rows > 0): ?>
        <ul class="comment-list">
    <?php while ($comentario = $result_comentarios->fetch_assoc()): ?>
        <li class="comment-item">
            <img src="<?= htmlspecialchars($comentario['fotoperfil'] ?? 'pfp/default-profile.jpg'); ?>" 
                 alt="Foto de <?= htmlspecialchars($comentario['nombre']); ?>" class="comment-photo">
            <div class="comment-content">
                <p class="name"><?= htmlspecialchars($comentario['nombre']); ?></p>
                <div class="stars">
                    <?php
                    $calificacion = intval($comentario['calificacion']);
                    for ($i = 1; $i <= 5; $i++) {
                        echo $i <= $calificacion 
                            ? '<span class="star">★</span>' 
                            : '<span class="star empty">★</span>';
                    }
                    ?>
                </div>
                <p class="comment-text"><?= htmlspecialchars($comentario['comentario']); ?></p>
                <small class="comment-date"><?= date("d/m/Y H:i", strtotime($comentario['fecha_valoracion'])); ?></small>
                
                <?php 
                // Verificar si el comentario es del usuario actual o si el usuario es administrador
                if ($comentario['usuarios_id_usuario'] == $id_usuario_actual || $es_admin): ?>
                    <form action="eliminar_comentario.php" method="POST" style="display:inline;">
                        <input type="hidden" name="comentario_id" value="<?= $comentario['id_valoracion']; ?>">
                        <button type="submit" name="eliminar_comentario" class="delete-button">Eliminar</button>
                    </form>
                <?php endif; ?>
            </div>
        </li>
    <?php endwhile; ?>
</ul>
    <?php else: ?>
        <p>Aún no hay comentarios. ¡Sé el primero en opinar!</p>
    <?php endif; ?>
</div>

<!-- Formulario para agregar un comentario -->
<div class="comments-section">
    <h2>Deja un comentario</h2>
    <form action="agregar_comentario.php" method="POST" class="comment-form">
        <input type="hidden" name="id_proyecto" value="<?= $id_proyecto; ?>">
        <div class="rating">
            <label for="calificacion">Calificación (1-5):</label>
            <select name="calificacion" id="calificacion" required>
                <option value="5">★★★★★</option>
                <option value="4">★★★★</option>
                <option value="3">★★★</option>
                <option value="2">★★</option>
                <option value="1">★</option>
            </select>
        </div>
        <div class="comment">
            <label for="comentario">Comentario:</label>
            <textarea name="comentario" id="comentario" rows="4" required></textarea>
        </div>
        <button type="submit">Enviar Comentario</button>
    </form>
</div>

</body>
</html>
