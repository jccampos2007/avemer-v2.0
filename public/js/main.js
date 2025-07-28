// php_mvc_app/public/js/main.js
// Archivo JavaScript general
const CKEDITOR_TOOLBAR_OPTIONS = {
    items: [
        'heading', // Títulos (H1, H2, etc.)
        '|',
        'bold', // Negrita
        'italic', // Cursiva
        'link', // Enlaces
        'bulletedList', // Lista con viñetas
        'numberedList', // Lista numerada
        '|',
        'outdent', // Disminuir sangría
        'indent', // Aumentar sangría
        '|',
        'blockQuote', // Cita en bloque
        'undo', // Deshacer
        'redo', // Rehacer
        '|',
        'insertTable', // Insertar tabla
        'mediaEmbed', // Insertar medios (videos, etc.)
    ]
};

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
function fillSelect(selectId, tableName, currentValueId = null, displayColumn = 'nombre', status = '') {
    const $select = $(`#${selectId}`);
    const currentValue = currentValueId ? $(`#${currentValueId}`).val() : null;
    const statusFilter = status !== '' ? `&statusFilter=${status}` : '';
    const apiUrl = `${BASE_URL_JS}api/data/${tableName}?displayColumn=${displayColumn}${statusFilter}`;

    $select.find('option:not(:first)').remove();

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
