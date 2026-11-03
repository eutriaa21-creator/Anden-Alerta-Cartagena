
<?php

// Activamos la visualización de errores para poder detectar problemas mientras desarrollamos.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluimos las funciones del proyecto y verificamos que sea administrador.
require_once __DIR__ . '/../includes/funciones.php';
requiereAdmin();

// Ubicamos el archivo JSON donde guardamos las categorías.
$archivo = __DIR__ . '/../data/categorias.json';

// Si todavía no existe el archivo, lo creamos con unas categorías iniciales.
if (!file_exists($archivo)) {
    file_put_contents($archivo, json_encode([
        'Hueco en el andén',
        'Andén deteriorado',
        'Obstáculo peatonal',
        'Rampa dañada',
        'Otro'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// Cargamos las categorías guardadas en el archivo.
$categorias = json_decode(file_get_contents($archivo), true) ?? [];

// Esta variable nos permite guardar los mensajes de error del formulario.
$error = '';

// Revisamos si se envió algún formulario desde esta página.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Comprobamos si el administrador está intentando agregar una categoría.
    if (isset($_POST['nueva_categoria'])) {

        // Obtenemos el nombre escrito y quitamos los espacios sobrantes.
        $nueva = trim($_POST['nueva_categoria']);

        // No permitimos que se agreguen categorías sin nombre.
        if ($nueva === '') {
            $error = 'Escribe el nombre de la categoría.';

        // Revisamos que la categoría no esté registrada anteriormente.
        } elseif (in_array($nueva, $categorias, true)) {
            $error = 'Esta categoría ya existe.';

        } else {
            // Si todo está correcto, agregamos la nueva categoría al arreglo.
            $categorias[] = $nueva;
        }
    }

    // Comprobamos si el administrador está intentando eliminar una categoría.
    if (isset($_POST['eliminar_categoria'])) {

        // Recibimos el nombre de la categoría que se quiere eliminar.
        $eliminar = $_POST['eliminar_categoria'];

        // Filtramos el arreglo para quitar la categoría seleccionada.
        $categorias = array_values(array_filter(
            $categorias,
            fn($categoria) => $categoria !== $eliminar
        ));
    }

    // Si no hubo errores, guardamos los cambios realizados.
    if ($error === '') {
        file_put_contents(
            $archivo,
            json_encode($categorias, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            LOCK_EX
        );

        // Recargamos la página para mostrar la lista actualizada.
        header('Location: categorias.php');
        exit;
    }
}

// Incluimos el encabezado general de la página.
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Título y descripción de la sección de categorías. -->
<section class="page-heading">
    <div>
        <h1>Gestión de categorías</h1>
        <p>Administra los tipos de daños que pueden reportar los ciudadanos.</p>
    </div>
</section>

<!-- Contenedor principal donde se organiza la gestión de categorías. -->
<section class="categories-container">

    <!-- Si ocurrió algún error, mostramos el mensaje correspondiente. -->
    <?php if ($error): ?>
        <div class="alert-error">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <!-- Formulario para registrar una nueva categoría. -->
    <div class="form-container">
        <h2>Agregar categoría</h2>

        <form method="POST" class="main-form">

            <!-- Campo donde se escribe el nombre de la nueva categoría. -->
            <label for="nueva_categoria">Nombre de la categoría</label>

            <input
                type="text"
                id="nueva_categoria"
                name="nueva_categoria"
                placeholder="Ej. Señalización dañada"
                required
            >

            <!-- Botón que envía el formulario para agregar la categoría. -->
            <button type="submit" class="btn-primary">
                Agregar categoría
            </button>
        </form>
    </div>

    <!-- Tabla donde mostramos todas las categorías que están registradas. -->
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>N.º</th>
                    <th>Nombre de la categoría</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody>

                <!-- Si no hay categorías guardadas, mostramos un mensaje. -->
                <?php if (empty($categorias)): ?>
                    <tr>
                        <td colspan="3">No hay categorías registradas.</td>
                    </tr>
                <?php else: ?>

                    <!-- Recorremos las categorías para mostrarlas una por una. -->
                    <?php foreach ($categorias as $indice => $categoria): ?>
                        <tr>

                            <!-- Mostramos la posición de cada categoría en la lista. -->
                            <td><?= $indice + 1 ?></td>

                            <!-- Mostramos el nombre de la categoría. -->
                            <td><?= htmlspecialchars($categoria) ?></td>

                            <td>
                                <!-- Formulario para eliminar la categoría seleccionada. -->
                                <form method="POST" class="inline-form"
                                    onsubmit="return confirm('¿Eliminar esta categoría?');">

                                    <!-- Enviamos el nombre de la categoría que queremos eliminar. -->
                                    <input
                                        type="hidden"
                                        name="eliminar_categoria"
                                        value="<?= htmlspecialchars($categoria) ?>"
                                    >

                                    <button type="submit" class="btn-delete">
                                        Eliminar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                <?php endif; ?>
            </tbody>
        </table>
    </div>

</section>

<!-- Incluimos el pie de página general del proyecto. -->
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
