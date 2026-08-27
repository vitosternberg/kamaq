<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Finance;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;

class DashboardController extends Controller
{
    public function index(): void
    {
        Auth::requireLogin();

        $since = date('Y-m-d H:i:s', strtotime('-30 days'));
        $marketing = (float) Setting::get('finance_marketing', '0');
        $fixedCosts = (float) Setting::get('finance_fixed_costs', '0');

        $revenue = Finance::revenue($since);
        $variable = Finance::variableCost($since);
        $margin = $revenue - $variable;
        $roi = $marketing > 0 ? ($revenue - $marketing) / $marketing : null;
        $cashFlow = $revenue - $variable - $fixedCosts - $marketing;

        $this->view('admin/dashboard/index', [
            'pageTitle' => 'Panel',
            'stats' => [
                'products' => Product::count(),
                'categories' => Category::count(),
                'orders' => Order::count(),
                'customers' => Customer::count(),
            ],
            'recentOrders' => Order::recent(5),
            'heroProducts' => Product::featured(50),
            'finance' => [
                'marketing' => $marketing,
                'fixedCosts' => $fixedCosts,
                'revenue' => $revenue,
                'variableCost' => $variable,
                'margin' => $margin,
                'roi' => $roi,
                'cashFlow' => $cashFlow,
            ],
        ], 'admin');
    }

    public function saveFinance(): void
    {
        Auth::requireLogin();
        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            flash('error', 'Sesión inválida.');
            redirect('/admin');
        }
        Setting::set('finance_marketing', (string) max(0, (float) ($_POST['marketing'] ?? 0)));
        Setting::set('finance_fixed_costs', (string) max(0, (float) ($_POST['fixed_costs'] ?? 0)));
        flash('success', 'Datos financieros actualizados.');
        redirect('/admin');
    }
}
