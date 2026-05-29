# Plan de Refactorización: Migración de Costos a las Aperturas de Ofertas Académicas

Este documento contiene la planificación detallada para refactorizar la base de datos y la aplicación, trasladando la definición de **Costo** (`costo`) e **Inicial** (`inicial`) desde las tablas base (`diplomado` y `evento`) a las respectivas tablas de apertura/oferta en el tiempo (`diplomado_abierto`, `curso_abierto`, `maestria_abierto` y `evento_abierto`).

> [!NOTE]  
> **Justificación del cambio:**  
> Los precios de las ofertas académicas pueden fluctuar a lo largo del tiempo debido a la inflación, los costos operativos de cada sede y los honorarios docentes. Definir el costo a nivel de *apertura* permite una gestión financiera precisa, realista e histórica, sin alterar retrospectivamente inscripciones del pasado.

---

## 1. Plan de Modificaciones de Base de Datos (DDL & DML)

Para realizar este cambio de forma limpia y preservar todo el historial de datos existente en producción, ejecutaremos la siguiente secuencia de scripts SQL:

### Paso A: Agregar columnas en las tablas de aperturas (Abierto)
Añadiremos los campos `costo` e `inicial` a las cuatro tablas de apertura con un valor por defecto de `0.00` para evitar inconsistencias en registros vacíos.

```sql
-- 1. Agregar columnas a diplomado_abierto
ALTER TABLE diplomado_abierto 
ADD COLUMN costo float NOT NULL DEFAULT 0.00,
ADD COLUMN inicial float NOT NULL DEFAULT 0.00;

-- 2. Agregar columnas a curso_abierto
ALTER TABLE curso_abierto 
ADD COLUMN costo float NOT NULL DEFAULT 0.00,
ADD COLUMN inicial float NOT NULL DEFAULT 0.00;

-- 3. Agregar columnas a maestria_abierto
ALTER TABLE maestria_abierto 
ADD COLUMN costo float NOT NULL DEFAULT 0.00,
ADD COLUMN inicial float NOT NULL DEFAULT 0.00;

-- 4. Agregar columnas a evento_abierto
ALTER TABLE evento_abierto 
ADD COLUMN costo float NOT NULL DEFAULT 0.00,
ADD COLUMN inicial float NOT NULL DEFAULT 0.00;
```

### Paso B: Migración de Datos Históricos
Migraremos los valores de costo e inicial actuales desde las tablas base (`diplomado` y `evento`) a las aperturas ya creadas que correspondan.

```sql
-- 1. Copiar costos históricos de diplomados a sus aperturas
UPDATE diplomado_abierto da 
JOIN diplomado d ON da.diplomado_id = d.id 
SET da.costo = d.costo, da.inicial = d.inicial;

-- 2. Copiar costos históricos de eventos a sus aperturas
UPDATE evento_abierto ea 
JOIN evento e ON ea.evento_id = e.id 
SET ea.costo = e.costo, ea.inicial = e.inicial;
```

### Paso C: Eliminación de columnas obsoletas (Posterior a la validación del código)
Una vez que el código esté refactorizado y validado, eliminaremos las columnas de las tablas base para mantener la integridad del modelo relacional.

```sql
-- 1. Remover columnas obsoletas de diplomado
ALTER TABLE diplomado DROP COLUMN costo, DROP COLUMN inicial;

-- 2. Remover columnas obsoletas de evento
ALTER TABLE evento DROP COLUMN costo, DROP COLUMN inicial;
```

---

## 2. Plan de Refactorización del Código del Sistema

A continuación se detallan los módulos y archivos específicos que se modificarán:

### A. Módulo `Diplomado` (Limpieza de la Entidad Base)
* **`App/Modules/Diplomado/DiplomadoModel.php`**
  - Remover `costo` e `inicial` de `getPaginatedDiplomados`, `create` y `update`.
* **`App/Modules/Diplomado/DiplomadoController.php`**
  - Eliminar la recepción, saneamiento y validación de `costo` e `inicial` en `processForm`.
* **`App/Modules/Diplomado/Views/form.php`**
  - Remover los elementos de entrada `<input>` para Costo e Inicial.
* **`App/Modules/Diplomado/diplomado.js`**
  - Limpiar las validaciones y variables correspondientes a `costo` e `inicial`.

### B. Módulo `DiplomadoAbierto` (Integración de Costos en la Apertura)
* **`App/Modules/DiplomadoAbierto/DiplomadoAbiertoModel.php`**
  - Agregar `costo` e `inicial` a las consultas de selección (`getPaginatedDiplomadoAbierto`, `getById`), e incluirlos en `create` y `update`.
* **`App/Modules/DiplomadoAbierto/DiplomadoAbiertoController.php`**
  - Agregar la recepción, saneamiento (`float`), validación e inserción/actualización de `costo` e `inicial` en `processForm`.
* **`App/Modules/DiplomadoAbierto/Views/form.php`**
  - Agregar dos inputs numéricos (`costo` e `inicial`) al diseño grid del formulario, conservando las clases Tailwind de estilo premium.

### C. Módulo `Evento` (Limpieza de la Entidad Base)
* **`App/Modules/Evento/EventoModel.php`**
  - Remover `costo` e `inicial` de las consultas, inserciones y actualizaciones.
* **`App/Modules/Evento/EventoController.php`**
  - Remover validación y saneamiento de `costo` e `inicial` en `processForm`.
* **`App/Modules/Evento/Views/form.php`**
  - Eliminar los inputs del formulario de Evento Base.
* **`App/Modules/Evento/evento.js`**
  - Eliminar variables y validaciones de `costo` e `inicial`.

### D. Módulo `EventoAbierto` (Integración de Costos en la Apertura)
* **`App/Modules/EventoAbierto/EventoAbiertoModel.php`**
  - Integrar `costo` e `inicial` en las consultas SQL de inserción, actualización y consulta de la apertura del evento.
* **`App/Modules/EventoAbierto/EventoAbiertoController.php`**
  - Manejar la validación, saneamiento y guardado de `costo` e `inicial` en `processForm`.
* **`App/Modules/EventoAbierto/Views/form.php`**
  - Agregar inputs visuales para `costo` e `inicial`.

### E. Módulo `CursoAbierto` (Nuevas Características de Costos)
* **`App/Modules/CursoAbierto/CursoAbiertoModel.php`**
  - Agregar campos en los métodos SQL de inserción, actualización y listado paginado.
* **`App/Modules/CursoAbierto/CursoAbiertoController.php`**
  - Agregar saneamiento de `costo` e `inicial` en `processForm`.
* **`App/Modules/CursoAbierto/Views/form.php`**
  - Agregar inputs de costo e inicial en el formulario de apertura de curso.

### F. Módulo `MaestriaAbierto` (Nuevas Características de Costos)
* **`App/Modules/MaestriaAbierto/MaestriaAbiertoModel.php`**
  - Agregar campos en los métodos SQL de inserción, actualización y listado paginado.
* **`App/Modules/MaestriaAbierto/MaestriaAbiertoController.php`**
  - Agregar saneamiento de `costo` e `inicial` en `processForm`.
* **`App/Modules/MaestriaAbierto/Views/form.php`**
  - Agregar inputs de costo e inicial en el formulario de apertura de maestría.

---

## 3. Plan de Pruebas y Validación

1. **Migración Exitosa:** Ejecutar los comandos `ALTER` y verificar que las aperturas previas conserven sus costos correspondientes mediante cruce de SQL.
2. **Creación/Edición Base:** Probar que al guardar o modificar un Diplomado o Evento base no se requiera ingresar ni se intente guardar el costo/inicial.
3. **Creación/Edición Aperturas:** Registrar un nuevo Diplomado, Evento, Curso o Maestría abiertos asignándole un costo e inicial personalizado, y verificar que se refleje correctamente en la base de datos.
4. **Verificación de Cobros (Cuotas):** Asegurarse de que el módulo de Cuotas (donde se generan las cuotas de pago de los alumnos) siga funcionando perfectamente usando el `monto` configurado en dicho módulo.
