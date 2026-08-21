<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Shipping;

class PageController extends Controller
{
    public function shipping(): void
    {
        $this->view('pages/shipping', [
            'pageTitle' => 'Política de envío — KAMAQ',
            'shipping' => Shipping::settings(),
            'breadcrumbs' => [
                ['label' => 'Inicio', 'url' => url('')],
                ['label' => 'Política de envío', 'url' => null],
            ],
        ]);
    }

    public function privacy(): void
    {
        $this->view('pages/privacy', [
            'pageTitle' => 'Protección de datos — KAMAQ',
            'breadcrumbs' => [
                ['label' => 'Inicio', 'url' => url('')],
                ['label' => 'Protección de datos', 'url' => null],
            ],
        ]);
    }

    public function howToBuy(): void
    {
        $this->view('pages/how-to-buy', [
            'pageTitle' => 'Cómo comprar — KAMAQ',
            'breadcrumbs' => [
                ['label' => 'Inicio', 'url' => url('')],
                ['label' => 'Cómo comprar', 'url' => null],
            ],
        ]);
    }
}
