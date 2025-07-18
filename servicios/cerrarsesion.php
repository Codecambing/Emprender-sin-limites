<?php
session_start();  // Iniciar la sesión

// Destruir todas las variables de sesión
session_unset(); 

// Destruir la sesión
session_destroy(); 

// Redirigir al usuario a la página de login
header("Location: ../login.php");
exit();  // Asegúrate de que el script se detenga aquí
?>