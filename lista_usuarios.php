<?php
session_start();
include_once "navbar.php";
include_once "servicios/conexion.php";

// Consultar los usuarios registrados que no estén dados de baja
$query = "SELECT id_usuario, nombre, fotoperfil FROM usuarios WHERE dado_de_baja != 'SI'";
$result = mysqli_query($conn, $query);

// Verificar si hay usuarios
if (!$result || mysqli_num_rows($result) === 0) {
    echo "<p>No hay usuarios registrados.</p>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Usuarios</title>
    <link rel="stylesheet" href="css/lista_usuarios.css">
</head>
<body>
    <div class="user-list-container">
        <h1>Lista de Usuarios</h1>
        <div class="user-list">
            <?php while ($user = mysqli_fetch_assoc($result)) { ?>
                <div class="user-item">
                    <a href="perfil_usuario.php?id=<?php echo $user['id_usuario']; ?>" class="user-link">
                        <img src="<?php echo htmlspecialchars($user['fotoperfil']) ?: 'pfp/default-profile.jpg'; ?>" alt="Foto de perfil de <?php echo htmlspecialchars($user['nombre']); ?>" class="user-photo">
                        <p class="user-name"><?php echo htmlspecialchars($user['nombre']); ?></p>
                    </a>
                </div>
            <?php } ?>
        </div>
    </div>
</body>
</html>
<?php
mysqli_close($conn);
?>
