<?php
session_start();
include_once "navbar.php";
include_once "servicios/conexion.php";

// Verificar si el usuario está logueado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

// Obtener el ID del usuario desde la sesión
$id_usuario = intval($_SESSION['id_usuario']);

// Consultar los privilegios del usuario
$sql_privilegios = "SELECT privilegio FROM usuarios WHERE id_usuario = ?";
$stmt = $conn->prepare($sql_privilegios);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Usuario no encontrado en la base de datos.");
}

// **Filtrar proyectos activos (dado_de_baja = 'NO')**
$sql = "SELECT 
        p.id_proyectos, 
        p.nombre_proyecto, 
        p.descripcion, 
        p.fecha_creacion, 
        p.meta_financiamiento, 
        p.banner, 
        tp.nombre_id_tipo_proyecto, 
        u.nombre_usuario,
        u.nombre,
        p.usuarios_id_usuario
    FROM proyectos p
    JOIN tipo_proyecto tp ON p.tipo_proyecto_id_tipo_proyecto = tp.id_tipo_proyecto
    JOIN usuarios u ON p.usuarios_id_usuario = u.id_usuario 
    WHERE p.dado_de_baja = 'NO'";  

$result = $conn->query($sql);

// Verificar si se obtuvieron proyectos
$proyectos = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $proyectos[] = $row;
    }
} else {
    echo "No hay proyectos activos disponibles.";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proyectos Activos</title>
    <link rel="stylesheet" href="css/proyectos_activos.css">
</head>
<body>
    <div class="project-page">
        <div class="container">
            <div class="project-list">
                <h2 class="proyecto">Proyectos Activos</h2>
                <ul id="projectList">
                    <?php if (!empty($proyectos)): ?>
                        <?php foreach ($proyectos as $proyecto): ?>
                            <li class="project-item">
                                <div class="project-card">
                                    <h3 class="nombre-proyecto">
                                        <a href="publicacion_proyecto.php?id=<?= $proyecto['id_proyectos']; ?>" class="nombre-proyecto-link">
                                            <?= htmlspecialchars($proyecto['nombre_proyecto']); ?>
                                        </a>
                                    </h3>
                                    <a href="publicacion_proyecto.php?id=<?= $proyecto['id_proyectos']; ?>">
                                        <img src="banners/<?= $proyecto['banner']; ?>" alt="Banner del proyecto" class="project-banner">
                                    </a>
                                    <div class="project-info">
                                        <p><strong>Descripción:</strong> <?= $proyecto['descripcion']; ?></p>
                                        <p><strong>Tipo de Proyecto:</strong> <?= $proyecto['nombre_id_tipo_proyecto']; ?></p>
                                        <p><strong>Meta Financiera:</strong> <?= number_format($proyecto['meta_financiamiento'], 0, ',', '.'); ?>$</p>
                                        <p><strong>Fecha de Creación:</strong> <?= date("d/m/Y", strtotime($proyecto['fecha_creacion'])); ?></p>
                                        <p><strong>Creado por:</strong> <?= $proyecto['nombre_usuario']; ?></p>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No hay proyectos activos disponibles.</p>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
    <script src="js/lista_proyectos.js"></script>
</body>
</html>
