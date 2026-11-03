
<?php

// Incluye las funciones necesarias para manejar las sesiones del usuario.
require_once __DIR__ . '/includes/funciones.php';

// Elimina todas las variables almacenadas en la sesión actual.
// Esto borra los datos del usuario que inició sesión.
session_unset();

// Destruye la sesión actual y cierra el acceso autenticado.
session_destroy();

// Redirige al usuario a la página de inicio de sesión.
header('Location: index.php');

// Detiene la ejecución del archivo después de realizar la redirección.
exit;
