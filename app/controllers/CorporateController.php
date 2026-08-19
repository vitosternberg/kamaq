<?php

namespace App\Controllers;

use App\Core\Controller;

class CorporateController extends Controller
{
    public function index(): void
    {
        $this->view('corporate/index', [
            'pageTitle' => 'Regalos Corporativos — KAMAQ',
            'metaDescription' => 'Regalos corporativos personalizados para tu empresa: cofres, cajas de vino y detalles de fin de año.',
            'breadcrumbs' => [
                ['label' => 'Inicio', 'url' => url('')],
                ['label' => 'Corporativo', 'url' => null],
            ],
        ]);
    }
}
