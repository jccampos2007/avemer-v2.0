<?php
// php_mvc_app/app/Modules/Auth/Controllers/AuthController.php
namespace App\Modules\Auth; // Nuevo namespace

use App\Core\Controller;
use App\Core\Auth;
use App\Modules\Users\UserModel;

class AuthController extends Controller
{
    private const REMEMBER_COOKIE = 'remembered_user';
    private const COOKIE_EXPIRY = 30; // días
    private const OTP_EXPIRY = 15; // minutos

    private UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function showLogin(): void
    {
        if (Auth::check()) {
            $this->redirect('dashboard');
        }

        $greetingUser = null;

        if (isset($_GET['forget-user'])) {
            $this->clearRememberCookie();
        } elseif (isset($_COOKIE[self::REMEMBER_COOKIE])) {
            $data = json_decode($_COOKIE[self::REMEMBER_COOKIE], true);
            if ($data && !empty($data['username']) && !empty($data['name'])) {
                $greetingUser = $data;
            }
        }

        $this->view('Auth/login', [
            'isLogin' => true,
            'greeting_user' => $greetingUser,
        ]);
    }

    public function processLogin(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();
            $username = $this->sanitizeInput($_POST['username']);
            $password = $_POST['password'];

            if (empty($username) || empty($password)) {
                Auth::setFlashMessage('error', 'Por favor, ingresa tu usuario y contraseña.');
                $this->redirect('login');
            }

            if (Auth::login($username, $password)) {
                if (!empty($_POST['remember_user'])) {
                    $this->setRememberCookie($username, Auth::user('user_name'));
                } else {
                    $this->clearRememberCookie();
                }
                Auth::setFlashMessage('success', '¡Bienvenido, ' . Auth::user('user_name') . '!');
                $this->redirect('dashboard');
            } else {
                Auth::setFlashMessage('error', 'Usuario o contraseña incorrectos.');
                $this->redirect('login');
            }
        } else {
            $this->redirect('login');
        }
    }

    public function logout(): void
    {
        Auth::logout();
        Auth::setFlashMessage('success', 'Has cerrado sesión correctamente.');
        $this->redirect('login');
    }

    public function showForgotPassword(): void
    {
        if (Auth::check()) {
            $this->redirect('dashboard');
        }
        $this->view('Auth/forgot_password', ['isLogin' => true]);
    }

    public function processForgotPassword(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('forgot-password');
        }
        $this->validateCsrf();

        $input = $this->sanitizeInput($_POST['username'] ?? '');
        if (empty($input)) {
            Auth::setFlashMessage('error', 'Ingresa tu usuario o correo electrónico.');
            $this->redirect('forgot-password');
        }

        $user = $this->userModel->findByUsernameOrEmail($input);
        if (!$user) {
            Auth::setFlashMessage('error', 'No encontramos una cuenta con ese usuario o correo.');
            $this->redirect('forgot-password');
        }

        // Generar OTP de 6 dígitos
        $otp = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Guardar OTP en DB y expiry en sesión
        $this->userModel->saveResetToken((int)$user['usuario_id'], $otp);
        $_SESSION['reset_user_id'] = (int)$user['usuario_id'];
        $_SESSION['token_expiry'] = time() + (self::OTP_EXPIRY * 60);

        // Enviar email
        $toEmail = $user['correo'] ?? '';
        if (empty($toEmail)) {
            Auth::setFlashMessage('error', 'Tu cuenta no tiene un correo electrónico registrado. Contacta al administrador.');
            $this->redirect('forgot-password');
        }

        require_once __DIR__ . '/../Correo/enviar.php';
        $subject = 'Código de recuperación - Grupo Avemer';
        $body = "
            <div style='font-family: Arial, sans-serif; max-width: 480px; margin: 0 auto;'>
                <div style='text-align: center; padding: 20px 0;'>
                    <img src='" . BASE_URL . "image/logo-grupo-avemer.webp' alt='Grupo Avemer' style='height:50px;'>
                </div>
                <h2 style='color: #1e3a5f; text-align: center;'>Recuperación de Contraseña</h2>
                <p style='color: #333;'>Hola <strong>" . htmlspecialchars($user['usuario_nombre']) . "</strong>,</p>
                <p style='color: #333;'>Has solicitado restablecer tu contraseña. Usa el siguiente código:</p>
                <div style='text-align: center; margin: 24px 0;'>
                    <span style='display: inline-block; font-size: 28px; font-weight: bold; letter-spacing: 6px; color: #1e3a5f; background: #f0f4f8; padding: 12px 24px; border-radius: 8px;'>$otp</span>
                </div>
                <p style='color: #666; font-size: 13px;'>Este código expira en " . self::OTP_EXPIRY . " minutos.</p>
                <p style='color: #666; font-size: 13px;'>Si no solicitaste este cambio, ignora este correo.</p>
                <hr style='border: none; border-top: 1px solid #e0e0e0; margin: 20px 0;'>
                <p style='color: #999; font-size: 12px; text-align: center;'>Grupo Avemer &copy; " . date('Y') . "</p>
            </div>
        ";

        $sent = correo($subject, $body, $toEmail);

        if (!$sent) {
            Auth::setFlashMessage('error', 'No pudimos enviar el correo. Intenta de nuevo más tarde.');
            $this->redirect('forgot-password');
        }

        Auth::setFlashMessage('success', 'Hemos enviado un código de verificación a tu correo.');
        $this->redirect('verify-otp');
    }

    public function showVerifyOtp(): void
    {
        if (Auth::check()) {
            $this->redirect('dashboard');
        }
        if (empty($_SESSION['reset_user_id'])) {
            $this->redirect('forgot-password');
        }
        $this->view('Auth/verify_otp', ['isLogin' => true]);
    }

    public function processVerifyOtp(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('verify-otp');
        }
        $this->validateCsrf();

        $userId = $_SESSION['reset_user_id'] ?? null;
        $expiry = $_SESSION['token_expiry'] ?? 0;

        if (!$userId || time() > $expiry) {
            $this->clearResetSession();
            Auth::setFlashMessage('error', 'El código ha expirado. Solicita uno nuevo.');
            $this->redirect('forgot-password');
        }

        $otp = preg_replace('/[^0-9]/', '', $_POST['otp'] ?? '');
        if (strlen($otp) !== 6) {
            Auth::setFlashMessage('error', 'El código debe tener 6 dígitos.');
            $this->redirect('verify-otp');
        }

        $user = $this->userModel->findById($userId);
        if (!$user || $user['token'] !== $otp) {
            Auth::setFlashMessage('error', 'Código incorrecto. Intenta de nuevo.');
            $this->redirect('verify-otp');
        }

        $_SESSION['reset_verified'] = true;
        Auth::setFlashMessage('success', 'Código verificado correctamente. Ingresa tu nueva contraseña.');
        $this->redirect('reset-password');
    }

    public function showResetPassword(): void
    {
        if (Auth::check()) {
            $this->redirect('dashboard');
        }
        if (empty($_SESSION['reset_verified']) || empty($_SESSION['reset_user_id'])) {
            $this->redirect('forgot-password');
        }
        $this->view('Auth/reset_password', ['isLogin' => true]);
    }

    public function processResetPassword(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('reset-password');
        }
        $this->validateCsrf();

        if (empty($_SESSION['reset_verified']) || empty($_SESSION['reset_user_id'])) {
            $this->redirect('forgot-password');
        }

        $password = $_POST['password'] ?? '';
        $confirm = $_POST['password_confirm'] ?? '';

        if (strlen($password) < 6) {
            Auth::setFlashMessage('error', 'La contraseña debe tener al menos 6 caracteres.');
            $this->redirect('reset-password');
        }

        if ($password !== $confirm) {
            Auth::setFlashMessage('error', 'Las contraseñas no coinciden.');
            $this->redirect('reset-password');
        }

        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $this->userModel->updatePassword((int)$_SESSION['reset_user_id'], $hashed);
        $this->userModel->clearResetToken((int)$_SESSION['reset_user_id']);

        $this->clearResetSession();

        Auth::setFlashMessage('success', 'Contraseña actualizada correctamente. Ahora puedes iniciar sesión.');
        $this->redirect('login');
    }

    private function setRememberCookie(string $username, string $name): void
    {
        $value = json_encode(['username' => $username, 'name' => $name]);
        setcookie(self::REMEMBER_COOKIE, $value, time() + (86400 * self::COOKIE_EXPIRY), '/', '', false, true);
    }

    private function clearRememberCookie(): void
    {
        if (isset($_COOKIE[self::REMEMBER_COOKIE])) {
            setcookie(self::REMEMBER_COOKIE, '', time() - 3600, '/', '', false, true);
            unset($_COOKIE[self::REMEMBER_COOKIE]);
        }
    }

    private function clearResetSession(): void
    {
        unset($_SESSION['reset_user_id']);
        unset($_SESSION['token_expiry']);
        unset($_SESSION['reset_verified']);
    }
}
