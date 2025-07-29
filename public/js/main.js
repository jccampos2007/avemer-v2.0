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

/**
 * Muestra un mensaje flash de éxito o error en la interfaz.
 * @param {string} type El tipo de mensaje ('success' o 'error').
 * @param {string} text El texto del mensaje a mostrar.
 */
function showFlashMessage(type, text) {
    // Eliminar cualquier mensaje flash existente para evitar duplicados
    const existingMsg = document.getElementById('flash-message');
    if (existingMsg) {
        existingMsg.remove();
    }

    // Crear el elemento del mensaje
    const msgDiv = document.createElement('div');
    msgDiv.id = 'flash-message';

    // Clases base para posicionamiento y estilo
    let classList = [
        'fixed', 'top-4', 'right-4', 'z-50', 'p-4', 'rounded-lg',
        'shadow-lg', 'text-white', 'transition-opacity', 'duration-300', 'ease-out', 'opacity-100'
    ];

    // Clases de color según el tipo de mensaje
    if (type === 'success') {
        classList.push('bg-green-500');
    } else if (type === 'error') {
        classList.push('bg-red-500');
    } else {
        // Por defecto, o si se pasa un tipo desconocido
        classList.push('bg-gray-700');
    }

    msgDiv.classList.add(...classList);
    msgDiv.textContent = text; // Usar textContent para seguridad (escapa HTML)

    // Añadir evento click para cerrar el mensaje manualmente
    msgDiv.onclick = function () {
        this.style.opacity = '0';
        setTimeout(() => this.remove(), 300); // Eliminar después de la transición de desvanecimiento
    };

    // Añadir el mensaje al cuerpo del documento
    document.body.appendChild(msgDiv);

    // Desaparecer el mensaje automáticamente después de 5 segundos
    setTimeout(function () {
        const currentMsg = document.getElementById('flash-message');
        if (currentMsg && currentMsg === msgDiv) { // Asegurarse de que es el mismo mensaje
            currentMsg.style.opacity = '0';
            setTimeout(() => currentMsg.remove(), 500); // Eliminar después de la transición
        }
    }, 5000); // 5 segundos
}
