
<?php
require_once __DIR__ . '/includes/funciones.php';
requiereLogin();

$archivo = __DIR__ . '/data/reportes.json';
$reportes = file_exists($archivo)
    ? (json_decode(file_get_contents($archivo), true) ?? [])
    : [];

$id = (int)($_GET['id'] ?? 0);
$reporte = null;

foreach ($reportes as $item) {
    if ((int)$item['id'] === $id) {
        $reporte = $item;
        break;
    }
}

$usuario = obtenerUsuario();

if (!$reporte) {
    http_response_code(404);
    exit('Reporte no encontrado.');
}

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

    <?php if (!empty($reporte['foto'])): ?>
        <img
            src="uploads/<?= htmlspecialchars($reporte['foto']) ?>"
            alt="Fotografía del daño"
            class="detail-image"
        >
    <?php endif; ?>

    <div class="detail-content">

        <h2><?= htmlspecialchars($reporte['categoria']) ?></h2>

        <p>
            <strong>Estado:</strong>
            <span class="status">
                <?= htmlspecialchars($reporte['estado']) ?>
            </span>
        </p>

        <p>
            <strong>Sector:</strong>
            <?= htmlspecialchars($reporte['sector']) ?>
        </p>

        <p><strong>Descripción:</strong></p>

        <p><?= nl2br(htmlspecialchars($reporte['descripcion'])) ?></p>

        <p>
            <strong>Fecha:</strong>
            <?= htmlspecialchars($reporte['fecha']) ?>
        </p>

        <?php if (esAdmin()): ?>

            <div class="report-user-info">
                <h3>Información del ciudadano</h3>

                <p>
                    <strong>Nombre:</strong>
                    <?= htmlspecialchars($reporte['usuario_nombre']) ?>
                </p>

                <p>
                    <strong>Correo:</strong>
                    <?= htmlspecialchars($reporte['usuario_correo']) ?>
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

<?php require_once __DIR__ . '/includes/footer.php'; ?>
