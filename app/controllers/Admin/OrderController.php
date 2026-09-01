<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Dte;
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
            'dte' => Dte::forOrder($id),
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
                // Emisión de DTE no-bloqueante: un fallo no debe romper la actualización.
                try {
                    Dte::emitForOrder($id);
                } catch (\Throwable $e) {
                    error_log('Dte: emisión al marcar pagado falló: ' . $e->getMessage());
                }
            }
            flash('success', 'Estado actualizado.');
        }
        redirect('/admin/pedidos/' . $id);
    }

    // Reintenta la emisión del DTE de un pedido (POST, protegido con CSRF).
    public function retryDte(int $id): void
    {
        Auth::requireLogin();
        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            flash('error', 'Sesión inválida.');
            redirect('/admin/pedidos/' . $id);
        }

        Dte::emitForOrder($id);

        $dte = Dte::forOrder($id);
        $estado = is_array($dte) ? (string) ($dte['estado'] ?? '') : '';
        if ($estado === 'emitido') {
            flash('success', 'DTE emitido correctamente.');
        } elseif ($estado === 'config_pendiente') {
            flash('error', 'DTE pendiente de configuración: falta libredte_hash en config.local.php.');
        } else {
            $glosa = is_array($dte) ? (string) ($dte['glosa'] ?? '') : '';
            flash('error', 'No se pudo emitir el DTE.' . ($glosa !== '' ? ' ' . $glosa : ''));
        }

        redirect('/admin/pedidos/' . $id);
    }
}
