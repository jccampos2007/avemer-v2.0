<?php
// app/Modules/Cronograma/CronogramaController.php
namespace App\Modules\Cronograma;

use App\Core\Controller;
use App\Core\Auth;

class CronogramaController extends Controller
{

    public function __construct()
    {
        Auth::requireLogin(); // Requiere que el usuario esté logueado para usar este módulo
    }

    /**
     * Muestra la vista de Cronograma (Coming Soon)
     */
    public function index(): void
    {
        $this->view('Cronograma/cronograma'); // Ruta de vista relativa al módulo
    }
}
