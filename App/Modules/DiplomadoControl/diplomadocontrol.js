// php_mvc_app/App/Modules/DiplomadoControl/diplomadocontrol.js
$(document).ready(function () {
    console.log("diplomadocontrol.js cargado.");

    if ($('#formDiplomadoControl').length) {
        var $diplomadoAbiertoAutocomplete = $('#diplomado_abierto_autocomplete');
        var $tbody = $('#tbodyCapitulos');
        var $bulkDocente = $('#bulk_docente');
        var $bulkMensualidad = $('#bulk_mensualidad');
        var $btnApplyBulk = $('#btnApplyBulk');

        var $infoCostoInicial = $('#infoCostoInicial');
        var $infoCosto = $('#infoCostoInicial .costo-value');
        var $infoInicial = $('#infoCostoInicial .inicial-value');
        var $seccionTablaCapitulos = $('#seccionTablaCapitulos');

        function loadCapitulos(diplomadoAbiertoId) {
            if (!diplomadoAbiertoId) {
                $tbody.html('\
                    <tr id="rowPlaceholder">\
                        <td colspan="4" class="px-5 py-8 text-center text-gray-500">\
                            Por favor, seleccione una oferta de diplomado abierto arriba para desplegar sus cap\u00edtulos asociados.\
                        </td>\
                    </tr>\
                ');
                $infoCostoInicial.addClass('hidden');
                $seccionTablaCapitulos.addClass('hidden');
                return;
            }

            $.ajax({
                url: BASE_URL_JS + 'diplomadocontrol/getCapitulosAjax?diplomado_abierto_id=' + diplomadoAbiertoId,
                method: 'GET',
                dataType: 'json',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                success: function (resp) {
                    $infoCosto.text('$' + parseFloat(resp.costo || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                    $infoInicial.text('$' + parseFloat(resp.inicial || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                    $infoCostoInicial.removeClass('hidden');

                    var capitulos = resp.capitulos || [];
                    if (capitulos.length === 0) {
                        $seccionTablaCapitulos.addClass('hidden');
                        Swal.fire({
                            icon: 'warning',
                            title: 'Sin cap\u00edtulos',
                            text: 'Este diplomado no tiene cap\u00edtulos cargados.',
                            showCancelButton: true,
                            confirmButtonColor: '#d97706',
                            cancelButtonColor: '#6b7280',
                            confirmButtonText: 'Ir a cargar cap\u00edtulos',
                            cancelButtonText: 'Cerrar'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.open(BASE_URL_JS + 'capitulo/create', '_blank');
                            }
                        });
                        return;
                    }


                    $seccionTablaCapitulos.removeClass('hidden');
                    $tbody.empty();
                    var today = new Date().toISOString().split('T')[0];

                    $.each(capitulos, function (i, cap) {
                        var docenteOptions = '<option value="">Seleccione...</option>';
                        $.each(docentesList, function (j, doc) {
                            docenteOptions += '<option value="' + doc.id + '">' + doc.primer_apellido + ', ' + doc.primer_nombre + '</option>';
                        });

                        var selectedDocente = cap.docente_id || '';
                        if (selectedDocente) {
                            docenteOptions = docenteOptions.replace(
                                'value="' + selectedDocente + '"',
                                'value="' + selectedDocente + '" selected'
                            );
                        }

                        var fechaVal = cap.fecha || '';
                        var mensualidadVal = cap.mensualidad !== undefined ? parseFloat(cap.mensualidad).toFixed(2) : '0.00';

                        var tr = '\
                            <tr class="hover:bg-gray-50/50 transition">\
                                <td class="px-5 py-4 border-b border-gray-200 text-sm font-semibold text-gray-800">\
                                    Cap\u00edtulo ' + cap.numero + ':\
                                    <span class="text-xs font-normal text-gray-500 block">' + cap.nombre + '</span>\
                                </td>\
                                <td class="px-5 py-4 border-b border-gray-200 text-sm">\
                                    <input type="text" name="capitulos[' + cap.id + '][fecha]" value="' + fechaVal + '" class="fecha-input px-2.5 py-1.5 border border-gray-300 rounded text-sm focus:ring-1 focus:ring-blue-500 focus:outline-none">\
                                </td>\
                                <td class="px-5 py-4 border-b border-gray-200 text-sm">\
                                    <select name="capitulos[' + cap.id + '][docente_id]" class="docente-select px-2.5 py-1.5 border border-gray-300 rounded text-sm focus:ring-1 focus:ring-blue-500 focus:outline-none w-full">\
                                        ' + docenteOptions + '\
                                    </select>\
                                </td>\
                                <td class="px-5 py-4 border-b border-gray-200 text-sm">\
                                    <div class="relative rounded-md shadow-sm max-w-[120px]">\
                                        <div class="absolute inset-y-0 left-0 pl-2 flex items-center pointer-events-none">\
                                            <span class="text-gray-500 text-xs">$</span>\
                                        </div>\
                                        <input type="number" step="0.01" name="capitulos[' + cap.id + '][mensualidad]" value="' + mensualidadVal + '" class="mensualidad-input pl-5 pr-2 py-1.5 w-full border border-gray-300 rounded text-sm focus:ring-1 focus:ring-blue-500 focus:outline-none" min="0">\
                                    </div>\
                                </td>\
                            </tr>\
                        ';
                        $tbody.append(tr);
                    });

                    if (typeof flatpickr !== 'undefined') {
                        $tbody.find('.fecha-input').each(function () {
                            flatpickr(this, {
                                dateFormat: "Y-m-d",
                                altInput: true,
                                altFormat: "d F, Y",
                                locale: "es",
                            });
                        });
                        flatpickr.localize(flatpickr.l10ns.es);
                    }
                }
            });
        }

        if ($diplomadoAbiertoAutocomplete.length) {
            setupAutocomplete('diplomado_abierto_autocomplete', 'diplomado_abierto_id', 'diplomado_abierto', 3, {
                displayColumn: "CONCAT(numero, ' - ', (SELECT nombre FROM diplomado WHERE id = diplomado_abierto.diplomado_id))",
                status: 'all'
            });

            $('#diplomado_abierto_autocomplete').on('autocompleteselect', function (event, ui) {
                loadCapitulos(ui.item.id);
            });

            $('#diplomado_abierto_autocomplete').on('autocompletechange', function (event, ui) {
                if (!ui.item) {
                    loadCapitulos(null);
                }
            });
        }

        if (typeof flatpickr !== 'undefined') {
            $('.fecha-input').each(function () {
                flatpickr(this, {
                    dateFormat: "Y-m-d",
                    altInput: true,
                    altFormat: "d F, Y",
                    locale: "es",
                });
            });
            flatpickr.localize(flatpickr.l10ns.es);
        }

        if (typeof setupAutocomplete === 'function') {
            setupAutocomplete('bulk_docente_autocomplete', 'bulk_docente', 'docente', 3, {
                displayColumn: "CONCAT(primer_apellido, ', ', primer_nombre)"
            });
        }

        $btnApplyBulk.on('click', function () {
            var selectedDocente = $bulkDocente.val();
            var valMensualidad = $bulkMensualidad.val();

            if (selectedDocente) {
                $('.docente-select').val(selectedDocente);
            }

            if (valMensualidad !== '') {
                $('.mensualidad-input').val(parseFloat(valMensualidad).toFixed(2));
            }
        });
    } else {
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
                    Swal.fire('Error', 'Error al cargar los datos.', 'error');
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
                        var configurados = parseInt(row[5]) || 0;
                        if (total > 0) {
                            return '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">' +
                                '<svg class="w-3.5 h-3.5 mr-1 text-blue-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l5-5z" clip-rule="evenodd"></path></svg>' +
                                'Configurado (' + configurados + '/' + total + ' Cap\u00edtulos)' +
                                '</span>';
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
    }
});
