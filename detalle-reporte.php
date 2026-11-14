
<?php

// Incluye las funciones necesarias para controlar las sesiones y los usuarios.
require_once __DIR__ . '/includes/funciones.php';

// Incluye la conexión con MySQL.
require_once __DIR__ . '/includes/conexion.php';

// Comprueba que el usuario haya iniciado sesión.
requiereLogin();

// Obtiene el ID del reporte desde la URL.
$id = (int)($_GET['id'] ?? 0);

// Comprueba que el ID sea válido.
if ($id <= 0) {
    http_response_code(404);
    exit('Reporte no encontrado.');
}

// Consulta el reporte y relaciona su categoría y el ciudadano que lo creó.
$consulta = $conexion->prepare(
    "SELECT
        r.id,
        r.sector,
        r.descripcion,
        r.imagen,
        r.estado,
        r.fecha_reporte,
        c.nombre AS categoria,
        u.nombre AS usuario_nombre,
        u.correo AS usuario_correo
    FROM reportes r
    INNER JOIN categorias c ON r.categoria_id = c.id
    INNER JOIN usuarios u ON r.usuario_id = u.id
    WHERE r.id = :id
    LIMIT 1"
);

// Ejecuta la consulta.
$consulta->execute([
    ':id' => $id
]);

// Obtiene los datos del reporte.
$reporte = $consulta->fetch();

// Si el reporte no existe, muestra un error 404.
if (!$reporte) {
    http_response_code(404);
    exit('Reporte no encontrado.');
}

// Obtiene los datos del usuario que inició sesión.
$usuario = obtenerUsuario();

// Incluye el encabezado general de la página.
require_once __DIR__ . '/includes/header.php';
?>

<section class="page-heading">
    <div>
        <h1>Detalle del reporte #<?= (int)$reporte['id'] ?></h1>
        <p>Información completa del daño registrado.</p>
    </div>

    <a href="<?= esAdmin() ? 'admin/reportes.php' : 'reportes.php' ?>" class="btn-secondary">
        Volver
    </a>
</section>

<section class="detail-container">

    <!-- Muestra la fotografía del reporte, si existe. -->
    <?php if (!empty($reporte['imagen'])): ?>
        <img
            src="uploads/<?= htmlspecialchars($reporte['imagen'], ENT_QUOTES, 'UTF-8') ?>"
            alt="Fotografía del daño"
            class="detail-image"
        >
    <?php endif; ?>

    <div class="detail-content">

        <!-- Categoría del reporte. -->
        <h2><?= htmlspecialchars($reporte['categoria'], ENT_QUOTES, 'UTF-8') ?></h2>

        <!-- Estado actual del reporte. -->
        <p>
            <strong>Estado:</strong>
            <span class="status">
                <?= htmlspecialchars($reporte['estado'], ENT_QUOTES, 'UTF-8') ?>
            </span>
        </p>

        <!-- Sector donde se encuentra el problema. -->
        <p>
            <strong>Sector:</strong>
            <?= htmlspecialchars($reporte['sector'], ENT_QUOTES, 'UTF-8') ?>
        </p>

        <!-- Descripción completa del problema. -->
        <p><strong>Descripción:</strong></p>

        <p>
            <?= nl2br(htmlspecialchars($reporte['descripcion'], ENT_QUOTES, 'UTF-8')) ?>
        </p>

        <!-- Fecha de creación del reporte. -->
        <p>
            <strong>Fecha:</strong>
            <?= htmlspecialchars($reporte['fecha_reporte'], ENT_QUOTES, 'UTF-8') ?>
        </p>

        <!-- La información del ciudadano solamente aparece para administradores. -->
        <?php if (esAdmin()): ?>

            <div class="report-user-info">
                <h3>Información del ciudadano</h3>

                <p>
                    <strong>Nombre:</strong>
                    <?= htmlspecialchars($reporte['usuario_nombre'], ENT_QUOTES, 'UTF-8') ?>
                </p>

                <p>
                    <strong>Correo:</strong>
                    <?= htmlspecialchars($reporte['usuario_correo'], ENT_QUOTES, 'UTF-8') ?>
                </p>
            </div>

            <a
                href="admin/editar-reporte.php?id=<?= (int)$reporte['id'] ?>"
                class="btn-primary"
            >
                Editar reporte
            </a>

        <?php endif; ?>

    </div>
</section>

<?php
// Incluye el pie de página.
require_once __DIR__ . '/includes/footer.php';
?>
