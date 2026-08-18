<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Order;

class OrderController extends Controller
{
    private const STATUSES = ['pendiente', 'pagado', 'enviado', 'entregado', 'cancelado'];

    public function index(): void
    {
        Auth::requireLogin();
        $this->view('admin/orders/index', [
            'pageTitle' => 'Pedidos',
            'orders' => Order::all('created_at DESC'),
        ], 'admin');
    }

    public function show(int $id): void
    {
        Auth::requireLogin();
        $order = Order::withItems($id);
        if (!$order) {
            flash('error', 'Pedido no encontrado.');
            redirect('/admin/pedidos');
        }
        $this->view('admin/orders/show', [
            'pageTitle' => 'Pedido ' . $order['order_number'],
            'order' => $order,
            'statuses' => self::STATUSES,
        ], 'admin');
    }

    public function updateStatus(int $id): void
    {
        Auth::requireLogin();
        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            flash('error', 'Sesión inválida.');
            redirect('/admin/pedidos');
        }
        $status = trim($_POST['status'] ?? '');
        if (in_array($status, self::STATUSES, true)) {
            Order::update($id, ['status' => $status]);
            if ($status === 'pagado') {
                Order::update($id, ['payment_status' => 'pagado']);
            }
            flash('success', 'Estado actualizado.');
        }
        redirect('/admin/pedidos/' . $id);
    }
}
