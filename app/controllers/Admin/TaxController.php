<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Tax;

class TaxController extends Controller
{
    public function index(): void
    {
        Auth::requireLogin();
        $this->view('admin/taxes/index', [
            'pageTitle' => 'Impuestos',
            'taxes' => Tax::all('sort_order ASC, id ASC'),
        ], 'admin');
    }

    public function store(): void
    {
        Auth::requireLogin();
        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            flash('error', 'Sesión inválida.');
            redirect('/admin/impuestos');
        }
        $name = trim($_POST['name'] ?? '');
        $rate = (float) ($_POST['rate'] ?? 0);
        if ($name === '' || $rate < 0) {
            flash('error', 'El nombre y la tasa son obligatorios.');
            redirect('/admin/impuestos');
        }
        Tax::create([
            'name' => $name,
            'rate' => $rate,
            'type' => trim($_POST['type'] ?? '') !== '' ? trim($_POST['type']) : 'IVA',
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'sort_order' => (int) ($_POST['sort_order'] ?? 0),
        ]);
        flash('success', 'Impuesto creado.');
        redirect('/admin/impuestos');
    }

    public function update(int $id): void
    {
        Auth::requireLogin();
        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            flash('error', 'Sesión inválida.');
            redirect('/admin/impuestos');
        }
        Tax::update($id, [
            'name' => trim($_POST['name'] ?? ''),
            'rate' => (float) ($_POST['rate'] ?? 0),
            'type' => trim($_POST['type'] ?? ''),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'sort_order' => (int) ($_POST['sort_order'] ?? 0),
        ]);
        flash('success', 'Impuesto actualizado.');
        redirect('/admin/impuestos');
    }

    public function delete(int $id): void
    {
        Auth::requireLogin();
        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            flash('error', 'Sesión inválida.');
            redirect('/admin/impuestos');
        }
        Tax::delete($id);
        flash('success', 'Impuesto eliminado.');
        redirect('/admin/impuestos');
    }
}
