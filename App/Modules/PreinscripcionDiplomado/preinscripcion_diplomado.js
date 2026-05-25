// app/Modules/PreinscripcionDiplomado/Views/js/preinscripcion_diplomado.js
console.log('preinscripcion_diplomado.js cargado.');

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
        searchAlumnoForm.hide();
        alumnoDetailsSection.hide();
        diplomadosAbiertosSection.hide();
        currentAlumnoId = null;
        selectedAlumnoIdInput.val('');
    }

    // --- Búsqueda de Alumno ---
    searchAlumnoForm.on('submit', function (e) {
        e.preventDefault();
        const ciPasapote = $('#ci_pasapote_search').val().trim();
        if (ciPasapote === '') {
            $('#search_result_message').text('Por favor, ingrese un CI/Pasaporte.').removeClass('text-green-600').addClass('text-red-600');
            return;
        }

        $('#search_result_message').text('Buscando alumno...').removeClass('text-red-600').addClass('text-blue-600');

        $.ajax({
            url: `${BASE_URL_JS}preinscripcion_diplomado/search_alumno`,
            method: 'POST',
            data: { ci_pasapote: ciPasapote },
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
                    $('#search_result_message').text(response.message || 'Error al buscar alumno.').removeClass('text-green-600').addClass('text-red-600');
                }
            },
            error: function (xhr, status, error) {
                console.error('Error AJAX al buscar alumno:', status, error, xhr.responseText);
                $('#search_result_message').text('Error de conexión al buscar alumno.').removeClass('text-green-600').addClass('text-red-600');
            }
        });
    });

    // --- Creación de Alumno ---
    createAlumnoForm.on('submit', function (e) {
        e.preventDefault();
        const formData = $(this).serialize(); // Serializa todos los campos del formulario

        $.ajax({
            url: `${BASE_URL_JS}preinscripcion_diplomado/create_alumno`,
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    showFlashMessage('success', response.message);
                    showAlumnoDetails(response.alumno);
                    createAlumnoForm.hide();

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
            url: `${BASE_URL_JS}preinscripcion_diplomado/get_diplomados_abiertos`,
            method: 'POST',
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

        if (confirm('¿Estás seguro de que quieres pre-inscribir a este alumno en el diplomado seleccionado?')) {
            $.ajax({
                url: `${BASE_URL_JS}preinscripcion_diplomado/create`, // Envía al mismo endpoint POST de creación
                method: 'POST',
                data: {
                    alumno_id: alumnoId,
                    diplomado_abierto_id: diplomadoAbiertoId
                },
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        showFlashMessage('success', response.message);
                        showAlumnoSearch(); // Reiniciar el formulario después de la pre-inscripción exitosa
                    } else {
                        showFlashMessage('error', response.message);
                    }
                },
                error: function (xhr, status, error) {
                    console.error('Error AJAX al pre-inscribir:', status, error, xhr.responseText);
                    showFlashMessage('error', 'Error de conexión al realizar la pre-inscripción.');
                }
            });
        }
    });

    // --- Inicialización al cargar la página ---
    showAlumnoSearch(); // Mostrar la sección de búsqueda de alumno al inicio
});
