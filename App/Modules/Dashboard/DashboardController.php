<?php
// php_mvc_app/app/Modules/Dashboard/Controllers/DashboardController.php
namespace App\Modules\Dashboard; // Nuevo namespace

use App\Core\Controller;
use App\Core\Auth;

class DashboardController extends Controller
{
    public function index(): void
    {
        Auth::requireLogin(); // Asegura que el usuario esté logueado
        $this->view('Dashboard/index'); // Ruta de vista relativa al módulo
    }
}
