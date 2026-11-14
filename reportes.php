
<?php

// Incluye las funciones necesarias para controlar las sesiones y los usuarios.
require_once __DIR__ . '/includes/funciones.php';

// Incluye la conexión con la base de datos MySQL.
require_once __DIR__ . '/includes/conexion.php';

// Comprueba que el usuario haya iniciado sesión.
requiereLogin();

// Si el usuario es administrador, lo redirige al panel administrativo.
if (esAdmin()) {
    header('Location: admin/reportes.php');
    exit;
}

// Inicializa el arreglo que almacenará los reportes.
$reportes = [];

try {

    // Consulta los reportes y obtiene el nombre de su categoría.
    // También permite mostrar primero los reportes más recientes.
    $consulta = $conexion->prepare(
        "SELECT
            r.id,
            r.sector,
            r.descripcion,
            r.imagen,
            r.estado,
            r.fecha_reporte,
            c.nombre AS categoria
         FROM reportes r
         INNER JOIN categorias c ON r.categoria_id = c.id
         ORDER BY r.fecha_reporte DESC"
    );

    // Ejecuta la consulta.
    $consulta->execute();

    // Obtiene todos los reportes encontrados.
    $reportes = $consulta->fetchAll();

} catch (PDOException $e) {

    // Muestra un mensaje si ocurre un problema al consultar los reportes.
    die('No se pudieron cargar los reportes. Inténtalo nuevamente.');

}

// Incluye el encabezado general de la página.
require_once __DIR__ . '/includes/header.php';
?>

<!-- Encabezado principal de la sección de reportes. -->
<section class="page-heading">
    <div>
        <h1>Reportes de la comunidad</h1>
        <p>Consulta los daños registrados por los ciudadanos de Cartagena.</p>
    </div>

    <a href="nuevo-reporte.php" class="btn-primary">
        + Nuevo reporte
    </a>
</section>

<!-- Contenedor donde se muestran todos los reportes registrados. -->
<section class="reports-container">

    <?php if (empty($reportes)): ?>

        <!-- Mensaje que aparece cuando todavía no existen reportes. -->
        <div class="empty-state">
            <h2>Aún no hay reportes</h2>
            <p>¡Sé el primero en registrar un problema!</p>

            <a href="nuevo-reporte.php" class="btn-primary">
                Crear reporte
            </a>
        </div>

    <?php else: ?>

        <!-- Cuadrícula donde se muestran los reportes. -->
        <div class="reports-grid">

            <?php foreach ($reportes as $reporte): ?>

                <article class="report-card">

                    <!-- Comprueba si el reporte tiene una fotografía adjunta. -->
                    <?php if (!empty($reporte['imagen'])): ?>

                        <img
                            src="uploads/<?= htmlspecialchars($reporte['imagen'], ENT_QUOTES, 'UTF-8') ?>"
                            alt="Fotografía del reporte"
                            class="report-image"
                        >

                    <?php endif; ?>

                    <div class="report-card-content">

                        <!-- Muestra el estado actual del reporte. -->
                        <span class="status">
                            <?= htmlspecialchars($reporte['estado'], ENT_QUOTES, 'UTF-8') ?>
                        </span>

                        <!-- Muestra la categoría del problema. -->
                        <h2>
                            <?= htmlspecialchars($reporte['categoria'], ENT_QUOTES, 'UTF-8') ?>
                        </h2>

                        <!-- Muestra el sector donde se encuentra el problema. -->
                        <p>
                            <strong>Sector:</strong>
                            <?= htmlspecialchars($reporte['sector'], ENT_QUOTES, 'UTF-8') ?>
                        </p>

                        <!-- Muestra la descripción del reporte. -->
                        <p>
                            <?= htmlspecialchars($reporte['descripcion'], ENT_QUOTES, 'UTF-8') ?>
                        </p>

                        <!-- Muestra la fecha de creación del reporte. -->
                        <p>
                            <small>
                                <?= htmlspecialchars($reporte['fecha_reporte'], ENT_QUOTES, 'UTF-8') ?>
                            </small>
                        </p>

                        <!-- Enlace a los detalles del reporte. -->
                        <a
                            href="detalle-reporte.php?id=<?= (int)$reporte['id'] ?>"
                            class="btn-secondary"
                        >
                            Ver detalle
                        </a>

                    </div>
                </article>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</section>

<?php
// Incluye el pie de página y los elementos finales compartidos del sitio.
require_once __DIR__ . '/includes/footer.php';
?>
