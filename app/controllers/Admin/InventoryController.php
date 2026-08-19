<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;

class InventoryController extends Controller
{
    public function index(): void
    {
        Auth::requireLogin();
        $threshold = (int) Setting::get('low_stock_threshold', '5');
        $filter = (string) ($_GET['status'] ?? '');

        $products = Product::adminList();
        foreach ($products as $i => $p) {
            $products[$i]['stock'] = (int) $p['stock'];
            $products[$i]['stock_status'] = $this->stockStatus((int) $p['stock'], $threshold);
        }

        $counts = ['total' => count($products), 'bajo' => 0, 'agotado' => 0];
        foreach ($products as $p) {
            if ($p['stock_status'] === 'bajo') {
                $counts['bajo']++;
            }
            if ($p['stock_status'] === 'agotado') {
                $counts['agotado']++;
            }
        }

        if ($filter !== '') {
            $products = array_values(array_filter($products, fn ($p) => $p['stock_status'] === $filter));
        }

        $this->view('admin/inventory/index', [
            'pageTitle' => 'Inventario',
            'products' => $products,
            'threshold' => $threshold,
            'filter' => $filter,
            'counts' => $counts,
            'categories' => Category::all('sort_order ASC, name ASC'),
        ], 'admin');
    }

    public function updateProduct(): void
    {
        Auth::requireLogin();
        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            flash('error', 'Sesión inválida.');
            redirect('/admin/inventario');
        }
        $id = (int) ($_POST['id'] ?? 0);
        $salePrice = trim($_POST['sale_price'] ?? '');
        Product::update($id, [
            'stock' => (int) ($_POST['stock'] ?? 0),
            'price' => (float) ($_POST['price'] ?? 0),
            'sale_price' => $salePrice !== '' ? (float) $salePrice : null,
        ]);
        flash('success', 'Producto actualizado.');
        redirect('/admin/inventario');
    }

    public function saveThreshold(): void
    {
        Auth::requireLogin();
        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            flash('error', 'Sesión inválida.');
            redirect('/admin/inventario');
        }
        Setting::set('low_stock_threshold', max(0, (int) ($_POST['threshold'] ?? 0)));
        flash('success', 'Umbral actualizado.');
        redirect('/admin/inventario');
    }

    public function bulkPrice(): void
    {
        Auth::requireLogin();
        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            flash('error', 'Sesión inválida.');
            redirect('/admin/inventario');
        }
        $percent = (float) ($_POST['percent'] ?? 0);
        $categoryId = (int) ($_POST['category_id'] ?? 0) ?: null;
        $affected = Product::applyPricePercent($percent, $categoryId);
        flash('success', 'Precios actualizados: ' . $affected . ' productos.');
        redirect('/admin/inventario');
    }

    public function bulkStock(): void
    {
        Auth::requireLogin();
        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            flash('error', 'Sesión inválida.');
            redirect('/admin/inventario');
        }
        $delta = (int) ($_POST['delta'] ?? 0);
        $categoryId = (int) ($_POST['category_id'] ?? 0) ?: null;
        $affected = Product::adjustStock($delta, $categoryId);
        flash('success', 'Stock actualizado: ' . $affected . ' productos.');
        redirect('/admin/inventario');
    }

    private function stockStatus(int $stock, int $threshold): string
    {
        if ($stock <= 0) {
            return 'agotado';
        }
        if ($stock <= $threshold) {
            return 'bajo';
        }
        return 'ok';
    }
}
