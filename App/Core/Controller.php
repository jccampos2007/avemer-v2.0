<?php
// php_mvc_app/app/Core/Controller.php
namespace App\Core;

class Controller
{
    protected function view(string $viewName, array $data = []): void
    {
        extract($data);
        $message = Auth::getFlashMessage();

        require_once VIEWS_LAYOUT_PATH . 'header.php';
        if (Auth::check()) {
            require_once VIEWS_LAYOUT_PATH . 'sidebar.php';
        }
        require_once VIEWS_LAYOUT_PATH . 'message.php';
        $parts = explode('/', $viewName, 2);
        if (count($parts) < 2) {
            die("Error: Formato de vista incorrecto. Esperado 'Modulo/vista'. Recibido: " . $viewName);
        }
        $moduleName = $parts[0]; // Ej: 'Dashboard'
        $actualViewFile = $parts[1]; // Ej: 'index'

        $viewFile = MODULES_PATH . $moduleName . '/' . $actualViewFile . '.php';

        if (!file_exists($viewFile))
            $viewFile = MODULES_PATH . $moduleName . '/Views/' . $actualViewFile . '.php';

        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            die("Error: Vista no encontrada en " . $viewFile . ". Verifique la ruta y el nombre del archivo.");
        }

        require_once VIEWS_LAYOUT_PATH . 'footer.php';
    }

    protected function model(string $modelName)
    {
        $modelClass = "App\\" . $modelName;
        if (class_exists($modelClass)) {
            return new $modelClass();
        }
        throw new \Exception("Modelo no encontrado: " . $modelName);
    }

    protected function redirect(string $path): void
    {
        header('Location: ' . BASE_URL . $path);
        exit();
    }

    protected function sanitizeInput(string $data): string
    {
        return htmlspecialchars(strip_tags(trim($data)));
    }
}
