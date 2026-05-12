$(document).ready(function () {
    console.log("banco.js cargado.");

    const formBanco = $('#form_banco');
    if (formBanco.length === 0) {
        var bancoTable = $('#bancoTable').DataTable({
            "processing": true,
            "serverSide": true,
            "responsive": true,
            "ajax": {
                "url": BASE_URL_JS + "banco/getBancosData",
                "type": "POST",
                "error": function (xhr, error, thrown) {
                    console.log("Error en la solicitud AJAX de DataTables:", error, thrown);
                    alert('Error al cargar los datos de bancos.');
                }
            },
            "columns": [
                { "data": 0 },
                { "data": 1 },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function (data, type, row) {
                        return `
                        <a href="${BASE_URL_JS}banco/edit/${row[0]}" class="btn btn-default"><i class="fas fa-edit fs-5"></i></a>
                        <a href="${BASE_URL_JS}banco/delete/${row[0]}" class="btn btn-default delete-btn" data-id="${row[0]}"><i class="fas fa-trash-alt fs-5"></i></a>
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
            if (confirm('¿Está seguro de que desea eliminar este banco?')) {
                window.location.href = BASE_URL_JS + 'banco/delete/' + id;
            }
        });
    }
});
