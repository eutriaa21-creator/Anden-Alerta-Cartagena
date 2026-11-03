
<?php
// Incluye las funciones necesarias para controlar las sesiones y los usuarios.
require_once __DIR__ . '/includes/funciones.php';

// Comprueba que el usuario haya iniciado sesión.
requiereLogin();

// Si el usuario es administrador, lo redirige al panel administrativo.
if (esAdmin()) {
    header('Location: admin/reportes.php');
    exit;
}

// Define la ruta del archivo JSON donde se almacenan los reportes.
$archivo = __DIR__ . '/data/reportes.json';

// Comprueba si el archivo existe.
// Si existe, lee su contenido y lo convierte en un arreglo de PHP.
// Si no existe o está vacío, utiliza un arreglo vacío.
$reportes = file_exists($archivo)
    ? (json_decode(file_get_contents($archivo), true) ?? [])
    : [];

// Ordena los reportes para mostrar primero los más recientes.
// La función strcmp compara las fechas de dos reportes.
usort($reportes, function ($a, $b) {
    return strcmp($b['fecha'] ?? '', $a['fecha'] ?? '');
});

// Incluye el encabezado general de la página.
// Este archivo contiene elementos compartidos, como la navegación.
require_once __DIR__ . '/includes/header.php';
?>

<!-- Encabezado principal de la sección de reportes. -->
<section class="page-heading">
    <div>
        <!-- Título y descripción de los reportes comunitarios. -->
        <h1>Reportes de la comunidad</h1>
        <p>Consulta los daños registrados por los ciudadanos de Cartagena.</p>
    </div>

    <!-- Enlace que lleva al formulario para crear un nuevo reporte. -->
    <a href="nuevo-reporte.php" class="btn-primary">
        + Nuevo reporte
    </a>
</section>

<!-- Contenedor donde se muestran todos los reportes registrados. -->
<section class="reports-container">

    <!-- Comprueba si todavía no existen reportes guardados. -->
    <?php if (empty($reportes)): ?>

        <!-- Mensaje que aparece cuando la comunidad aún no ha registrado reportes. -->
        <div class="empty-state">
            <h2>Aún no hay reportes</h2>
            <p>¡Sé el primero en registrar un problema!</p>

            <!-- Permite crear el primer reporte desde esta pantalla. -->
            <a href="nuevo-reporte.php" class="btn-primary">
                Crear reporte
            </a>
        </div>

    <?php else: ?>

        <!-- Si existen reportes, crea una cuadrícula para mostrarlos. -->
        <div class="reports-grid">

            <!-- Recorre el arreglo y obtiene cada reporte individualmente. -->
            <?php foreach ($reportes as $reporte): ?>

                <!-- Tarjeta individual que contiene la información de un reporte. -->
                <article class="report-card">

                    <!-- Comprueba si el reporte tiene una fotografía adjunta. -->
                    <?php if (!empty($reporte['foto'])): ?>

                        <!-- Muestra la fotografía almacenada en la carpeta uploads. -->
                        <img
                            src="uploads/<?= htmlspecialchars($reporte['foto']) ?>"
                            alt="Fotografía del reporte"
                            class="report-image"
                        >

                    <?php endif; ?>

                    <!-- Contenedor de los datos y la información del reporte. -->
                    <div class="report-card-content">

                        <!-- Muestra el estado actual del reporte. -->
                        <span class="status">
                            <?= htmlspecialchars($reporte['estado']) ?>
                        </span>

                        <!-- Muestra la categoría del problema reportado. -->
                        <h2><?= htmlspecialchars($reporte['categoria']) ?></h2>

                        <!-- Muestra el sector de Cartagena donde se encuentra el problema. -->
                        <p>
                            <strong>Sector:</strong>
                            <?= htmlspecialchars($reporte['sector']) ?>
                        </p>

                        <!-- Muestra la descripción del problema escrita por el ciudadano. -->
                        <p>
                            <?= htmlspecialchars($reporte['descripcion']) ?>
                        </p>

                        <!-- Muestra la fecha en la que se registró el reporte. -->
                        <p>
                            <small>
                                <?= htmlspecialchars($reporte['fecha']) ?>
                            </small>
                        </p>

                        <!-- Enlace a la página de detalles del reporte.
                             El ID permite identificar cuál reporte se debe consultar. -->
                        <a
                            href="detalle-reporte.php?id=<?= (int)$reporte['id'] ?>"
                            class="btn-secondary"
                        >
                            Ver detalle
                        </a>

                    </div>
                </article>

            <!-- Finaliza el recorrido de todos los reportes. -->
            <?php endforeach; ?>

        </div>

    <!-- Finaliza la condición que comprueba si existen reportes. -->
    <?php endif; ?>

</section>

<?php
// Incluye el pie de página y los elementos finales compartidos del sitio.
require_once __DIR__ . '/includes/footer.php';
?>
