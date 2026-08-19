<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Customer;

class CustomerController extends Controller
{
    public function index(): void
    {
        Auth::requireLogin();
        $customers = Customer::allCustomers();

        $totalOrders = 0;
        $totalRevenue = 0.0;
        foreach ($customers as $c) {
            $totalOrders += $c['order_count'];
            $totalRevenue += $c['total_spent'];
        }

        $this->view('admin/customers/index', [
            'pageTitle' => 'Clientes',
            'customers' => $customers,
            'stats' => [
                'customers' => count($customers),
                'orders' => $totalOrders,
                'revenue' => $totalRevenue,
                'avgTicket' => $totalOrders > 0 ? $totalRevenue / $totalOrders : 0.0,
            ],
        ], 'admin');
    }

    public function show(string $email): void
    {
        Auth::requireLogin();
        $email = rawurldecode($email);
        $customer = Customer::findByEmail($email);
        if (!$customer) {
            flash('error', 'Cliente no encontrado.');
            redirect('/admin/clientes');
        }

        $this->view('admin/customers/show', [
            'pageTitle' => 'Cliente ' . $customer['name'],
            'customer' => $customer,
            'orders' => Customer::orders($email),
        ], 'admin');
    }
}
