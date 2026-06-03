$(document).ready(function () {
    // Form-specific logic
    const formPago = $('#formPago');
    if (formPago.length) {
        if (typeof flatpickr !== 'undefined') {
            $('#fecha').flatpickr({ dateFormat: 'Y-m-d', altInput: true, altFormat: 'd/m/Y', locale: 'es' });
        }

        function loadCuotasByAlumno(alumnoId) {
            const cuotaSelect = $('#cuota_id');
            cuotaSelect.empty().append('<option value="">Cargando cuotas...</option>');

            if (!alumnoId) {
                cuotaSelect.empty().append('<option value="">Seleccione un alumno primero...</option>');
                return;
            }

            $.get(`${BASE_URL_JS}pago/getCuotasByAlumnoAjax`, { alumno_id: alumnoId }, function (resp) {
                cuotaSelect.empty().append('<option value="">Seleccione una cuota...</option>');
                if (resp.success && resp.data.length > 0) {
                    $.each(resp.data, function (i, cuota) {
                        cuotaSelect.append(`<option value="${cuota.id}" data-saldo="${cuota.saldo_pendiente}">${cuota.nombre} (${cuota.tipo_oferta_nombre}) - $${parseFloat(cuota.saldo_pendiente).toFixed(2)}</option>`);
                    });
                } else {
                    cuotaSelect.append('<option value="">No hay cuotas pendientes</option>');
                }
            });
        }

        if (typeof setupAutocomplete === 'function') {
            setupAutocomplete('alumno_autocomplete', 'alumno_id', 'alumno', 3, {
                displayColumn: "CONCAT(primer_nombre, ' ', primer_apellido, ', CI:', COALESCE(tipo_documento,''), ci_pasapote)"
            });
        }

        $('#alumno_autocomplete').on('autocompleteselect', function (event, ui) {
            loadCuotasByAlumno(ui.item.id);
        });

        $('#cuota_id').on('change', function () {
            const selected = $(this).find('option:selected');
            const saldo = selected.data('saldo');
            if (saldo) {
                $('#monto').val(saldo);
            }
        });

        function toggleBancoReferencia() {
            const fp = parseInt($('#forma_pago_id').val());
            const required = fp !== 4 && fp !== 6;
            $('#banco_id').prop('required', required);
            $('#numero_control').prop('required', required);
        }

        $('#forma_pago_id').on('change', toggleBancoReferencia);
        toggleBancoReferencia();

        formPago.on('submit', function (e) {
            e.preventDefault();
            const form = $(this);
            const url = form.attr('action') || window.location.href;
            $.ajax({
                url: url,
                type: 'POST',
                data: form.serialize(),
                dataType: 'json',
                success: function (resp) {
                    if (resp.success) {
                        Swal.fire({ icon: 'success', title: 'Éxito', text: resp.message, timer: 2000 }).then(function () {
                            window.location.href = BASE_URL_JS + 'pago';
                        });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: resp.message });
                    }
                },
                error: function () {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Ocurrió un error al procesar la solicitud.' });
                }
            });
        });
    }

    // List-specific logic
    const pagosTable = $('#pagosTable');
    if (pagosTable.length) {
        pagosTable.DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            dom: 'lBfrtip',
            buttons: [
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel"></i><span class="export-label"> Exportar a Excel</span>',
                    className: 'buttons-excel',
                    title: 'Listado de Pagos',
                    exportOptions: { columns: [1, 2, 3, 4, 5, 6, 7] },
                    action: newExportAction
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fas fa-file-pdf"></i><span class="export-label"> Exportar a PDF</span>',
                    className: 'buttons-pdf',
                    title: 'Listado de Pagos',
                    exportOptions: { columns: [1, 2, 3, 4, 5, 6, 7] },
                    action: newExportAction,
                    customize: function (doc) {
                        doc.content[1].table.widths = ['18%', '14%', '18%', '12%', '12%', '14%', '12%'];
                        doc.styles.tableHeader.fillColor = '#1e3a8a';
                        doc.styles.tableHeader.color = '#ffffff';
                    }
                }
            ],
            ajax: {
                url: `${BASE_URL_JS}pago/getPagosData`,
                type: 'POST',
                error: function () { console.error('Error al cargar pagos.'); }
            },
            columns: [
                { data: 0, visible: false, searchable: false },
                {
                    data: null,
                    render: function (data, type, row) {
                        var nombre = row[1] || '';
                        var ci = row[2] || '';
                        var telefono = row[3] || '';
                        var correo = row[4] || '';
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
                {
                    data: 5,
                    render: function (data, type) {
                        if (type === 'display' && data) {
                            return data.split(' - ').join('<br>');
                        }
                        return data || '';
                    }
                },
                {
                    data: 6,
                    render: function (data, type) {
                        if (type === 'display' && data) {
                            return data.split(' · ').join('<br>');
                        }
                        return data || '';
                    }
                },
                { data: 7, className: 'text-right font-semibold' },
                {
                    data: 8,
                    render: function (data, type) {
                        if (type === 'display' && data) {
                            return data.split('-').reverse().join('/');
                        }
                        return data || '';
                    }
                },
                {
                    data: 9,
                    orderable: true,
                    searchable: false,
                    className: 'text-center',
                    render: function (data) {
                        if (data === 'POR CONFIRMAR') return '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">POR CONFIRMAR</span>';
                        if (data === 'CONFIRMADO') return '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">CONFIRMADO</span>';
                        if (data === 'ELIMINADO') return '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">ELIMINADO</span>';
                        return data;
                    }
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    width: '1%',
                    className: 'actions-column',
                    render: function (data, type, row) {
                        const estatus = row[9];
                        let btns = '';
                        if (estatus === 'POR CONFIRMAR') {
                            if (PAGO_PERMISSIONS.modificar) {
                                btns += `<button class="btn-action btn-action-confirm" data-id="${row[0]}" title="Confirmar"><i class="fas fa-check"></i></button> `;
                            }
                            if (PAGO_PERMISSIONS.eliminar) {
                                btns += `<button class="btn-action btn-action-delete" data-id="${row[0]}" title="Eliminar"><i class="fas fa-trash-alt"></i></button> `;
                            }
                            if (PAGO_PERMISSIONS.modificar) {
                                btns += `<a href="${BASE_URL_JS}pago/edit/${row[0]}" class="btn-action btn-action-edit" title="Editar"><i class="fas fa-edit"></i></a>`;
                            }
                        } else if (estatus === 'CONFIRMADO') {
                            if (PAGO_PERMISSIONS.eliminar) {
                                btns += `<button class="btn-action btn-action-delete" data-id="${row[0]}" title="Eliminar"><i class="fas fa-trash-alt"></i></button> `;
                            }
                        }
                        return btns;
                    }
                }
            ],
            language: { url: 'https://cdn.datatables.net/plug-ins/2.3.2/i18n/es-ES.json' },
            autoWidth: false,
            order: [[0, 'desc']]
        });

        pagosTable.on('click', '.btn-action-confirm', function (e) {
            e.preventDefault();
            const id = $(this).data('id');
            Swal.fire({
                title: '¿Confirmar pago?',
                text: 'El pago pasará a estado CONFIRMADO.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, confirmar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `${BASE_URL_JS}pago/confirm/${id}`,
                        type: 'POST',
                        data: { csrf_token: CSRF_TOKEN },
                        success: function (response) {
                            const res = typeof response === 'string' ? JSON.parse(response) : response;
                            if (res.success) {
                                Swal.fire('¡Confirmado!', res.message, 'success');
                                pagosTable.DataTable().ajax.reload(null, false);
                            } else {
                                Swal.fire('Error', res.message, 'error');
                            }
                        },
                        error: function () {
                            Swal.fire('Error', 'Ocurrió un error al procesar la solicitud.', 'error');
                        }
                    });
                }
            });
        });

        pagosTable.on('click', '.btn-action-delete', function (e) {
            e.preventDefault();
            const id = $(this).data('id');
            Swal.fire({
                title: '¿Eliminar pago?',
                text: 'El pago pasará a estado ELIMINADO.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `${BASE_URL_JS}pago/softDelete/${id}`,
                        type: 'POST',
                        data: { csrf_token: CSRF_TOKEN },
                        success: function (response) {
                            const res = typeof response === 'string' ? JSON.parse(response) : response;
                            if (res.success) {
                                Swal.fire('¡Eliminado!', res.message, 'success');
                                pagosTable.DataTable().ajax.reload(null, false);
                            } else {
                                Swal.fire('Error', res.message, 'error');
                            }
                        },
                        error: function () {
                            Swal.fire('Error', 'Ocurrió un error al procesar la solicitud.', 'error');
                        }
                    });
                }
            });
        });
    }
});
