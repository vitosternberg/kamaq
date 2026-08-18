<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;

class DashboardController extends Controller
{
    public function index(): void
    {
        Auth::requireLogin();
        $this->view('admin/dashboard/index', [
            'pageTitle' => 'Panel',
            'stats' => [
                'products' => Product::count(),
                'categories' => Category::count(),
                'orders' => Order::count(),
            ],
            'recentOrders' => Order::recent(5),
        ], 'admin');
    }
}
