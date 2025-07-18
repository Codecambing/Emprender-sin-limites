<?php
session_start();
include_once "servicios/conexion.php";

// Verificar si el usuario está logueado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

// Verificar si se ha pasado el ID de inversión y si es un número válido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID de inversión no válido.");
}

$id_inversion = intval($_GET['id']);
$id_usuario_actual = $_SESSION['id_usuario']; // ID del usuario logueado

// Consultar los datos de la inversión para asegurarnos de que es válida
$sql = "SELECT p.id_proyectos, ip.monto
        FROM inversion_proyectos ip
        JOIN proyectos p ON ip.proyectos_id_proyectos = p.id_proyectos
        WHERE ip.id_inversion = ? AND ip.usuarios_id_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id_inversion, $id_usuario_actual);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Inversión no encontrada o no pertenece a este usuario.");
}

// Obtenemos los datos necesarios de la inversión
$inversion = $result->fetch_assoc();
$id_proyecto = $inversion['id_proyectos'];

// Iniciar una transacción para garantizar la consistencia de los datos
$conn->begin_transaction();

try {
    // Marcar la inversión como anulada y guardar la fecha de anulación
    $fecha_anulacion = date('Y-m-d H:i:s'); // Fecha y hora actuales
    $sql_anular = "UPDATE inversion_proyectos SET fecha_anulacion = ? WHERE id_inversion = ?";
    $stmt_anular = $conn->prepare($sql_anular);
    $stmt_anular->bind_param("si", $fecha_anulacion, $id_inversion);
    $stmt_anular->execute();

    // Confirmar la transacción si todo fue exitoso
    $conn->commit();

    // Redirigir al usuario a la página de inversiones
    header("Location: inversiones_personales.php");
    exit();
} catch (Exception $e) {
    // Si ocurre algún error, hacemos un rollback de la transacción
    $conn->rollback();
    die("Error al anular la inversión: " . $e->getMessage());
}
?>
