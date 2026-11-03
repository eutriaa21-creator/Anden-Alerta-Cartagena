
<?php

// Iniciamos la sesión solamente si todavía no está activa.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Esta función revisa si hay un usuario que haya iniciado sesión.
function usuarioAutenticado() {
    return isset($_SESSION['usuario']);
}

// Obtenemos los datos del usuario que tiene la sesión iniciada.
// Si no hay ningún usuario, devuelve null.
function obtenerUsuario() {
    return $_SESSION['usuario'] ?? null;
}

// Revisamos si el usuario que inició sesión tiene el rol de administrador.
function esAdmin() {
    return usuarioAutenticado() &&
           $_SESSION['usuario']['rol'] === 'admin';
}

// Esta función sirve para evitar que alguien sin iniciar sesión
// pueda entrar a las páginas que necesitan una cuenta.
function requiereLogin() {
    if (!usuarioAutenticado()) {

        // Si no ha iniciado sesión, lo enviamos a la página de inicio.
        header('Location: /AndenAlertaCartagena/index.php');
        exit;
    }
}

// Esta función comprueba que el usuario tenga permisos de administrador.
function requiereAdmin() {

    // Primero revisamos que haya iniciado sesión.
    requiereLogin();

    // Si no es administrador, lo enviamos a la página de reportes.
    if (!esAdmin()) {
        header('Location: /AndenAlertaCartagena/reportes.php');
        exit;
    }
}
