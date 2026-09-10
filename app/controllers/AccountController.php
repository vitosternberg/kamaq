<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\CustomerAuth;
use App\Models\Order;

class AccountController extends Controller
{
    public function index(): void
    {
        $customer = $this->requireCustomer();
        $customerId = (int) $customer['id'];
        $email = (string) $customer['email'];

        $stats = Order::statsForCustomer($customerId, $email);
        $topProducts = Order::topProductsForCustomer($customerId, $email);
        $orders = array_slice(Order::forCustomer($customerId, $email), 0, 5);

        $this->view('account/dashboard', [
            'pageTitle' => 'Mi cuenta — delatierra',
            'customer' => $customer,
            'stats' => $stats,
            'topProducts' => $topProducts,
            'orders' => $orders,
            'activeNav' => 'dashboard',
            'breadcrumbs' => [
                ['label' => 'Inicio', 'url' => url('')],
                ['label' => 'Mi cuenta', 'url' => null],
            ],
        ]);
    }

    public function orders(): void
    {
        $customer = $this->requireCustomer();
        $customerId = (int) $customer['id'];
        $email = (string) $customer['email'];

        $this->view('account/orders', [
            'pageTitle' => 'Mis pedidos — delatierra',
            'customer' => $customer,
            'orders' => Order::forCustomer($customerId, $email),
            'activeNav' => 'orders',
            'breadcrumbs' => [
                ['label' => 'Inicio', 'url' => url('')],
                ['label' => 'Mi cuenta', 'url' => url('cuenta')],
                ['label' => 'Mis pedidos', 'url' => null],
            ],
        ]);
    }

    public function orderShow(string $id): void
    {
        $customer = $this->requireCustomer();
        $orderId = (int) $id;
        $order = Order::findForCustomer($orderId, (int) $customer['id'], (string) $customer['email']);

        if (!$order) {
            flash('error', 'No encontramos ese pedido.');
            redirect('cuenta/pedidos');
        }

        $this->view('account/order_show', [
            'pageTitle' => 'Pedido ' . $order['order_number'] . ' — delatierra',
            'customer' => $customer,
            'order' => $order,
            'activeNav' => 'orders',
            'breadcrumbs' => [
                ['label' => 'Inicio', 'url' => url('')],
                ['label' => 'Mi cuenta', 'url' => url('cuenta')],
                ['label' => 'Mis pedidos', 'url' => url('cuenta/pedidos')],
                ['label' => $order['order_number'], 'url' => null],
            ],
        ]);
    }

    private function requireCustomer(): array
    {
        CustomerAuth::requireLogin();
        $customer = CustomerAuth::user();
        if (!$customer) {
            CustomerAuth::logout();
            redirect('cuenta/ingresar');
        }
        return $customer;
    }
}
