# Convenciones para el agente

## Funciones globales en main.js

Antes de implementar autocomplete, dropdowns o cualquier funcionalidad que involucre selección/búsqueda de datos, revisar primero las funciones disponibles en `/var/www/html/php_mvc_app/public/js/main.js`.

### `setupAutocomplete(inputId, hiddenId, endpoint, minLength, extraData)`
Usar esta función para cualquier campo que requiera autocomplete con búsqueda server-side vía `api/search/{endpoint}`. Está diseñada para trabajar con el `ApiController::getAutocompleteData()` y soporta:
- Display columns personalizados vía `extraData.displayColumn`
- Filtro por estatus vía `extraData.status`
- Manejo automático de `select` y `change`

### Evento `autocompleteselect`
Para ejecutar lógica adicional cuando se selecciona un item del autocomplete (ej. cargar datos dependientes), usar:
```js
$('#inputId').on('autocompleteselect', function (event, ui) {
    // ui.item.id, ui.item.value, ui.item.label
});
```

### `setupAlumnoCopyHandler(tableSelector)`
Usar esta función para habilitar el botón de copiar datos del alumno en tablas DataTable. Recibe el selector de la tabla. Llamar después de inicializar el DataTable:
```js
setupAlumnoCopyHandler('#miTabla');
```

## Botones de acción en listas (DataTables)

Todos los botones de acciones en columnas de tablas deben usar las clases `btn-action` + `btn-action-{tipo}` definidas en `/var/www/html/php_mvc_app/public/css/base.css`, **nunca estilos inline**. Las clases disponibles son:

| Clase | Uso | Color |
|-------|-----|-------|
| `btn-action-edit` | Editar/Modificar | Azul |
| `btn-action-delete` | Eliminar | Rojo |
| `btn-action-generar` | Generar deuda | Naranja |
| `btn-action-email` | Enviar correo | Índigo |
| `btn-action-view` | Ver/Consultar | Verde |
| `btn-action-copy` | Copiar datos | Gris |

Ejemplo correcto:
```js
buttons += '<a href="..." class="btn-action btn-action-edit" title="Editar"><i class="fas fa-edit"></i></a>';
buttons += '<button class="btn-action btn-action-delete" title="Eliminar"><i class="fas fa-trash-alt"></i></button>';
```
