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
            'shipping' => Shipping::settings(),
        ], 'admin');
    }

    public function savePolicy(): void
    {
        Auth::requireLogin();
        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            flash('error', 'Sesión inválida.');
            redirect('/admin/envios');
        }

        Setting::set('shipping_rm_price', (float) ($_POST['rm_price'] ?? 0));
        Setting::set('shipping_free_threshold', (float) ($_POST['free_threshold'] ?? 0));
        Setting::set('shipping_express_price', (float) ($_POST['express_price'] ?? 0));
        Setting::set('shipping_outside_price', (float) ($_POST['outside_price'] ?? 0));

        flash('success', 'Política de envío actualizada.');
        redirect('/admin/envios');
    }
}
