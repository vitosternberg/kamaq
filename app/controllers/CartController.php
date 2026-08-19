<?php

namespace App\Controllers;

use App\Core\Cart;
use App\Core\Controller;
use App\Models\Product;

class CartController extends Controller
{
    public function index(): void
    {
        $items = Cart::items();
        $this->view('cart/index', [
            'pageTitle' => 'Carrito — KAMAQ',
            'items' => $items,
            'subtotal' => Cart::subtotal(),
            'breadcrumbs' => [
                ['label' => 'Inicio', 'url' => url('')],
                ['label' => 'Carrito', 'url' => null],
            ],
        ]);
    }

    public function add(): void
    {
        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            flash('error', 'Sesión inválida.');
            redirect('carrito');
        }
        $productId = (int) ($_POST['product_id'] ?? 0);
        $quantity = (int) ($_POST['quantity'] ?? 1);
        if (!Product::find($productId)) {
            flash('error', 'Producto no encontrado.');
            redirect('carrito');
        }
        Cart::add($productId, $quantity);
        flash('success', 'Producto agregado al carrito.');
        redirect('carrito');
    }

    public function update(): void
    {
        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            flash('error', 'Sesión inválida.');
            redirect('carrito');
        }
        Cart::update((int) ($_POST['product_id'] ?? 0), (int) ($_POST['quantity'] ?? 1));
        redirect('carrito');
    }

    public function remove(): void
    {
        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            flash('error', 'Sesión inválida.');
            redirect('carrito');
        }
        Cart::remove((int) ($_POST['product_id'] ?? 0));
        flash('success', 'Producto eliminado del carrito.');
        redirect('carrito');
    }
}
