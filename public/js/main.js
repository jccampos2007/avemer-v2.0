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
function fillSelect(selectId, tableName, currentValueId = null, displayColumn = 'nombre') {
    const $select = $(`#${selectId}`);
    // Limpiar opciones existentes, excepto la primera "Seleccione..."
    $select.find('option:not(:first)').remove();

    // Obtener el valor actual si estamos en modo edición
    const currentValue = currentValueId ? $(`#${currentValueId}`).val() : null;

    // Construir la URL con el parámetro displayColumn
    // Asume que BASE_URL_JS está definida en el ámbito global de JS
    // Puedes definirla en tu layout PHP si no lo está: <script>const BASE_URL_JS = '<?php echo BASE_URL; ?>';</script>
    const apiUrl = `${BASE_URL_JS}api/data/${tableName}?displayColumn=${displayColumn}`;

    $.ajax({
        url: apiUrl,
        method: 'GET',
        dataType: 'json',
        success: function (response) {
            console.log(`Datos cargados para ${tableName} (columna: ${displayColumn}):`, response);
            if (response.success && response.data) {
                $.each(response.data, function (index, item) {
                    let displayText = item.text;

                    if (displayText && typeof displayText === 'string' && displayText.startsWith(', ')) {
                        displayText = displayText.substring(2);
                    }

                    $select.append($('<option>', {
                        value: item.id,
                        text: displayText
                    }));
                });

                // Si hay un valor actual (modo edición), seleccionarlo
                if (currentValue) {
                    $select.val(currentValue);
                }
            } else {
                console.error(`Error al cargar datos de ${tableName} (columna: ${displayColumn}):`, response.message);
                // Opcional: mostrar un mensaje de error al usuario
            }
        },
        error: function (xhr, status, error) {
            console.error(`Error AJAX al cargar datos de ${tableName} (columna: ${displayColumn}):`, status, error);
            // Opcional: mostrar un mensaje de error al usuario
        }
    });
}
