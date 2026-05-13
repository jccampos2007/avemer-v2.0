$(document).ready(function () {
    console.log("grupo.js cargado.");

    const isListPage = $('#grupoTable').length > 0;
    const isFormPage = $('#form_grupo_page').length > 0;

    if (isListPage) {
        const grupoTable = $('#grupoTable').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": BASE_URL_JS + "grupo/data",
                "type": "POST"
            },
            "columns": [
                { "data": 0 },
                { "data": 1 },
                { "data": 2 },
                { "data": 3 },
                {
                    data: null,
                    orderable: false,
                    render: function (data, type, row) {
                        return `
                            <div class="flex gap-2 justify-center">
                                <a href="${BASE_URL_JS}grupo/edit/${row[0]}" class="btn btn-default" title="Editar y Permisos">
                                    <i class="fas fa-edit fs-5"></i>
                                </a>
                                <button class="btn btn-default btn-delete text-red-600" title="Eliminar" data-id="${row[0]}">
                                    <i class="fas fa-trash-alt fs-5"></i>
                                </button>
                            </div>
                        `;
                    }
                }
            ],
            "language": {
                "url": "https://cdn.datatables.net/plug-ins/2.3.2/i18n/es-ES.json"
            }
        });

        // Delete button click
        $(document).on('click', '.btn-delete', function () {
            const id = $(this).data('id');
            Swal.fire({
                title: '¿Estás seguro?',
                text: "Se eliminarán también todos los permisos asociados.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.get(BASE_URL_JS + 'grupo/delete/' + id, function (response) {
                        const res = JSON.parse(response);
                        if (res.success) {
                            Swal.fire('Eliminado', res.message, 'success');
                            grupoTable.ajax.reload(null, false);
                        } else {
                            Swal.fire('Error', res.message, 'error');
                        }
                    });
                }
            });
        });
    }

    if (isFormPage) {
        const grupoId = $('#grupo_id').val();
        if (grupoId) {
            loadPermissions(grupoId);
        }

        $('#form_grupo_page').on('submit', function (e) {
            e.preventDefault();
            const id = $('#grupo_id').val();
            const url = id ? BASE_URL_JS + 'grupo/update/' + id : BASE_URL_JS + 'grupo/store';

            $.ajax({
                url: url,
                type: 'POST',
                data: $(this).serialize(),
                success: function (response) {
                    const res = JSON.parse(response);
                    if (res.success) {
                        Swal.fire({
                            title: '¡Éxito!',
                            text: res.message,
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            if (!id) {
                                window.location.href = BASE_URL_JS + 'grupo/edit/' + res.id;
                            }
                        });
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                }
            });
        });

        // Check All Global
        $('#check-all-global').on('change', function () {
            const isChecked = $(this).is(':checked');
            $('.p-checkbox').prop('checked', isChecked);
            $('.row-checkbox').prop('checked', isChecked);
        });

        // Check All Row
        $(document).on('change', '.row-checkbox', function () {
            const isChecked = $(this).is(':checked');
            $(this).closest('tr').find('.p-checkbox').prop('checked', isChecked);
        });

        // Save Permissions
        $('#btn-save-permissions').on('click', function () {
            const currentGroupId = $('#grupo_id').val();
            if (!currentGroupId) return;

            const permissions = [];
            $('#permissions-body tr').each(function () {
                const row = $(this);
                permissions.push({
                    ventana_id: row.find('.p-ventana-id').val(),
                    aplicacion_id: row.find('.p-aplicacion-id').val(),
                    crear: row.find('.p-crear').is(':checked'),
                    modificar: row.find('.p-modificar').is(':checked'),
                    eliminar: row.find('.p-eliminar').is(':checked'),
                    listar: row.find('.p-listar').is(':checked')
                });
            });

            $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-2"></i> Guardando...');

            $.ajax({
                url: BASE_URL_JS + 'grupo/save_permissions',
                type: 'POST',
                data: {
                    grupo_id: currentGroupId,
                    permissions: permissions
                },
                success: function (response) {
                    const res = JSON.parse(response);
                    if (res.success) {
                        Swal.fire('¡Éxito!', res.message, 'success');
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                    $('#btn-save-permissions').prop('disabled', false).html('<i class="fa fa-check-circle mr-2"></i> Guardar Permisos');
                }
            });
        });
    }

    function loadPermissions(id) {
        $('#loading-permissions').removeClass('hidden');
        $('#permissions-grid').addClass('hidden');

        $.get(BASE_URL_JS + 'grupo/permissions/' + id, function (response) {
            const res = JSON.parse(response);
            if (res.success) {
                renderPermissions(res.data);
                $('#loading-permissions').addClass('hidden');
                $('#permissions-grid').removeClass('hidden');
            }
        });
    }

    function renderPermissions(data) {
        let html = '';
        data.forEach((p) => {
            html += `
                <tr class="border-b hover:bg-gray-50 transition">
                    <td class="px-5 py-4 text-center">
                        <input type="checkbox" class="row-checkbox w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 cursor-pointer">
                    </td>
                    <td class="px-5 py-4 font-medium text-gray-700">
                        ${p.nombre_ventana}
                        <input type="hidden" class="p-ventana-id" value="${p.ventana_id}">
                        <input type="hidden" class="p-aplicacion-id" value="${p.aplicacion_id}">
                    </td>
                    ${renderCheckbox(p.crear, 'crear')}
                    ${renderCheckbox(p.modificar, 'modificar')}
                    ${renderCheckbox(p.eliminar, 'eliminar')}
                    ${renderCheckbox(p.listar, 'listar')}
                </tr>
            `;
        });
        $('#permissions-body').html(html);
    }

    function renderCheckbox(checked, action) {
        return `
            <td class="px-2 py-4 text-center">
                <input type="checkbox" class="p-checkbox p-${action} w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500" ${checked ? 'checked' : ''}>
            </td>
        `;
    }
});
