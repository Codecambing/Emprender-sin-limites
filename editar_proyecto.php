<?php
session_start();
include_once "navbar.php";
include_once "servicios/conexion.php";

// Verificar si el usuario está logueado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

// Verificar si el ID del proyecto está presente en la URL
if (!isset($_GET['id'])) {
    echo "Error: No se proporcionó el ID del proyecto.";
    exit();
}

$id_proyecto = $_GET['id'];

// Obtener los datos del proyecto desde la base de datos
$sql = "SELECT * FROM proyectos WHERE id_proyectos = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_proyecto);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "Error: Proyecto no encontrado.";
    exit();
}

$proyecto = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Proyecto</title>
    <link rel="stylesheet" href="css/editar_proyecto.css">
</head>
<body>
    <div class="edit-container">
        <h1>Editar Proyecto: <?= htmlspecialchars($proyecto['nombre_proyecto']); ?></h1>
        <form id="formulario" action="procesar_edicion.php" method="POST">
            <!-- Incluir el ID del proyecto como un campo oculto -->
            <input type="hidden" name="id_proyecto" value="<?= $proyecto['id_proyectos']; ?>">

            <label for="nombre_proyecto">Nombre del Proyecto:</label>
            <input type="text" id="nombre_proyecto" name="nombre_proyecto" value="<?= htmlspecialchars($proyecto['nombre_proyecto']); ?>" required>

            <label for="descripcion">Descripción:</label>
            <textarea id="descripcion" name="descripcion" required><?= htmlspecialchars($proyecto['descripcion']); ?></textarea>

            <label for="detalle">Detalles publicación:</label>
            <div id="bbcode-editor">
                <div id="bbcode-toolbar">
                    <button type="button" onclick="insertBBCode('[b]', '[/b]')">Negrita</button>
                    <button type="button" onclick="insertBBCode('[i]', '[/i]')">Cursiva</button>
                    <button type="button" onclick="insertBBCode('[u]', '[/u]')">Subrayado</button>
                    <button type="button" onclick="insertBBCode('[url]', '[/url]')">Enlace</button>
                    <button type="button" onclick="insertBBCode('[img]', '[/img]')">Imagen</button>
                    <button type="button" onclick="insertBBCode('[left]', '[/left]')">Alinear Izquierda</button>
                    <button type="button" onclick="insertBBCode('[center]', '[/center]')">Centrar</button>
                    <button type="button" onclick="insertBBCode('[right]', '[/right]')">Alinear Derecha</button>
                    <button type="button" onclick="insertBBCode('[color=red]', '[/color]')">Color Rojo</button>
                    <button type="button" onclick="insertBBCode('[size=20]', '[/size]')">Tamaño 20</button>
                </div>
                <textarea id="detalle" name="detalle" rows="10" required><?= htmlspecialchars($proyecto['detalle']); ?></textarea>
            </div>

            <label for="meta_financiamiento">Meta Financiera:</label>
            <input type="number" id="meta_financiamiento" name="meta_financiamiento" value="<?= $proyecto['meta_financiamiento']; ?>" required>

            <div class="reward-container">
                <label>
                    <input type="checkbox" id="enable-rewards" onclick="toggleRewards()"> Activar sistema de recompensas
                </label>
                <div id="rewards-section" class="hidden">
                    <label for="reward-levels">Número de recompensas:</label>
                    <select id="reward-levels" name="reward_levels" onchange="updateRewardFields()">
                        <option value="0">Selecciona</option>
                        <option value="1">1 Recompensa</option>
                        <option value="2">2 Recompensas</option>
                        <option value="3">3 Recompensas</option>
                    </select>
                    <div id="reward-fields" class="reward-fields hidden">
                        <!-- Campos para recompensas -->
                        <div id="reward-1" class="hidden">
                            <label for="recompensa_menor">Monto Recompensa 1:</label>
                            <input type="number" name="recompensa_menor" id="recompensa_menor" min="1">
                            <label for="info_recompensa_menor">Descripción Recompensa 1:</label>
                            <textarea name="info_recompensa_menor" id="info_recompensa_menor"></textarea>
                        </div>
                        <div id="reward-2" class="hidden">
                            <label for="recompensa_media">Monto Recompensa 2:</label>
                            <input type="number" name="recompensa_media" id="recompensa_media" min="1">
                            <label for="info_recompensa_media">Descripción Recompensa 2:</label>
                            <textarea name="info_recompensa_media" id="info_recompensa_media"></textarea>
                        </div>
                        <div id="reward-3" class="hidden">
                            <label for="recompensa_mayor">Monto Recompensa 3:</label>
                            <input type="number" name="recompensa_mayor" id="recompensa_mayor" min="1">
                            <label for="info_recompensa_mayor">Descripción Recompensa 3:</label>
                            <textarea name="info_recompensa_mayor" id="info_recompensa_mayor"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botones para guardar o cancelar -->
            <button type="submit">Guardar Cambios</button>
            <button type="button" onclick="window.location.href='lista_proyectos.php';">Cancelar</button>
        </form>
    </div>
    <script src="script.js" defer></script>
    <script>
        function toggleRewards() {
            const rewardsSection = document.getElementById('rewards-section');
            rewardsSection.classList.toggle('hidden');
        }

        function updateRewardFields() {
            const rewardLevels = parseInt(document.getElementById('reward-levels').value);
            const rewardFields = document.getElementById('reward-fields');
            rewardFields.classList.remove('hidden');

            // Mostrar u ocultar los campos según el número de recompensas
            for (let i = 1; i <= 3; i++) {
                document.getElementById(`reward-${i}`).classList.toggle('hidden', i > rewardLevels);
            }
        }
    </script>
</html>