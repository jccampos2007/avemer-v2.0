// php_mvc_app/App/Modules/DiplomadoControl/diplomadocontrol.js
$(document).ready(function () {
    console.log("diplomadocontrol.js cargado.");

    var table = $('#diplomadocontrolTable').DataTable({
        "processing": true,
        "serverSide": true,
        "responsive": true,
        "dom": 'lBfrtip',
        "buttons": [
            {
                extend: 'excelHtml5',
                text: '<i class="fas fa-file-excel"></i><span class="export-label"> Exportar a Excel</span>',
                className: 'buttons-excel',
                title: 'Control de Diplomados',
                exportOptions: {
                    columns: [1, 2, 3, 4]
                },
                action: newExportAction
            },
            {
                extend: 'pdfHtml5',
                text: '<i class="fas fa-file-pdf"></i><span class="export-label"> Exportar a PDF</span>',
                className: 'buttons-pdf',
                title: 'Control de Diplomados',
                exportOptions: {
                    columns: [1, 2, 3, 4]
                },
                action: newExportAction,
                customize: function (doc) {
                    doc.content[1].table.widths = ['20%', '30%', '20%', '30%'];
                    doc.styles.tableHeader.fillColor = '#1e3a8a';
                    doc.styles.tableHeader.color = '#ffffff';
                }
            }
        ],
        "ajax": {
            "url": BASE_URL_JS + "diplomadocontrol/data",
            "type": "POST",
            "error": function (xhr, error, thrown) {
                console.log("Error en la solicitud AJAX de DataTables:", error, thrown);
                showFlashMessage('error', 'Error al cargar los datos.');
            }
        },
        "columns": [
            {
                data: 0,
                visible: false,
                searchable: false
            },
            { data: 1, responsivePriority: 1 },
            { data: 2, responsivePriority: 2 },
            {
                data: 3,
                responsivePriority: 3,
                render: function (data, type, row) {
                    var statusClass = 'bg-gray-100 text-gray-700';
                    var status = (data || '').toLowerCase();
                    if (status === 'activo') {
                        statusClass = 'bg-green-100 text-green-800';
                    } else if (status === 'inactivo') {
                        statusClass = 'bg-red-100 text-red-800';
                    }
                    return '<span class="inline-flex px-2.5 py-1 text-xs font-semibold rounded-full ' + statusClass + '">' + data + '</span>';
                }
            },
            {
                data: null,
                orderable: false,
                searchable: false,
                responsivePriority: 4,
                render: function (data, type, row) {
                    var total = parseInt(row[4]) || 0;
                    var generados = parseInt(row[5]) || 0;
                    if (total > 0) {
                        return '<div class="flex flex-col space-y-1">' +
                            '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 self-start">' +
                            '<svg class="w-3.5 h-3.5 mr-1 text-blue-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l5-5z" clip-rule="evenodd"></path></svg>' +
                            'Configurado (' + total + ' Capítulos)' +
                            '</span>' +
                            '<span class="text-xs text-gray-500">Generados: ' + generados + ' / ' + total + '</span>' +
                            '</div>';
                    }
                    return '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">' +
                        '<svg class="w-3.5 h-3.5 mr-1 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>' +
                        'Sin Detalle / Pendiente' +
                        '</span>';
                }
            },
            {
                data: null,
                orderable: false,
                searchable: false,
                responsivePriority: 10000,
                width: "1%",
                className: "actions-column",
                render: function (data, type, row) {
                    return '<div class="flex gap-2 justify-center">' +
                        '<a href="' + BASE_URL_JS + 'diplomadocontrol/edit/' + row[0] + '" class="btn-action btn-action-edit" title="Gestionar Detalle de Control">' +
                        '<i class="fas fa-edit"></i>' +
                        '</a>' +
                        '</div>';
                }
            }
        ],
        "language": {
            "url": "https://cdn.datatables.net/plug-ins/2.3.2/i18n/es-ES.json"
        }
    });
});
