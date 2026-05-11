<?php
// app/Modules/Pagos/PagoController.php
namespace App\Modules\Pagos;

use App\Core\Controller;
use App\Core\Auth;

class PagoController extends Controller
{

    public function __construct()
    {
        Auth::requireLogin(); // Requiere que el usuario esté logueado para usar este módulo
    }

    /**
     * Muestra la lista de registros de Maestría Abierta.
     */
    public function index(): void
    {
        $this->view('Pagos/pagos'); // Ruta de vista relativa al módulo
    }
}
