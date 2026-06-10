<?php
namespace App\Modules\Profile;

use App\Core\Controller;
use App\Core\Auth;
use App\Modules\Users\UserModel;

class ProfileController extends Controller
{
    private UserModel $userModel;

    public function __construct()
    {
        Auth::requireLogin();
        $this->userModel = new UserModel();
    }

    public function index(): void
    {
        $userId = Auth::user('user_id');
        $user = $this->userModel->findById($userId);

        $this->view('Profile/index', [
            'user' => $user,
        ]);
    }

    public function update(): void
    {
        $this->validateCsrf();
        $userId = Auth::user('user_id');

        $data = [
            'usuario_nombre' => $this->sanitizeInput($_POST['usuario_nombre'] ?? ''),
            'usuario_apellido' => $this->sanitizeInput($_POST['usuario_apellido'] ?? ''),
            'correo' => $this->sanitizeInput($_POST['correo'] ?? ''),
        ];

        if (empty($data['usuario_nombre']) || empty($data['usuario_apellido'])) {
            Auth::setFlashMessage('error', 'Nombre y apellido son obligatorios.');
            $this->redirect('profile');
        }

        $oldImage = null;
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            $uploadedFile = $_FILES['profile_image'];

            $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $detectedMimeType = mime_content_type($uploadedFile['tmp_name']);
            if (!in_array($detectedMimeType, $allowedMimeTypes)) {
                Auth::setFlashMessage('error', 'Solo se permiten imágenes (JPEG, PNG, GIF, WebP).');
                $this->redirect('profile');
            }

            if ($uploadedFile['size'] > 2 * 1024 * 1024) {
                Auth::setFlashMessage('error', 'La imagen no debe superar los 2MB.');
                $this->redirect('profile');
            }

            $avatarDir = dirname(__DIR__, 3) . '/public/uploads/avatars/';
            if (!is_dir($avatarDir)) {
                mkdir($avatarDir, 0755, true);
            }

            $currentUser = $this->userModel->findById($userId);
            $oldImage = $currentUser['profile_image'] ?? null;

            $webpPath = $this->convertToWebP($uploadedFile['tmp_name'], $avatarDir, $userId);
            if ($webpPath === null) {
                Auth::setFlashMessage('error', 'Error al procesar la imagen.');
                $this->redirect('profile');
            }

            $data['profile_image'] = $webpPath;

            if ($oldImage && $oldImage !== $webpPath) {
                $oldFilePath = $avatarDir . $oldImage;
                if (file_exists($oldFilePath)) {
                    unlink($oldFilePath);
                }
            }
        }

        $this->userModel->updateProfile($userId, $data);

        $_SESSION['user_name'] = $data['usuario_nombre'];
        if (isset($data['profile_image'])) {
            $_SESSION['profile_image'] = $data['profile_image'];
        }

        Auth::setFlashMessage('success', 'Perfil actualizado correctamente.');
        $this->redirect('profile');
    }

    public function changePassword(): void
    {
        $this->validateCsrf();
        $userId = Auth::user('user_id');

        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            Auth::setFlashMessage('error', 'Todos los campos de contraseña son obligatorios.');
            $this->redirect('profile');
        }

        if ($newPassword !== $confirmPassword) {
            Auth::setFlashMessage('error', 'La nueva contraseña y la confirmación no coinciden.');
            $this->redirect('profile');
        }

        if (strlen($newPassword) < 6) {
            Auth::setFlashMessage('error', 'La nueva contraseña debe tener al menos 6 caracteres.');
            $this->redirect('profile');
        }

        $user = $this->userModel->findById($userId);
        if (!password_verify($currentPassword, $user['usuario_pws'])) {
            Auth::setFlashMessage('error', 'La contraseña actual no es correcta.');
            $this->redirect('profile');
        }

        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $this->userModel->updatePassword($userId, $hashedPassword);

        session_regenerate_id(true);

        Auth::setFlashMessage('success', 'Contraseña actualizada correctamente.');
        $this->redirect('profile');
    }

    private function convertToWebP(string $sourcePath, string $destDir, int $userId): ?string
    {
        $imageInfo = getimagesize($sourcePath);
        if ($imageInfo === false) {
            return null;
        }

        $mime = $imageInfo['mime'];
        $image = match ($mime) {
            'image/jpeg' => imagecreatefromjpeg($sourcePath),
            'image/png' => imagecreatefrompng($sourcePath),
            'image/gif' => imagecreatefromgif($sourcePath),
            'image/webp' => imagecreatefromwebp($sourcePath),
            default => null,
        };

        if ($image === null) {
            return null;
        }

        $filename = 'user_' . $userId . '_' . time() . '.webp';
        $destPath = rtrim($destDir, '/') . '/' . $filename;

        imagepalettetotruecolor($image);
        imagealphablending($image, true);
        imagesavealpha($image, true);

        $result = imagewebp($image, $destPath, 80);
        imagedestroy($image);

        return $result ? $filename : null;
    }
}
