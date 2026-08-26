<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Shipping;

class ShippingController extends Controller
{
    public function index(): void
    {
        Auth::requireLogin();
        $this->view('admin/shipping/index', [
            'pageTitle' => 'Envíos',
            'modalities' => Shipping::MODALITIES,
            'zones' => Shipping::ZONES,
            'tiers' => Shipping::TIERS,
            'rates' => Shipping::RATES,
            'zoneRegions' => Shipping::ZONE_REGIONS,
        ], 'admin');
    }
}
