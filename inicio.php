<?php
session_start();
include_once("navbar.php");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio - Emprender sin límites</title>
    <link rel="stylesheet" href="css/inicio.css">
</head>
<body>

<div class="welcome-container">
    <?php if (!isset($_SESSION['id_usuario'])): ?>
        <!-- Usuario no logueado -->
        <h1>Bienvenido a Emprender sin límites</h1>
        <p>
            <!-- Placeholder de texto de bienvenida y sobre el proyecto -->
            Bienvenido a nuestra plataforma de emprendimiento, donde conectamos ideas con inversiones y ayudamos a los emprendedores a alcanzar sus sueños.
        </p>
        
        <div class="buttons-container">
            <button class="register-button" onclick="window.location.href='register.php'">Crea una cuenta</button>
            <p>o si ya tienes una cuenta</p>
            <button class="login-button" onclick="window.location.href='login.php'">Inicia sesión acá</button>
        </div>

    <?php else: ?>
        <!-- Usuario logueado -->
        <h1>Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre']); ?>, ¿qué deseas hacer hoy?</h1>
    <?php endif; ?>
</div>

</body>
</html>