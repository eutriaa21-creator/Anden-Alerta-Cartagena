<?php

// Cargamos la configuración local de conexión.
$config = require __DIR__ . '/config.local.php';

// Obtenemos los datos de conexión.
$host = $config['host'];
$puerto = $config['puerto'];
$baseDatos = $config['baseDatos'];
$usuario = $config['usuario'];
$contrasena = $config['contrasena'];

try {

    $conexion = new PDO(
        "mysql:host=$host;port=$puerto;dbname=$baseDatos;charset=utf8mb4",
        $usuario,
        $contrasena
    );

    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conexion->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    die("Error de conexión con la base de datos.");

}