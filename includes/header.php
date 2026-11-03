
<?php
// Incluimos las funciones que necesitamos para identificar al usuario.
require_once __DIR__ . '/funciones.php';

// Obtenemos los datos del usuario que tiene la sesión iniciada.
$usuarioActual = obtenerUsuario();

// Revisamos si el usuario actual tiene el rol de administrador.
$esAdministrador = esAdmin();

// Guardamos la ruta principal del proyecto para utilizarla en los enlaces.
$base = '/AndenAlertaCartagena/';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <!-- Configuramos la codificación para mostrar correctamente los caracteres y las tildes. -->
    <meta charset="UTF-8">

    <!-- Hacemos que la página se adapte a diferentes tamaños de pantalla. -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Título que aparece en la pestaña del navegador. -->
    <title>Andén Alerta Cartagena</title>

    <!-- Conectamos el archivo CSS donde están los estilos de la plataforma. -->
    <link rel="stylesheet" href="<?= $base ?>css/style.css">
</head>

<body>

<!-- Encabezado principal que aparece en las páginas de la plataforma. -->
<header class="main-header">

    <div class="header-content">

        <!-- Logo o nombre de la plataforma que también funciona como enlace a los reportes. -->
        <a href="<?= $base ?>reportes.php" class="logo">
            Andén Alerta
        </a>

        <!-- Menú de navegación de acuerdo con el tipo de usuario. -->
        <nav>

            <!-- Comprobamos si hay un usuario que haya iniciado sesión. -->
            <?php if ($usuarioActual): ?>

                <!-- Si es administrador, mostramos las opciones del panel administrativo. -->
                <?php if ($esAdministrador): ?>

                    <a href="<?= $base ?>admin/reportes.php">Reportes</a>
                    <a href="<?= $base ?>admin/categorias.php">Categorías</a>

                <!-- Si es un ciudadano, mostramos las opciones de su cuenta. -->
                <?php else: ?>

                    <a href="<?= $base ?>reportes.php">Mis reportes</a>
                    <a href="<?= $base ?>nuevo-reporte.php">Crear reporte</a>

                <?php endif; ?>

                <!-- Enlace para cerrar la sesión y salir de la cuenta. -->
                <a href="<?= $base ?>logout.php">Cerrar sesión</a>

            <?php endif; ?>

        </nav>
    </div>
</header>

<!-- Aquí comienza el contenido principal de cada página. -->
<main class="main-content">
