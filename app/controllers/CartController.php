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
            'tax' => Cart::tax(),
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
        $product = Product::find($productId);
        if (!$product) {
            flash('error', 'Producto no encontrado.');
            redirect('carrito');
        }
        $stock = (int) $product['stock'];
        if ($stock <= 0) {
            flash('error', 'Este producto no tiene stock disponible.');
            redirect('producto/' . $product['slug']);
        }
        $inCart = Cart::quantityOf($productId);
        Cart::add($productId, $quantity);
        flash('success', $inCart + $quantity > $stock
            ? 'Producto agregado al carrito (stock máximo: ' . $stock . ').'
            : 'Producto agregado al carrito.');
        redirect('carrito');
    }

    public function update(): void
    {
        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            flash('error', 'Sesión inválida.');
            redirect('carrito');
        }
        $productId = (int) ($_POST['product_id'] ?? 0);
        $quantity = (int) ($_POST['quantity'] ?? 1);
        $product = Product::find($productId);
        Cart::update($productId, $quantity);
        if ($product && $quantity > (int) $product['stock']) {
            flash('success', 'Cantidad ajustada al stock disponible (' . (int) $product['stock'] . ').');
        }
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
