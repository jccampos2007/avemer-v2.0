# avemer-ui — UI/UX Conventions

## Action Buttons in Lists

**All list actions MUST use the `btn-action` button classes** — not bare icons or text links.

### Standard Buttons

```html
<!-- Edit button -->
<a href="module/edit/{id}" class="btn-action btn-action-edit" title="Editar">
  <i class="fas fa-edit"></i>
</a>

<!-- Delete button -->
<a href="module/delete/{id}" class="btn-action btn-action-delete" title="Eliminar">
  <i class="fas fa-trash-alt"></i>
</a>
```

For delete operations triggered via AJAX, use a `<button>` element (not `<a>`):

```html
<button class="btn-action btn-action-delete" title="Eliminar" data-id="{id}">
  <i class="fas fa-trash-alt"></i>
</button>
```

### CSS Classes (defined in `public/css/base.css`)

| Class | Purpose | Color |
|-------|---------|-------|
| `.btn-action` | Base: 32×32 inline-flex, border, rounded, hover transition | transparent |
| `.btn-action-edit` | Edit action (blue) | `#2563eb` / `#1d4ed8` on hover |
| `.btn-action-delete` | Delete action (red) | `#dc2626` / `#b91c1c` on hover |

These use `!important` to ensure consistency across contexts.

### DataTable Action Column Pattern

Always in the JS file for the module:

```js
{
    data: null,
    orderable: false,
    searchable: false,
    width: "1%",
    className: "actions-column",
    render: function (data, type, row) {
        return '<div class="flex gap-2 justify-center">' +
            '<a href="module/edit/' + row[0] + '" class="btn-action btn-action-edit" title="Editar">' +
            '<i class="fas fa-edit"></i></a>' +
            '<a href="module/delete/' + row[0] + '" class="btn-action btn-action-delete" title="Eliminar">' +
            '<i class="fas fa-trash-alt"></i></a>' +
            '</div>';
    }
}
```

If row data uses named properties (not array indices), use `row.id` instead of `row[0]`.

### Additional Action Buttons

For custom actions (like "Generate Debt"), follow the same pattern:

```js
// Button when action is available
`<button type="button" class="btn-generar-deuda btn btn-default" title="Generar deuda"
    data-cuota-id="${row.id}" data-monto="${row.monto}">
  <i class="fas fa-file-invoice-dollar fs-5 text-orange-500"></i>
</button>`

// Button when action is already done (disabled)
`<button type="button" class="btn btn-default" title="Deuda ya generada" disabled>
  <i class="fas fa-check-circle fs-5 text-green-600"></i>
</button>`
```

## Delete Confirmation

Always use SweetAlert2 confirmation before delete:

```js
$(document).on('click', '.btn-action-delete', function (e) {
    e.preventDefault();
    const url = $(this).attr('href');

    Swal.fire({
        title: '¿Estás seguro?',
        text: "Esta acción no se puede deshacer.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({ url: url, type: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                success: function (response) {
                    let res = typeof response === 'string' ? JSON.parse(response) : response;
                    if (res.success) {
                        Swal.fire('¡Eliminado!', res.message, 'success');
                        // reload table
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                }
            });
        }
    });
});
```

## Permissions

Inject permissions from PHP `list.php` into a JS global:

```php
<script>
const MODULE_PERMISSIONS = {
    crear: <?php echo $canCreate ? 'true' : 'false'; ?>,
    modificar: <?php echo $canEdit ? 'true' : 'false'; ?>,
    eliminar: <?php echo $canDelete ? 'true' : 'false'; ?>
};
</script>
```

Conditionally render buttons in the DataTable render function:

```js
if (typeof MODULE_PERMISSIONS !== 'undefined') {
    if (MODULE_PERMISSIONS.modificar) {
        actions += `<a href="..." class="btn-action btn-action-edit">...</a>`;
    }
}
```

## Form Styling

Use Tailwind CSS utility classes. Custom classes defined in `public/css/input.css`:

```css
.label-form { @apply block text-gray-700 text-sm font-bold mb-2; }
.input-form { @apply shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight; }
```

Example:
```html
<div class="mb-4">
    <label for="nombre" class="label-form">Nombre:</label>
    <input type="text" id="nombre" name="nombre" class="input-form" required>
</div>
```

## Icon Library

Font Awesome 5 via CDN: `fas fa-edit`, `fas fa-trash-alt`, `fas fa-plus`, etc.

## Modals

Use Tailwind fixed overlay pattern (see Cuota `form.php` for example):

```html
<div id="modalId" class="fixed inset-0 z-50 hidden bg-black bg-opacity-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-3xl w-full max-h-[80vh] flex flex-col">
        <div class="flex items-center justify-between p-4 border-b">
            <h3 class="text-lg font-bold">Title</h3>
            <button type="button" class="close-modal text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
        </div>
        <div class="p-4 overflow-y-auto flex-1">
            <!-- content -->
        </div>
        <div class="flex items-center justify-end gap-2 p-4 border-t">
            <button class="close-modal bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">Cancelar</button>
            <button id="confirmBtn" class="bg-orange-500 hover:bg-orange-600 text-white font-bold py-2 px-4 rounded" disabled>Confirmar</button>
        </div>
    </div>
</div>
```
