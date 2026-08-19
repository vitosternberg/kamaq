<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\ShippingMethod;

class ShippingController extends Controller
{
    public function index(): void
    {
        Auth::requireLogin();
        $this->view('admin/shipping/index', [
            'pageTitle' => 'Envíos',
            'methods' => ShippingMethod::all('sort_order ASC, id ASC'),
        ], 'admin');
    }

    public function store(): void
    {
        Auth::requireLogin();
        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            flash('error', 'Sesión inválida.');
            redirect('/admin/envios');
        }
        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            flash('error', 'El nombre es obligatorio.');
            redirect('/admin/envios');
        }
        ShippingMethod::create([
            'name' => $name,
            'price' => (float) ($_POST['price'] ?? 0),
            'sort_order' => (int) ($_POST['sort_order'] ?? 0),
            'is_active' => 1,
        ]);
        flash('success', 'Método de envío agregado.');
        redirect('/admin/envios');
    }

    public function update(int $id): void
    {
        Auth::requireLogin();
        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            flash('error', 'Sesión inválida.');
            redirect('/admin/envios');
        }
        ShippingMethod::update($id, [
            'name' => trim($_POST['name'] ?? ''),
            'price' => (float) ($_POST['price'] ?? 0),
            'sort_order' => (int) ($_POST['sort_order'] ?? 0),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
        ]);
        flash('success', 'Método de envío actualizado.');
        redirect('/admin/envios');
    }

    public function delete(int $id): void
    {
        Auth::requireLogin();
        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            flash('error', 'Sesión inválida.');
            redirect('/admin/envios');
        }
        ShippingMethod::delete($id);
        flash('success', 'Método de envío eliminado.');
        redirect('/admin/envios');
    }
}
