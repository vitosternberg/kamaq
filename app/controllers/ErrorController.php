<?php

namespace App\Controllers;

use App\Core\Controller;

class ErrorController extends Controller
{
    public function notFound(): void
    {
        $this->view('errors/404', [
            'pageTitle' => 'Página no encontrada — KAMAQ',
            'breadcrumbs' => [
                ['label' => 'Inicio', 'url' => url('')],
                ['label' => 'Página no encontrada', 'url' => null],
            ],
        ]);
    }
}
