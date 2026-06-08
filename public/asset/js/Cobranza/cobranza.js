(function ($) {
    'use strict';

    const COBRANZA_PERMISSIONS = {
        crear: typeof COBRANZA_CREAR !== 'undefined' ? COBRANZA_CREAR : false,
        modificar: typeof COBRANZA_MODIFICAR !== 'undefined' ? COBRANZA_MODIFICAR : false,
        eliminar: typeof COBRANZA_ELIMINAR !== 'undefined' ? COBRANZA_ELIMINAR : false
    };

    $(document).ready(function () {
        var table = $('#cobranzaTable').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            stateSave: true,
            pageLength: 25,
            order: [[7, 'desc']],
            dom: '<"flex flex-col md:flex-row md:items-center md:justify-between gap-4 p-4"<"flex items-center gap-2"B><"flex-1"f>>rt<"flex flex-col md:flex-row md:items-center md:justify-between gap-4 p-4"<"flex items-center gap-2"l><"flex items-center gap-2"i><"flex items-center gap-2"p>>',
            buttons: [
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel"></i><span class="export-label"> Exportar a Excel</span>',
                    className: 'buttons-excel',
                    title: 'Cobranza - Deudas Pendientes',
                    exportOptions: { columns: [1, 2, 3, 4, 5, 6, 7] },
                    action: newExportAction
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fas fa-file-pdf"></i><span class="export-label"> Exportar a PDF</span>',
                    className: 'buttons-pdf',
                    title: 'Cobranza - Deudas Pendientes',
                    exportOptions: { columns: [1, 2, 3, 4, 5, 6, 7] },
                    action: newExportAction,
                    customize: function (doc) {
                        doc.content[1].table.widths = ['18%', '18%', '12%', '12%', '12%', '14%', '14%'];
                        doc.styles.tableHeader.fillColor = '#991b1b';
                        doc.styles.tableHeader.color = '#ffffff';
                    }
                }
            ],
            ajax: {
                url: `${BASE_URL_JS}cobranza/getData`,
                type: 'POST',
                error: function () { console.error('Error al cargar cobranza.'); }
            },
            columns: [
                { data: 0, visible: false, searchable: false },
                {
                    data: null,
                    render: function (data, type, row) {
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
                        return nombre;
                    }
                },
                { data: 6 },
                { data: 7 },
                { data: 8 },
                {
                    data: 9,
                    render: function (data, type) {
                        if (type === 'display' && data) {
                            return data.split('-').reverse().join('/');
                        }
                        return data || '';
                    }
                },
                {
                    data: 10,
                    render: function (data, type, row) {
                        if (type === 'display') {
                            var val = parseFloat(data);
                            var dias = Number(row[11]);
                            if (val > 0 && dias > 0) {
                                return '<span class="text-red-600 font-bold">$ ' + data + '</span>';
                            }
                            return '<span class="text-gray-700">$ ' + data + '</span>';
                        }
                        return data;
                    }
                },
                {
                    data: 11,
                    render: function (data, type) {
                        if (type === 'display') {
                            var days = Number(data);
                            if (days > 0) {
                                // Vencida
                                if (days >= 30) {
                                    return '<span class="text-red-700 font-bold bg-red-100 px-2 py-1 rounded">' + days + ' días vencido</span>';
                                } else if (days >= 15) {
                                    return '<span class="text-orange-600 font-bold bg-orange-100 px-2 py-1 rounded">' + days + ' días vencido</span>';
                                } else {
                                    return '<span class="text-yellow-600 font-bold bg-yellow-100 px-2 py-1 rounded">' + days + ' días vencido</span>';
                                }
                            } else if (days === 0) {
                                return '<span class="text-orange-500 font-bold bg-orange-50 px-2 py-1 rounded">Vence hoy</span>';
                            } else {
                                // Aún no vencida — mostrar días restantes sin signo negativo
                                var remaining = Math.abs(days);
                                if (remaining <= 7) {
                                    return '<span class="text-yellow-600 font-semibold bg-yellow-50 px-2 py-1 rounded">' + remaining + ' días restantes</span>';
                                }
                                return '<span class="text-green-600 font-semibold bg-green-50 px-2 py-1 rounded">' + remaining + ' días restantes</span>';
                            }
                        }
                        return Math.abs(Number(data));
                    }
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function (data, type, row) {
                        if (type !== 'display') return data;
                        var alumnoId = row[12];
                        var correo = row[5] || '';

                        var buttons = '<div class="flex gap-1 justify-center">';
                        if (correo) {
                            buttons += '<a href="mailto:' + encodeURIComponent(correo) + '?subject=Recordatorio de pago pendiente" class="btn-action btn-action-email" title="Enviar correo"><i class="fas fa-envelope"></i></a>';
                        }
                        buttons += '<a href="' + BASE_URL_JS + 'pago?alumno_id=' + alumnoId + '" class="btn-action btn-action-view" title="Ver pagos del alumno"><i class="fas fa-search-dollar"></i></a>';
                        buttons += '</div>';
                        return buttons;
                    }
                }
            ],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
            }
        });
        setupAlumnoCopyHandler('#cobranzaTable');
    });
})(jQuery);
