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

    // Configuración global para DataTables: usar showFlashMessage en lugar de alert()
    if (typeof $.fn.dataTable !== 'undefined') {
        $.fn.dataTable.ext.errMode = 'none';

        $(document).on('error.dt', function (e, settings, techNote, message) {
            console.error('DataTables error:', message);
            
            let errorText = 'Ocurrió un error al cargar la tabla.';
            // Intentar extraer el error personalizado del JSON del servidor
            if (settings && settings.jqXHR && settings.jqXHR.responseJSON && settings.jqXHR.responseJSON.error) {
                errorText = settings.jqXHR.responseJSON.error;
            } else if (message) {
                // Extraer un mensaje más limpio si viene el prefijo de DataTables
                let parts = message.split(' - ');
                errorText = parts.length > 1 ? parts[1] : message;
            }
            
            showFlashMessage('error', errorText);
        });
    }

    // Ejemplo: Desaparecer mensajes flash al hacer clic
    $('#flash-message').on('click', function () {
        $(this).fadeOut(300, function () {
            $(this).remove();
        });
    });

    // Inicializar el toggle de colapsar formulario (acordeón) de forma global
    // Se activa automáticamente en cualquier módulo que tenga los IDs requeridos
    initFormToggle();

    // Click en placeholder badge para insertar en CKEditor
    $(document).on('click', '.placeholder-badge', function () {
        const text = $(this).data('placeholder');
        const editor = window.nombreCartaEditor;
        if (editor) {
            editor.model.change(writer => {
                writer.insertText(text, editor.model.document.selection.getFirstPosition());
            });
            editor.editing.view.focus();
        }
    });

    // Toggle entre CKEditor y textarea HTML
    $(document).on('click', '.toggle-html-btn', function () {
        const textarea = document.getElementById('nombre_carta');
        const editor = window.nombreCartaEditor;
        if (!editor || !textarea) return;

        if ($(this).text().trim() === 'Ver HTML') {
            textarea.value = editor.getData();
            $(textarea).show();
            if (editor.ui && editor.ui.view && editor.ui.view.element) {
                $(editor.ui.view.element).hide();
            }
            $(this).text('Ocultar HTML');
        } else {
            editor.setData(textarea.value);
            $(textarea).hide();
            if (editor.ui && editor.ui.view && editor.ui.view.element) {
                $(editor.ui.view.element).show();
            }
            $(this).text('Ver HTML');
        }
    });

    // Sincronizar textarea → editor antes de cualquier submit si el textarea está visible
    $(document).on('submit', 'form', function () {
        const textarea = document.getElementById('nombre_carta');
        const editor = window.nombreCartaEditor;
        if (textarea && editor && $(textarea).is(':visible')) {
            editor.setData(textarea.value);
        }
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
 * Inicializa el toggle de colapsar/expandir formulario (estilo acordeón con switch).
 * Se llama automáticamente desde document.ready en cualquier vista que contenga
 * los IDs estándar del formulario colapsable. No requiere ningún código adicional
 * en los módulos individuales para funcionar.
 *
 * IDs HTML requeridos en la vista:
 *   - toggle_form_collapse_btn   → el elemento switch (button/div)
 *   - toggle_form_collapse_dot   → el punto deslizante del switch
 *   - toggle_form_text           → etiqueta de texto "Ocultar / Mostrar"
 *   - form_collapsible_wrapper   → contenedor con grid-template-rows para la animación
 *   - form_collapsible_content   → contenido colapsable (hijo directo del wrapper)
 *   - form_main_card             → tarjeta principal del formulario (opcional)
 *   - form_header_row            → fila de cabecera del formulario (opcional)
 */
function initFormToggle() {
    const toggleBtn   = document.getElementById('toggle_form_collapse_btn');
    const toggleDot   = document.getElementById('toggle_form_collapse_dot');
    const toggleText  = document.getElementById('toggle_form_text');
    const collWrapper = document.getElementById('form_collapsible_wrapper');
    const collContent = document.getElementById('form_collapsible_content');
    const mainCard    = document.getElementById('form_main_card');
    const headerRow   = document.getElementById('form_header_row');

    // Si no existe el botón o el wrapper, esta vista no usa el toggle — salir silenciosamente
    if (!toggleBtn || !collWrapper || !collContent) return;

    let isCollapsed = false;

    // Clic en la etiqueta de texto también activa el switch
    if (toggleText) {
        toggleText.addEventListener('click', function () { toggleBtn.click(); });
    }

    toggleBtn.addEventListener('click', function () {
        isCollapsed = !isCollapsed;

        if (isCollapsed) {
            // --- SWITCH: activar (verde oscuro) ---
            toggleBtn.classList.remove('bg-green-200');
            toggleBtn.classList.add('bg-green-600');
            toggleDot.classList.remove('translate-x-0');
            toggleDot.classList.add('translate-x-8');
            toggleBtn.setAttribute('aria-checked', 'true');
            if (toggleText) toggleText.textContent = 'Mostrar';

            // --- TARJETA: estilo colapsado ---
            if (mainCard) {
                mainCard.classList.remove('border-gray-100', 'shadow-md');
                mainCard.classList.add('border-gray-800', 'shadow-sm');
            }
            if (headerRow) {
                headerRow.classList.remove('border-b', 'pb-3', 'mb-6');
                headerRow.classList.add('mb-0');
            }

            // --- COLAPSAR contenido ---
            collContent.style.overflow = 'hidden';
            collWrapper.style.gridTemplateRows = '0fr';

        } else {
            // --- SWITCH: desactivar (verde claro) ---
            toggleBtn.classList.remove('bg-green-600');
            toggleBtn.classList.add('bg-green-200');
            toggleDot.classList.remove('translate-x-8');
            toggleDot.classList.add('translate-x-0');
            toggleBtn.setAttribute('aria-checked', 'false');
            if (toggleText) toggleText.textContent = 'Ocultar';

            // --- TARJETA: restaurar estilo original ---
            if (mainCard) {
                mainCard.classList.remove('border-gray-800', 'shadow-sm');
                mainCard.classList.add('border-gray-100', 'shadow-md');
            }
            if (headerRow) {
                headerRow.classList.remove('mb-0');
                headerRow.classList.add('border-b', 'pb-3', 'mb-6');
            }

            // --- EXPANDIR contenido ---
            collWrapper.style.gridTemplateRows = '1fr';

            // Restaurar overflow para que selects/autocompletes no queden recortados
            setTimeout(function () {
                if (!isCollapsed) collContent.style.overflow = 'visible';
            }, 300);
        }
    });
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

/**
 * Función auxiliar global para exportar todos los datos en Server-Side Processing.
 * Cambia temporalmente la longitud de la página a -1 (todos) y dispara la acción nativa de DataTables.
 * Útil para múltiples módulos que requieran exportación de listados completos.
 */
function newExportAction(e, dt, button, config) {
    var self = this;
    var oldStart = dt.settings()[0]._iDisplayStart;
    var oldLength = dt.settings()[0]._iDisplayLength;

    // Mostrar un indicador de carga al usuario mientras se exporta todo
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Preparando exportación',
            text: 'Por favor, espere un momento...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    }

    dt.one('preXhr', function (e, s, data) {
        // Justo antes de enviar la petición, le decimos al backend que queremos TODOS los registros
        data.start = 0;
        data.length = -1; // -1 indica que se deben omitir los límites de paginación
    });

    dt.one('draw', function (e, settings) {
        // Una vez recibidos los datos completos, ejecutamos la exportación original
        if (button[0].className.indexOf('buttons-excel') >= 0) {
            $.fn.dataTable.ext.buttons.excelHtml5.action.call(self, e, dt, button, config);
        } else if (button[0].className.indexOf('buttons-pdf') >= 0) {
            $.fn.dataTable.ext.buttons.pdfHtml5.action.call(self, e, dt, button, config);
        }

        // Cerrar indicador de carga
        if (typeof Swal !== 'undefined') {
            Swal.close();
        }

        // Restablecer la tabla a su paginación y estado original
        dt.one('preXhr', function (e, s, data) {
            settings._iDisplayStart = oldStart;
            settings._iDisplayLength = oldLength;
            data.start = oldStart;
            data.length = oldLength;
        });

        // Recargar el grid con el tamaño original de paginación de manera silenciosa
        setTimeout(function() {
            dt.ajax.reload(null, false);
        }, 100);
    });

    // Disparar la petición AJAX interna con la nueva longitud (-1)
    dt.ajax.reload();
}
/**
 * Renderiza la celda de la columna "Alumno" en las tablas de inscripciones.
 * Muestra nombre, cédula, teléfono (link), email (link) y botón copiar.
 */
function renderAlumnoColumn(type, row) {
    var nombre = row[2] || '';
    var ci = row[3] || '';
    var telefono = row[4] || '';
    var correo = row[5] || '';
    if (type === 'display') {
        var telHref = telefono.replace(/[^0-9+]/g, '');
        var copyText = nombre + '\nC.I.: ' + ci + '\nTel: ' + telefono + '\nEmail: ' + correo;
        var copyBtn = '<button class="btn-copy-alumno" data-copy="' + copyText.replace(/"/g, '&quot;') + '" title="Copiar datos" style="background:none;border:none;cursor:pointer;color:#6b7280;padding:0 4px;vertical-align:middle"><i class="fas fa-copy"></i></button>';
        var html = '<div class="alumno-info" style="line-height:1.8">';
        html += '<span class="font-bold">' + nombre + '</span> ' + copyBtn + '<br>';
        html += 'C.I.: ' + ci + '<br>';
        if (telefono && telefono !== 'N/A') {
            html += '<a href="tel:' + telHref + '" style="text-decoration:none;color:#2563eb">' + telefono + '</a><br>';
        } else {
            html += telefono + '<br>';
        }
        if (correo && correo !== 'N/A') {
            html += '<a href="mailto:' + encodeURIComponent(correo) + '" style="text-decoration:none;color:#2563eb">' + correo + '</a>';
        } else {
            html += correo;
        }
        html += '</div>';
        return html;
    }
    return nombre + ' - C.I.: ' + ci + ' - Tel: ' + telefono + ' - Email: ' + correo;
}

/**
 * Configura el manejador de clic para el botón de copiar datos del alumno.
 * @param {string} tableSelector - Selector jQuery de la tabla DataTable.
 */
function setupAlumnoCopyHandler(tableSelector) {
    $(tableSelector).on("click", ".btn-copy-alumno", function (e) {
        e.stopPropagation();
        var text = $(this).data('copy');
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(function () {
                Swal.fire({title: 'Copiado', text: 'Datos copiados al portapapeles', icon: 'success', timer: 1500, showConfirmButton: false});
            });
        } else {
            var $temp = $('<textarea>');
            $('body').append($temp);
            $temp.val(text).select();
            document.execCommand('copy');
            $temp.remove();
            Swal.fire({title: 'Copiado', text: 'Datos copiados al portapapeles', icon: 'success', timer: 1500, showConfirmButton: false});
        }
    });
}

/**
 * Configura un autocomplete de jQuery UI con búsqueda AJAX.
 * @param {string} inputId - ID del input visible.
 * @param {string} hiddenId - ID del input oculto para almacenar el ID seleccionado.
 * @param {string} endpoint - Nombre del recurso (ej: 'alumno' → 'api/search/alumno') o ruta completa (ej: 'api/users/search').
 * @param {number} [minLength=2] - Caracteres mínimos para iniciar la búsqueda.
 * @param {object|null} [extraData=null] - Datos adicionales a enviar en la petición (ej: { displayColumn: '...' }).
 */
function setupAutocomplete(inputId, hiddenId, endpoint, minLength, extraData) {
    if (typeof minLength === 'undefined' || minLength === null) minLength = 2;
    if (typeof extraData === 'undefined' || extraData === null) extraData = {};

    var url = endpoint.indexOf('/') > -1
        ? `${BASE_URL_JS}${endpoint}`
        : `${BASE_URL_JS}api/search/${endpoint}`;

    $('#' + inputId).autocomplete({
        minLength: minLength,
        source: function (request, response) {
            $.ajax({
                url: url,
                dataType: "json",
                data: $.extend({ term: request.term }, extraData),
                success: function (data) { response(data); },
                error: function () { response([]); }
            });
        },
        select: function (event, ui) {
            $('#' + hiddenId).val(ui.item.id);
            $(this).val(ui.item.label);
            return false;
        },
        change: function (event, ui) {
            if (!ui.item) { $('#' + hiddenId).val(''); }
        }
    });
}