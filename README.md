# Sistema de Gestión Educativa (Diplomados, Talleres, Eventos y Maestrías)

Este proyecto es un sistema de gestión educativa desarrollado en PHP que permite el control y administración de diplomados, talleres, eventos y maestrías. La aplicación utiliza jQuery para la interactividad del lado del cliente y Tailwind CSS para un diseño moderno y responsivo. Una de las características clave es su **enrutamiento centralizado a través de `index.php`**, lo que significa que todas las solicitudes se gestionan desde un único punto de entrada.

## ✨ Características Principales

* **Gestión Integral de Programas:** Administración completa de inscripciones, detalles, y seguimiento para Diplomados, Talleres, Eventos y Maestrías.

* **Interfaz de Usuario Moderna:** Construida con **Tailwind CSS** para un diseño limpio, adaptable y altamente personalizable.

* **Interactividad Dinámica:** Integración de **jQuery** para añadir funcionalidades dinámicas y mejorar la experiencia del usuario.

* **Arquitectura MVC Ligera:** Aunque todo se enruta a través de `index.php`, la estructura interna se orienta a un patrón MVC (Modelo-Vista-Controlador) para una mejor organización del código.

* **Configuración Simplificada:** Uso de variables de entorno (`.env`) para una configuración de base de datos sencilla y segura en entornos locales.

## 🚀 Tecnologías Utilizadas

* **Backend:**

    * **PHP:** El lenguaje de programación principal del lado del servidor.

* **Frontend:**

    * **jQuery:** Librería de JavaScript para manipulación del DOM y manejo de eventos.

    * **Tailwind CSS:** Framework CSS de utilidades para un diseño rápido y flexible.

    * **Alpine.js (Recomendado):** Para la interactividad de componentes de UI (ej. menús desplegables) de forma declarativa y ligera.

* **Base de Datos:**

    * **MySQL / MariaDB:** Sistema de gestión de bases de datos relacional.

## 📋 Requisitos del Sistema

Para ejecutar esta aplicación en tu entorno local, necesitarás:

* **Servidor Web:** Apache, Nginx, XAMPP, Laragon, WAMP, etc.

* **PHP:** Versión 7.4 o superior (recomendado PHP 8.x).

* **Base de Datos:** MySQL o MariaDB.

* **Composer:** (Opcional, pero recomendado si el proyecto usa librerías de PHP a través de Composer).

* **Node.js y npm:** Indispensables para compilar Tailwind CSS.

## ⚙️ Configuración Local

Sigue estos pasos para configurar y ejecutar el proyecto en tu máquina de desarrollo:

### 1. Clonar el Repositorio

```bash
git clone https://github.com/jccampos2007/avemer-v2.0.git
cd avemer-v2.0
```

### 2. Configurar el Servidor Web

Asegúrate de que el **"Document Root" (o "Web Root") de tu servidor web apunte a la carpeta `public`** dentro del proyecto. Esto es fundamental para el enrutamiento centralizado a través de `index.php` y para una buena práctica de seguridad.

* **Ejemplo para Apache:** Asegúrate de que tu configuración de host virtual o el archivo `.htaccess` en la carpeta `public` (si existe y lo necesitas para reescritura de URLs amigables) esté correctamente configurado.

### 3. Crear la Base de Datos

Crea una base de datos vacía en tu servidor MySQL/MariaDB. Por ejemplo, `gestion_educativa_db`.

### 4. Configurar Variables de Entorno (`.env`)

Crea un archivo llamado `.env` en la **raíz de tu proyecto** (al mismo nivel que `package.json` y `tailwind.config.js`). Este archivo contendrá las credenciales de tu base de datos:

```bash
DB_HOST=localhost       # O la IP de tu servidor de BD
DB_NAME=gestion_educativa_db # El nombre de la BD que creaste
DB_USER=root            # Tu usuario de BD
DB_PASS=                # Tu contraseña de BD (vacío si no tienes)
```

**Importante:** Asegúrate de que tu aplicación PHP esté configurada para leer estas variables de entorno (por ejemplo, usando una librería como `vlucas/phpdotenv` o un mecanismo personalizado). El archivo `.env` **nunca debe ser subido** a tu repositorio de control de versiones.

### 5. Instalar Dependencias de Node.js

Desde la **raíz de tu proyecto** en tu terminal, ejecuta el siguiente comando para instalar las dependencias de desarrollo (Tailwind CSS, PostCSS, Autoprefixer):

```bash
npm install
```

### 6. Compilar Tailwind CSS

Una vez instaladas las dependencias de Node.js, debes compilar el CSS de Tailwind.

* **Durante el desarrollo (con `watch`):**
    Abre una terminal en la raíz de tu proyecto y ejecuta este comando. Mantenla abierta mientras desarrollas. Los cambios que hagas en tus archivos HTML/PHP que utilicen clases de Tailwind se reflejarán automáticamente en tu archivo CSS de salida.

    ```
    npm run dev
    # O, si no tienes scripts configurados en package.json:
    # npx tailwindcss -i ./resources/css/input.css -o ./public/css/output.css --watch
    
    ```

    **Verifica:** Tu archivo `resources/css/input.css` (o la ruta que uses) debe contener las directivas principales de Tailwind:

    ```
    @tailwind base;
    @tailwind components;
    @tailwind utilities;
    
    ```

    También asegúrate de que tu `tailwind.config.js` esté configurado para escanear correctamente tus archivos PHP y HTML para detectar las clases de Tailwind que utilizas.

* **Para producción (CSS final y minificado):**
    Ejecuta este comando **una sola vez** antes de desplegar tu aplicación a un servidor en vivo. Generará un archivo CSS optimizado y más pequeño.

    ```
    npm run build
    # O:
    # npx tailwindcss -i ./resources/css/input.css -o ./public/css/output.css --minify
    
    ```

### 7. Importar Esquema de Base de Datos

Si el proyecto incluye un archivo SQL (`.sql`) con el esquema de la base de datos y/o datos iniciales, impórtalo en tu base de datos recién creada:

```bash
mysql -u DB_USER -p DB_NAME < path/to/your/database.sql
# Reemplaza DB_USER, DB_NAME y path/to/your/database.sql con tus valores.
```

*(Si usas un sistema de migraciones PHP, consulta la documentación específica de ese sistema.)*

### 8. Acceder a la Aplicación

Una vez que todos los pasos anteriores se hayan completado (el servidor web configurado, las dependencias instaladas y Tailwind CSS compilado en modo `watch`), abre tu navegador y navega a la URL de tu proyecto. Por ejemplo:

```bash
http://localhost/tu_proyecto_php/public/
```

*(Ajusta la URL según la configuración de tu servidor local.)*

## 📂 Estructura del Proyecto (Ejemplo Básico)

Aunque tu `README` no lo detalla, una estructura típica de un proyecto PHP MVC que cumple con estos requisitos podría ser:

```bash
.
├── app/                    # Lógica principal de la aplicación
│   ├── Controllers/        # Clases de controladores
│   ├── Models/             # Clases de modelos (interacción con la BD)
│   ├── Views/              # Archivos de vista (HTML con PHP)
│   │   ├── components/     # Pequeños parciales reutilizables
│   │   ├── auth/           # Vistas de autenticación (ej. login.php)
│   │   └── layout.php      # Plantilla principal
│   └── Config/             # Configuraciones internas de la app
├── public/                 # Directorio público accesible desde el navegador
│   ├── css/
│   │   └── output.css      # CSS compilado de Tailwind
│   ├── js/
│   │   └── main.js         # Scripts JS personalizados
│   ├── index.php           # Punto de entrada y enrutador principal
│   └── .htaccess           # Reglas de reescritura para Apache
├── resources/              # Archivos fuente para desarrollo (no públicos)
│   ├── css/
│   │   └── input.css       # CSS de entrada para Tailwind
│   ├── js/
│   └── assets/             # Imágenes, fuentes, etc.
├── vendor/                 # Dependencias PHP (Composer)
├── .env                    # Variables de entorno (no se sube al repo)
├── .env.example            # Plantilla base para .env
├── package.json            # Dependencias de Node (Tailwind)
├── tailwind.config.js      # Configuración de Tailwind
├── postcss.config.js       # Configuración de PostCSS
└── README.md               # Documentación principal del proyecto
```


## 🤝 Contribución

¡Las contribuciones son bienvenidas! Si deseas mejorar este proyecto, por favor:

1.  Haz un "fork" del repositorio.

2.  Crea una nueva rama (`git checkout -b feature/nombre-de-la-funcionalidad`).

3.  Realiza tus cambios y commitea (`git commit -am 'Agrega nueva funcionalidad X'`).

4.  Sube tus cambios a tu "fork" (`git push origin feature/nombre-de-la-funcionalidad`).

5.  Crea un "Pull Request" a la rama `main` del repositorio original.

## 📄 Licencia

[Aquí puedes especificar la licencia de tu proyecto, por ejemplo: MIT License, Apache 2.0, etc.]