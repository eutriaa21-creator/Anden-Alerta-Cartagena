
document.addEventListener('DOMContentLoaded', function () {

    // Confirmación antes de eliminar elementos
    const botonesEliminar = document.querySelectorAll('[data-confirm]');

    botonesEliminar.forEach(function (boton) {
        boton.addEventListener('click', function (event) {
            const mensaje = boton.dataset.confirm || '¿Deseas continuar?';

            if (!confirm(mensaje)) {
                event.preventDefault();
            }
        });
    });

    // Mostrar nombre del archivo seleccionado
    const camposArchivo = document.querySelectorAll('input[type="file"]');

    camposArchivo.forEach(function (campo) {
        campo.addEventListener('change', function () {
            if (campo.files.length > 0) {
                const etiqueta = document.querySelector(
                    `[data-file-name="${campo.id}"]`
                );

                if (etiqueta) {
                    etiqueta.textContent = campo.files[0].name;
                }
            }
        });
    });

});
