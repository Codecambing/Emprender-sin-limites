<?php
include_once "servicios/conexion.php";

// Verificar si el usuario está logueado
if (isset($_SESSION['id_usuario'])) {
    $id_usuario = $_SESSION['id_usuario'];

    // Consultar el nombre, la foto de perfil y el privilegio del usuario
    $query = "SELECT nombre, fotoperfil, privilegio FROM usuarios WHERE id_usuario = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    $nombre_usuario = $user['nombre'] ?? 'Usuario';
    $foto_perfil = $user['fotoperfil'] ?? 'pfp/default-profile.jpg';
    $privilegio = $user['privilegio'] ?? 'usuario';
} else {
    $nombre_usuario = 'Invitado';
    $foto_perfil = 'pfp/default-profile.jpg';
    $privilegio = 'usuario';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">

   <!--=============== REMIXICONS ===============-->
   <link href="https://cdn.jsdelivr.net/npm/remixicon@3.2.0/fonts/remixicon.css" rel="stylesheet">

   <!--=============== CSS ===============-->
   <link rel="stylesheet" href="css/navbar.css">

   <title>Emprender sin limites</title>
</head>
<body>
   <!--=============== HEADER ===============-->
   <header class="header">
      <nav class="nav container">
         <div class="nav__data">
            <a href="inicio.php" class="nav__logo">
               <i class="ri-planet-line"></i>Emprender sin limites
            </a>
            
            <div class="nav__toggle" id="nav-toggle">
               <i class="ri-menu-line nav__burger"></i>
               <i class="ri-close-line nav__close"></i>
            </div>
         </div>

         <!--=============== NAV MENU ===============-->
         <div class="nav__menu" id="nav-menu">
            <ul class="nav__list">
               <li><a href="lista_proyectos.php" class="nav__link">Proyectos</a></li>
               <li><a href="inversiones_personales.php" class="nav__link">Inversiones</a></li>
               <li><a href="lista_usuarios.php" class="nav__link">Usuarios</a></li>

               <!-- Mostrar sección "Administración" si el usuario es administrador -->
               <!--=============== DROPDOWN 2 ===============-->
               <?php if ($privilegio === 'Administrador'): ?>
               <li class="dropdown__item">
                  <div class="nav__link">
                     Administración <i class="ri-arrow-down-s-line dropdown__arrow"></i>
                  </div>

                  <ul class="dropdown__menu">
                     <li>
                        <a href="dashboard.php" class="dropdown__link">
                           <i class="ri-settings-3-line"></i> Dashboard
                        </a>                          
                     </li>

                     <li>
                        <a href="reportes_de_cuentas.php" class="dropdown__link">
                             <i class="ri-user-line"></i> Reportes de Usuarios
                        </a>
                     </li>

                     <li>
                        <a href="revision_solicitudes.php" class="dropdown__link">
                           <i class="ri-message-3-line"></i> Solicitudes de retiro
                        </a>
                     </li>

                  </ul>
               </li>
               <?php endif; ?>

               <!--=============== DROPDOWN 2 ===============-->
               <li class="dropdown__item">
                  <div class="nav__link">
                     Cuenta <i class="ri-arrow-down-s-line dropdown__arrow"></i>
                  </div>

                  <ul class="dropdown__menu">
                     <li>
                        <a href="perfil.php" class="dropdown__link">
                           <i class="ri-user-line"></i> Perfil
                        </a>                          
                     </li>

                     <li>
                        <a href="mis_proyectos.php" class="dropdown__link">
                             <i class="ri-settings-3-line"></i> Mis proyectos
                        </a>
                     </li>

                     <li>
                        <a href="mis_solicitudes.php" class="dropdown__link">
                             <i class="ri-settings-3-line"></i> Mis solicitudes
                        </a>
                     </li>

                     <li>
                        <a href="bandeja_mensajes.php" class="dropdown__link">
                           <i class="ri-message-3-line"></i> Mensajes
                        </a>
                     </li>

                     <li>
                        <a href="servicios/cerrarsesion.php" class="dropdown__link">
                        <i class="ri-door-closed-line"></i> Cerrar sesión
                        </a>
                     </li>
                  </ul>
               </li>

               <!-- Nombre del usuario y foto de perfil -->
               <li class="nav__user">
                  <img src="<?php echo htmlspecialchars($foto_perfil); ?>" alt="Foto de perfil" class="nav__user-photo">
                  <span class="nav__user-name"><?php echo htmlspecialchars($nombre_usuario); ?></span>
               </li>
            </ul>
         </div>
      </nav>
   </header>

   <!--=============== MAIN JS ===============-->
   <script src="js/main.js"></script>
</body>
</html>