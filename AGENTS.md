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
