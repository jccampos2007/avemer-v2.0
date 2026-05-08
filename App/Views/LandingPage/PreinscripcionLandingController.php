<?php
// app/Views/LandingPage/PreinscripcionLandingController.php
namespace App\Views\LandingPage;

use App\Core\Controller; // Asume que Controller provee los helpers

class PreinscripcionLandingController extends Controller
{

    public function index(): void
    {
        $this->renderLanding('LandingPage/preinscripcion_landing');
    }

}
