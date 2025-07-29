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


/**
 * Muestra un cuadro de diálogo de confirmación personalizado.
 * @param {string} message El mensaje a mostrar en el cuadro de diálogo.
 * @param {function} onConfirm Callback a ejecutar si el usuario confirma.
 * @param {function} [onCancel] Callback a ejecutar si el usuario cancela (opcional).
 */
function showConfirmationDialog(message, onConfirm, onCancel = null) {
    // Eliminar cualquier diálogo existente para evitar duplicados
    const existingDialog = document.getElementById('custom-confirm-dialog');
    if (existingDialog) {
        existingDialog.remove();
    }

    // Crear el overlay
    const overlay = document.createElement('div');
    overlay.id = 'custom-confirm-overlay';
    overlay.classList.add(
        'fixed', 'inset-0', 'bg-gray-900', 'bg-opacity-50', 'flex', 'items-center', 'justify-center', 'z-[1000]',
        'transition-opacity', 'duration-300', 'ease-out', 'opacity-0' // Inicialmente oculto
    );

    // Crear el contenedor del diálogo
    const dialog = document.createElement('div');
    dialog.id = 'custom-confirm-dialog';
    dialog.classList.add(
        'bg-white', 'p-6', 'rounded-lg', 'shadow-xl', 'max-w-sm', 'w-full', 'mx-4',
        'transform', 'scale-95', 'opacity-0', 'transition-all', 'duration-300', 'ease-out' // Inicialmente oculto
    );

    // Contenido del mensaje
    const messageP = document.createElement('p');
    messageP.classList.add('text-gray-800', 'text-lg', 'mb-6', 'text-center');
    messageP.textContent = message;

    // Contenedor de botones
    const buttonContainer = document.createElement('div');
    buttonContainer.classList.add('flex', 'justify-end', 'space-x-4');

    // Botón de Confirmar
    const confirmBtn = document.createElement('button');
    confirmBtn.classList.add(
        'bg-blue-600', 'hover:bg-blue-700', 'text-white', 'font-bold', 'py-2', 'px-4', 'rounded',
        'focus:outline-none', 'focus:shadow-outline', 'transition-colors', 'duration-200'
    );
    confirmBtn.textContent = 'Confirmar';
    confirmBtn.onclick = () => {
        onConfirm();
        closeDialog();
    };

    // Botón de Cancelar
    const cancelBtn = document.createElement('button');
    cancelBtn.classList.add(
        'bg-gray-400', 'hover:bg-gray-500', 'text-white', 'font-bold', 'py-2', 'px-4', 'rounded',
        'focus:outline-none', 'focus:shadow-outline', 'transition-colors', 'duration-200'
    );
    cancelBtn.textContent = 'Cancelar';
    cancelBtn.onclick = () => {
        if (onCancel) {
            onCancel();
        }
        closeDialog();
    };

    // Construir el diálogo
    buttonContainer.appendChild(cancelBtn);
    buttonContainer.appendChild(confirmBtn);
    dialog.appendChild(messageP);
    dialog.appendChild(buttonContainer);
    overlay.appendChild(dialog);
    document.body.appendChild(overlay);

    // Función para cerrar el diálogo
    function closeDialog() {
        overlay.style.opacity = '0';
        dialog.style.transform = 'scale(0.95)';
        dialog.style.opacity = '0';
        setTimeout(() => overlay.remove(), 300); // Eliminar después de la transición
    }

    // Mostrar el diálogo con animación
    setTimeout(() => {
        overlay.style.opacity = '1';
        dialog.style.transform = 'scale(1)';
        dialog.style.opacity = '1';
    }, 10); // Pequeño retraso para que la transición se aplique
}
