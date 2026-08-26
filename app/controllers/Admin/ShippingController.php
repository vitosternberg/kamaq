<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Setting;
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
            'rates' => Shipping::rates(),
            'zoneRegions' => Shipping::ZONE_REGIONS,
        ], 'admin');
    }

    public function save(): void
    {
        Auth::requireLogin();
        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            flash('error', 'Sesión inválida.');
            redirect('/admin/envios');
        }

        $input = $_POST['rates'] ?? [];
        $matrix = Shipping::RATES;
        foreach (Shipping::MODALITIES as $m) {
            foreach (Shipping::ZONES as $z) {
                foreach (Shipping::TIERS as $t) {
                    $v = $input[$m][$z][$t] ?? null;
                    if (is_numeric($v)) {
                        $matrix[$m][$z][$t] = (int) $v;
                    }
                }
            }
        }

        Setting::set('shipping_rates', json_encode($matrix));
        flash('success', 'Tarifas de envío actualizadas.');
        redirect('/admin/envios');
    }
}
