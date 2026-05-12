$(document).ready(function () {
    console.log("sede.js cargado.");

    const formSede = $('#form_sede');
    if (formSede.length > 0) {
        // Llenar select de estados
        if (typeof fillSelect === 'function') {
            // Nota: Se asume que existe un endpoint o lógica para 'estado' en el sistema
            fillSelect('estado_id', 'estado', 'estado_current');
        }
    } else {
        var sedeTable = $('#sedeTable').DataTable({
            "processing": true,
            "serverSide": true,
            "responsive": true,
            "ajax": {
                "url": BASE_URL_JS + "sede/getSedesData",
                "type": "POST",
                "error": function (xhr, error, thrown) {
                    console.log("Error en la solicitud AJAX de DataTables:", error, thrown);
                    alert('Error al cargar los datos de sedes.');
                }
            },
            "columns": [
                { "data": 0 },
                { "data": 1 },
                { "data": 2 },
                { "data": 3 },
                { "data": 4 },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function (data, type, row) {
                        return `
                        <a href="${BASE_URL_JS}sede/edit/${row[0]}" class="btn btn-default"><i class="fas fa-edit fs-5"></i></a>
                        <a href="${BASE_URL_JS}sede/delete/${row[0]}" class="btn btn-default delete-btn" data-id="${row[0]}"><i class="fas fa-trash-alt fs-5"></i></a>
                    `;
                    }
                }
            ],
            "language": {
                "url": "https://cdn.datatables.net/plug-ins/2.3.2/i18n/es-ES.json"
            }
        });

        $(document).on('click', '.delete-btn', function (e) {
            e.preventDefault();
            const id = $(this).data('id');
            if (confirm('¿Está seguro de que desea eliminar esta sede?')) {
                window.location.href = BASE_URL_JS + 'sede/delete/' + id;
            }
        });
    }
});
