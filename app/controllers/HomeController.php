<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Product;

class HomeController extends Controller
{
    public function index(): void
    {
        $this->view('home/index', [
            'pageTitle' => 'KAMAQ — Regalos corporativos y mementos personalizados',
            'metaDescription' => 'Tienda de regalos corporativos personalizados, mementos para bautizos, baby shower, matrimonios, cumpleaños, cajas de vino y joyeros.',
            'featured' => Product::featured(8),
            'bestsellers' => Product::bestsellers(8),
        ]);
    }
}
