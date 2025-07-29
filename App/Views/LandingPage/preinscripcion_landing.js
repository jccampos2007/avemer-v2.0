// preinscripcion_landing.js (Versión Standalone)

// --- CONFIGURACIÓN ---
// ¡IMPORTANTE! DEBES CAMBIAR ESTA URL para que apunte a tu script PHP en el backend
// que manejará las solicitudes AJAX (ej. 'https://tu-dominio.com/api/preinscripcion.php')
const BACKEND_API_URL = 'http://localhost/your_app/api/preinscripcion_api.php'; // <--- ¡CAMBIA ESTO!

// --- FUNCIONES AUXILIARES DE UI ---

/**
 * Muestra un mensaje flash de éxito o error en la interfaz.
 * @param {string} type El tipo de mensaje ('success' o 'error' o 'info').
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
    } else if (type === 'info') {
        classList.push('bg-blue-500'); // Un color para mensajes informativos
    } else {
        classList.push('bg-gray-700'); // Por defecto
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


$(document).ready(function () {
    const searchAlumnoForm = $('#searchAlumnoForm');
    const alumnoDetailsSection = $('#alumnoDetails');
    const createAlumnoForm = $('#createAlumnoForm');
    const diplomadosAbiertosSection = $('#diplomadosAbiertosSection');
    const diplomadosAbiertosList = $('#diplomadosAbiertosList');
    const preinscribirBtn = $('#preinscribirBtn');
    const selectedAlumnoIdInput = $('#selectedAlumnoId'); // Campo oculto para el ID del alumno
    const selectedDiplomadoAbiertoIdInput = $('#selectedDiplomadoAbiertoId'); // Campo oculto para el ID del diplomado abierto

    let currentAlumnoId = null; // Almacena el ID del alumno seleccionado/creado

    // --- Funciones de Visibilidad ---
    function showAlumnoSearch() {
        searchAlumnoForm.show();
        alumnoDetailsSection.hide();
        createAlumnoForm.hide();
        diplomadosAbiertosSection.hide();
        $('#ci_pasapote_search').val('');
        $('#search_result_message').text('');
        currentAlumnoId = null;
        selectedAlumnoIdInput.val('');
        selectedDiplomadoAbiertoIdInput.val(''); // Limpiar selección de diplomado
        preinscribirBtn.prop('disabled', true).addClass('opacity-50 cursor-not-allowed'); // Deshabilitar botón
        diplomadosAbiertosList.empty(); // Limpiar lista de diplomados
    }

    function showAlumnoDetails(alumno) {
        alumnoDetailsSection.show();
        $('#alumno_ci_pasapote').text(alumno.ci_pasapote);
        $('#alumno_nombre_completo').text(`${alumno.primer_nombre} ${alumno.segundo_nombre || ''} ${alumno.primer_apellido} ${alumno.segundo_apellido || ''}`);
        $('#alumno_correo').text(alumno.correo || 'N/A');
        $('#alumno_celular').text(alumno.tlf_celular || 'N/A');
        currentAlumnoId = alumno.id;
        selectedAlumnoIdInput.val(alumno.id);
        searchAlumnoForm.hide();
        createAlumnoForm.hide();
        loadDiplomadosAbiertos(); // Cargar diplomados una vez que el alumno está listo
        diplomadosAbiertosSection.show();
    }

    function showCreateAlumnoForm(ciPasapote = '') {
        createAlumnoForm.show();
        $('#new_ci_pasapote').val(ciPasapote);
        // Limpiar otros campos del formulario de creación
        $('#new_primer_nombre').val('');
        $('#new_segundo_nombre').val('');
        $('#new_primer_apellido').val('');
        $('#new_segundo_apellido').val('');
        $('#new_correo').val('');
        $('#new_tlf_habitacion').val('');
        $('#new_tlf_trabajo').val('');
        $('#new_tlf_celular').val('');

        searchAlumnoForm.hide();
        alumnoDetailsSection.hide();
        diplomadosAbiertosSection.hide();
        currentAlumnoId = null;
        selectedAlumnoIdInput.val('');
        selectedDiplomadoAbiertoIdInput.val(''); // Limpiar selección de diplomado
        preinscribirBtn.prop('disabled', true).addClass('opacity-50 cursor-not-allowed'); // Deshabilitar botón
        diplomadosAbiertosList.empty(); // Limpiar lista de diplomados
    }

    // --- Búsqueda de Alumno ---
    searchAlumnoForm.on('submit', function (e) {
        e.preventDefault();
        const ciPasapote = $('#ci_pasapote_search').val().trim();
        if (ciPasapote === '') {
            showFlashMessage('error', 'Por favor, ingrese un CI/Pasaporte.');
            return;
        }

        $('#search_result_message').text('Buscando alumno...').removeClass('text-red-600 text-green-600').addClass('text-blue-600');

        $.ajax({
            url: BACKEND_API_URL, // Apunta a la URL de tu backend
            method: 'POST',
            data: { action: 'search_alumno', ci_pasapote: ciPasapote },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    if (response.found) {
                        $('#search_result_message').text('Alumno encontrado.').removeClass('text-red-600').addClass('text-green-600');
                        showAlumnoDetails(response.alumno);
                    } else {
                        $('#search_result_message').text(response.message).removeClass('text-green-600').addClass('text-red-600');
                        showCreateAlumnoForm(ciPasapote); // Mostrar formulario para crear
                    }
                } else {
                    showFlashMessage('error', response.message || 'Error al buscar alumno.');
                    $('#search_result_message').text(response.message || 'Error al buscar alumno.').removeClass('text-green-600').addClass('text-red-600');
                }
            },
            error: function (xhr, status, error) {
                console.error('Error AJAX al buscar alumno:', status, error, xhr.responseText);
                showFlashMessage('error', 'Error de conexión al buscar alumno.');
                $('#search_result_message').text('Error de conexión al buscar alumno.').removeClass('text-green-600').addClass('text-red-600');
            }
        });
    });

    // --- Creación de Alumno ---
    createAlumnoForm.on('submit', function (e) {
        e.preventDefault();
        const formData = $(this).serializeArray();
        formData.push({ name: 'action', value: 'create_alumno' }); // Añadir la acción al FormData

        $.ajax({
            url: BACKEND_API_URL, // Apunta a la URL de tu backend
            method: 'POST',
            data: $.param(formData), // Serializa el array de objetos a una cadena de consulta
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    showFlashMessage('success', response.message);
                    showAlumnoDetails(response.alumno);
                } else {
                    showFlashMessage('error', response.message || 'Error al crear alumno.');
                }
            },
            error: function (xhr, status, error) {
                console.error('Error AJAX al crear alumno:', status, error, xhr.responseText);
                showFlashMessage('error', 'Error de conexión al crear alumno.');
            }
        });
    });

    $('#cancelCreateAlumnoBtn').on('click', function () {
        showAlumnoSearch(); // Volver a la búsqueda
    });

    // --- Funcionalidad del botón 'Cambiar Alumno' ---
    $('#changeAlumnoBtn').on('click', function () {
        showAlumnoSearch(); // Vuelve a la sección de búsqueda de alumno
    });

    // --- Cargar Diplomados Abiertos ---
    function loadDiplomadosAbiertos() {
        diplomadosAbiertosList.empty().append('<p class="text-gray-500">Cargando diplomados abiertos...</p>');
        preinscribirBtn.prop('disabled', true).addClass('opacity-50 cursor-not-allowed');

        $.ajax({
            url: BACKEND_API_URL, // Apunta a la URL de tu backend
            method: 'POST',
            data: { action: 'get_diplomados_abiertos' },
            dataType: 'json',
            success: function (response) {
                diplomadosAbiertosList.empty();
                if (response.success && response.data.length > 0) {
                    response.data.forEach(diplomado => {
                        const item = `
                            <div class="border rounded-lg p-4 mb-2 cursor-pointer hover:bg-blue-50 transition-colors duration-200" data-id="${diplomado.id}">
                                <h4 class="font-semibold text-lg text-blue-700">${diplomado.numero} - ${diplomado.diplomado_nombre}</h4>
                                <p class="text-gray-600 text-sm">Sede: ${diplomado.sede_nombre}</p>
                                <p class="text-gray-600 text-sm">Fechas: ${diplomado.fecha_inicio} al ${diplomado.fecha_fin}</p>
                                <p class="text-gray-500 text-xs">${diplomado.nombre_carta_truncated}</p>
                            </div>
                        `;
                        diplomadosAbiertosList.append(item);
                    });
                } else {
                    diplomadosAbiertosList.append('<p class="text-gray-500">No hay diplomados abiertos disponibles.</p>');
                }
            },
            error: function (xhr, status, error) {
                console.error('Error AJAX al cargar diplomados abiertos:', status, error, xhr.responseText);
                diplomadosAbiertosList.empty().append('<p class="text-red-600">Error al cargar diplomados abiertos.</p>');
            }
        });
    }

    // --- Selección de Diplomado Abierto ---
    diplomadosAbiertosList.on('click', 'div', function () {
        diplomadosAbiertosList.find('div').removeClass('border-blue-500 bg-blue-100').addClass('border-gray-200');
        $(this).addClass('border-blue-500 bg-blue-100');
        selectedDiplomadoAbiertoIdInput.val($(this).data('id'));
        preinscribirBtn.prop('disabled', false).removeClass('opacity-50 cursor-not-allowed');
    });

    // --- Proceso de Pre-inscripción ---
    preinscribirBtn.on('click', function () {
        const alumnoId = selectedAlumnoIdInput.val();
        const diplomadoAbiertoId = selectedDiplomadoAbiertoIdInput.val();

        if (!alumnoId || !diplomadoAbiertoId) {
            showFlashMessage('error', 'Por favor, seleccione un alumno y un diplomado abierto.');
            return;
        }

        showConfirmationDialog(
            '¿Estás seguro de que quieres pre-inscribir a este alumno en el diplomado seleccionado?',
            function () {
                // Código a ejecutar si el usuario hace clic en "Confirmar"
                $.ajax({
                    url: BACKEND_API_URL, // Apunta a la URL de tu backend
                    method: 'POST',
                    data: {
                        action: 'process_preinscripcion', // Añadir la acción
                        alumno_id: alumnoId,
                        diplomado_abierto_id: diplomadoAbiertoId
                    },
                    dataType: 'json',
                    success: function (response) {
                        showFlashMessage(response.success ? 'success' : 'error', response.message);
                        if (response.success) {
                            showAlumnoSearch(); // Reiniciar el formulario después de la pre-inscripción exitosa
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('Error AJAX al pre-inscribir:', status, error, xhr.responseText);
                        showFlashMessage('error', 'Error de conexión al realizar la pre-inscripción.');
                    }
                });
            },
            function () {
                // Código a ejecutar si el usuario hace clic en "Cancelar" (opcional)
                showFlashMessage('info', 'Pre-inscripción cancelada.');
            }
        );
    });

    // --- Inicialización al cargar la página ---
    showAlumnoSearch(); // Mostrar la sección de búsqueda de alumno al inicio
});
