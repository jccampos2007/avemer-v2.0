/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        "./**/*.php",       // Escanea todos los archivos PHP en cualquier nivel
        "./App/Modules/**/*.{php,js}", // Si usas una estructura de carpetas específica
        "./index.php",
    ],
    theme: {
        extend: {},
    },
    plugins: [],
}