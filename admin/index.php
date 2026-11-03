
<?php

// Incluimos las funciones que usamos para controlar las sesiones y los permisos.
require_once __DIR__ . '/../includes/funciones.php';

// Comprobamos que el usuario haya iniciado sesión como administrador.
requiereAdmin();

// Si todo está correcto, lo enviamos directamente al panel de reportes.
header('Location: reportes.php');

// Detenemos la ejecución después de realizar la redirección.
exit;
