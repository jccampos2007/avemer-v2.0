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

        $viewFile = MODULES_PATH . $moduleName . '/Views/' . $actualViewFile . '.php';
        if (!file_exists($viewFile)) {
            $viewFile = MODULES_PATH . $moduleName . '/' . $actualViewFile . '.php';
        }

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

    protected function validateCsrf(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            return;
        }
        $token = $_POST['csrf_token'] ?? '';
        if (Auth::validateCsrfToken($token)) {
            return;
        }
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Token CSRF inválido.']);
            exit;
        }
        Auth::setFlashMessage('error', 'Token CSRF inválido. Intente nuevamente.');
        $this->redirect($_SERVER['HTTP_REFERER'] ?? 'dashboard');
    }

    protected function imageToWebP(string $sourcePath, int $quality = 80): ?string
    {
        $info = @getimagesize($sourcePath);
        if ($info === false) return null;

        $image = match ($info[2]) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($sourcePath),
            IMAGETYPE_PNG  => @imagecreatefrompng($sourcePath),
            IMAGETYPE_GIF  => @imagecreatefromgif($sourcePath),
            IMAGETYPE_WEBP => @imagecreatefromwebp($sourcePath),
            default => null,
        };
        if ($image === null) return null;

        ob_start();
        imagewebp($image, null, $quality);
        $webpData = ob_get_clean();
        imagedestroy($image);

        return $webpData !== false ? $webpData : null;
    }

    protected function renderLanding(string $viewPath): void
    {
        // Construimos la ruta hacia App/Views/
        $viewFile = '../App/Modules/' . $viewPath . '.php';

        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            die("Error: La vista de la landing no existe en: " . $viewFile);
        }
    }
}
