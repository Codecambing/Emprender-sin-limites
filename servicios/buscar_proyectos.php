<?php
include_once "servicios/conexion.php";

if (isset($_GET['query'])) {
    $query = $_GET['query'];
    $con = mysqli_connect($host, $user, $pass, $db);

    if (!$con) {
        die("Error de conexión: " . mysqli_connect_error());
    }

    $query = mysqli_real_escape_string($con, $query);
    $sql = "SELECT nombre_proyecto FROM proyectos WHERE nombre_proyecto LIKE '%$query%'";
    $result = mysqli_query($con, $sql);

    $proyectos = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $proyectos[] = $row['nombre_proyecto'];
    }

    echo json_encode($proyectos);
    mysqli_close($con);
}
?>