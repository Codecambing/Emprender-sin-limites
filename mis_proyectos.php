<?php
session_start();
include_once "navbar.php";
include_once "servicios/conexion.php"; // Incluir archivo de conexión

// Verificar si el usuario está logueado
if (!isset($_SESSION['id_usuario'])) {
    echo "<script>alert('Por favor inicie sesión');</script>";
    header("Location: login.php");
    exit();
}

$id_usuario_logueado = $_SESSION['id_usuario']; // Obtener el ID del usuario logueado

// Verificar si la conexión a la base de datos es exitosa
if (!$conn) {
    die("Error en la conexión a la base de datos.");
}

// Consultar los proyectos activos
$sql_activos = "SELECT 
                    p.id_proyectos, 
                    p.nombre_proyecto, 
                    p.descripcion, 
                    p.fecha_creacion, 
                    p.meta_financiamiento, 
                    p.banner, 
                    tp.nombre_id_tipo_proyecto
                FROM proyectos p
                JOIN tipo_proyecto tp ON p.tipo_proyecto_id_tipo_proyecto = tp.id_tipo_proyecto
                WHERE p.usuarios_id_usuario = ?
                AND p.id_proyectos NOT IN (
                    SELECT proyectos_id_proyecto 
                    FROM solicitudes_retiro
                )";

$stmt_activos = $conn->prepare($sql_activos);
$stmt_activos->bind_param("i", $id_usuario_logueado);
$stmt_activos->execute();
$result_activos = $stmt_activos->get_result();

$proyectos_activos = [];
if ($result_activos->num_rows > 0) {
    while ($row = $result_activos->fetch_assoc()) {
        $proyectos_activos[] = $row;
    }
}

// Consultar los proyectos finalizados
$sql_finalizados = "SELECT 
                        p.id_proyectos, 
                        p.nombre_proyecto, 
                        p.descripcion, 
                        p.fecha_creacion, 
                        p.meta_financiamiento, 
                        p.banner, 
                        tp.nombre_id_tipo_proyecto
                    FROM proyectos p
                    JOIN tipo_proyecto tp ON p.tipo_proyecto_id_tipo_proyecto = tp.id_tipo_proyecto
                    WHERE p.usuarios_id_usuario = ?
                    AND p.id_proyectos IN (
                        SELECT proyectos_id_proyecto 
                        FROM solicitudes_retiro
                        WHERE estado IN ('pendiente', 'completado')
                    )";

$stmt_finalizados = $conn->prepare($sql_finalizados);
$stmt_finalizados->bind_param("i", $id_usuario_logueado);
$stmt_finalizados->execute();
$result_finalizados = $stmt_finalizados->get_result();

$proyectos_finalizados = [];
if ($result_finalizados->num_rows > 0) {
    while ($row = $result_finalizados->fetch_assoc()) {
        $proyectos_finalizados[] = $row;
    }
}

$stmt_activos->close();
$stmt_finalizados->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Proyectos</title>
    <link rel="stylesheet" href="css/mis_proyectos.css">
</head>
<body>
    <div class="project-page">
        <h2 class="mis-proyectos">Mis Proyectos</h2>
        <div class="tabs">
            <button class="tab-button" onclick="openTab(event, 'activos')">Proyectos Activos</button>
            <button class="tab-button" onclick="openTab(event, 'finalizados')">Proyectos No Activos</button>
        </div>

        <div id="activos" class="tab-content">
            <ul id="projectListActivos">
                <?php if (!empty($proyectos_activos)): ?>
                    <?php foreach ($proyectos_activos as $proyecto): ?>
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
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No tienes proyectos activos.</p>
                <?php endif; ?>
            </ul>
        </div>

        <div id="finalizados" class="tab-content" style="display: none;">
            <ul id="projectListFinalizados">
                <?php if (!empty($proyectos_finalizados)): ?>
                    <?php foreach ($proyectos_finalizados as $proyecto): ?>
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
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No tienes proyectos pendientes ni completados.</p>
                <?php endif; ?>
            </ul>
        </div>
    </div>

    <script>
        function openTab(event, tabName) {
            var i, tabcontent, tabbuttons;
            tabcontent = document.getElementsByClassName("tab-content");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].style.display = "none";
            }
            tabbuttons = document.getElementsByClassName("tab-button");
            for (i = 0; i < tabbuttons.length; i++) {
                tabbuttons[i].className = tabbuttons[i].className.replace(" active", "");
            }
            document.getElementById(tabName).style.display = "block";
            event.currentTarget.className += " active";
        }
        document.querySelector(".tab-button").click(); // Abrir la primera pestaña por defecto
    </script>
</body>
</html>
