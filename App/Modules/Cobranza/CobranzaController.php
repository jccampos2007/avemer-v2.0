<?php
namespace App\Modules\Cobranza;

use App\Core\Controller;
use App\Core\Auth;

class CobranzaController extends Controller
{
    private CobranzaModel $cobranzaModel;

    public function __construct()
    {
        Auth::requireLogin();
        $this->cobranzaModel = new CobranzaModel();
    }

    public function index(): void
    {
        $this->view('Cobranza/list');
    }

    public function getData(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        $data = $this->cobranzaModel->getPaginated($_POST);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
