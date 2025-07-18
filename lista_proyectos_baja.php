<?php
session_start();
include_once "navbar.php";
include_once "servicios/conexion.php"; // Incluir archivo de conexión

// Verificar si el usuario está logueado
if (!isset($_SESSION['id_usuario'])) {
    echo "<script>alert('Por favor inicie sesion');</script>";
    header("Location: login.php");
    exit();
}

$id_usuario_logueado = $_SESSION['id_usuario']; // Obtener el ID del usuario logueado

// Verificar si la conexión a la base de datos es exitosa
if (!$conn) {
    die("Error en la conexión a la base de datos.");
}

// Consultar los proyectos en la base de datos, incluyendo el nombre del usuario y su ID
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
    WHERE p.dado_de_baja = 'NO' 
    AND p.id_proyectos NOT IN (SELECT proyectos_id_proyecto FROM solicitudes_retiro WHERE estado = 'pendiente')
";  // Filtrar proyectos activos
$result = $conn->query($sql);

// Verificar si se obtuvieron proyectos
$proyectos = [];
if ($result->num_rows > 0) {
    // Recorrer los proyectos y agregarlos al arreglo
    while ($row = $result->fetch_assoc()) {
        $proyectos[] = $row;
    }
} else {
    echo "No hay proyectos disponibles.";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Proyectos</title>
    <link rel="stylesheet" href="css/lista_proyectos.css">
</head>
<body>
    <div class="project-page">
        <div class="container">
            <!-- Sidebar para búsqueda y creación de proyecto -->
            <div class="sidebar">
                <div class="createProjectButton">
                    <button id="createProjectButton" onclick="window.location.href='crear_proyecto.php'">Crear Proyecto</button>
                </div>
                <h2>Búsqueda de Proyectos</h2>
                <input type="text" id="searchInput" placeholder="Buscar proyectos..." onkeyup="searchProjects()">
                <div class="createProjectButton">
                    <button id="searchButton">Buscar</button>
                </div>
            </div>

            <!-- Lista de proyectos -->
            <div class="project-list">
                <h2 class="proyecto">Proyectos</h2>
                <ul id="projectList">
                    <?php if (!empty($proyectos)): ?>
                        <?php foreach ($proyectos as $proyecto): ?>
                            <li class="project-item">
                                <div class="project-card">
                                    <!-- Nombre del proyecto como enlace -->
                                    <h3 class="nombre-proyecto">
                                        <a href="publicacion_proyecto.php?id=<?= $proyecto['id_proyectos']; ?>" class="nombre-proyecto-link">
                                            <?= htmlspecialchars($proyecto['nombre_proyecto']); ?>
                                        </a>
                                    </h3>

                                    <!-- Banner como enlace -->
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
                        <p>No hay proyectos disponibles.</p>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
    <script src="js/lista_proyectos.js"></script>
</body>
</html>
