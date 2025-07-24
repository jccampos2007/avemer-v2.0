// php_mvc_app/public/js/main.js
// Archivo JavaScript general
$(document).ready(function () {
    // Código JavaScript/jQuery general para la aplicación
    console.log("main.js cargado.");

    // Ejemplo: Desaparecer mensajes flash al hacer clic
    $('#flash-message').on('click', function () {
        $(this).fadeOut(300, function () {
            $(this).remove();
        });
    });

});

// Función reusable para llenar un select
function fillSelect(selectId, tableName, currentValueId = null) {
    const $select = $(`#${selectId}`);
    // Limpiar opciones existentes, excepto la primera "Seleccione..."
    $select.find('option:not(:first)').remove();

    // Obtener el valor actual si estamos en modo edición
    const currentValue = currentValueId ? $(`#${currentValueId}`).val() : null;

    // Asume que BASE_URL está definida en el ámbito global de JS
    // Puedes definirla en tu layout PHP si no lo está: <script>const BASE_URL = '<?php echo BASE_URL; ?>';</script>
    const apiUrl = `${BASE_URL_JS}api/data/${tableName}`; // Ajusta esta URL si tu ruta es diferente
    $.ajax({
        url: apiUrl,
        method: 'GET',
        dataType: 'json',
        success: function (response) {
            if (response.success && response.data) {
                $.each(response.data, function (index, item) {
                    $select.append($('<option>', {
                        value: item.id,
                        text: item.text // Usamos 'text' porque PHP lo aliased como AS text
                    }));
                });

                // Si hay un valor actual (modo edición), seleccionarlo
                if (currentValue) {
                    $select.val(currentValue);
                }
            } else {
                console.error(`Error al cargar datos de ${tableName}:`, response.message);
                // Opcional: mostrar un mensaje de error al usuario
            }
        },
        error: function (xhr, status, error) {
            console.error(`Error AJAX al cargar datos de ${tableName}:`, status, error);
            // Opcional: mostrar un mensaje de error al usuario
        }
    });
}

