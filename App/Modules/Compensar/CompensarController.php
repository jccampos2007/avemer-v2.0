<?php
// app/Modules/Compensar/CompensarController.php
namespace App\Modules\Compensar;

use App\Core\Controller;
use App\Core\Auth;

class CompensarController extends Controller
{

    public function __construct()
    {
        Auth::requireLogin(); // Requiere que el usuario esté logueado para usar este módulo
    }

    /**
     * Muestra la vista de Compensar (Coming Soon)
     */
    public function index(): void
    {
        $this->view('Compensar/compensar'); // Ruta de vista relativa al módulo
    }
}
