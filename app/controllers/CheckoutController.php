<?php

namespace App\Controllers;

use App\Core\Cart;
use App\Core\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ShippingMethod;

class CheckoutController extends Controller
{
    public function index(): void
    {
        $items = Cart::items();
        if (!$items) {
            redirect('carrito');
        }
        $methods = ShippingMethod::active();
        $this->view('checkout/index', [
            'pageTitle' => 'Finalizar compra — KAMAQ',
            'items' => $items,
            'subtotal' => Cart::subtotal(),
            'shippingMethods' => $methods,
            'shipping' => $methods ? (float) $methods[0]['price'] : 0.0,
            'breadcrumbs' => [
                ['label' => 'Inicio', 'url' => url('')],
                ['label' => 'Carrito', 'url' => url('carrito')],
                ['label' => 'Finalizar compra', 'url' => null],
            ],
        ]);
    }

    public function store(): void
    {
        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            flash('error', 'Sesión inválida.');
            redirect('checkout');
        }
        $items = Cart::items();
        if (!$items) {
            redirect('carrito');
        }

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        if ($name === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            remember_old($_POST);
            flash('error', 'Revisa tus datos: nombre y correo son obligatorios.');
            redirect('checkout');
        }

        $subtotal = Cart::subtotal();
        $method = $this->selectedMethod((int) ($_POST['shipping_method_id'] ?? 0));
        $shipping = $method ? (float) $method['price'] : 0.0;
        $total = $subtotal + $shipping;
        $orderNumber = 'KQ-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));
        $isNewCustomer = Customer::findByEmail($email) === null;

        $orderId = Order::create([
            'order_number' => $orderNumber,
            'customer_name' => $name,
            'customer_email' => $email,
            'customer_phone' => trim($_POST['phone'] ?? ''),
            'address' => trim($_POST['address'] ?? ''),
            'city' => trim($_POST['city'] ?? ''),
            'region' => trim($_POST['region'] ?? ''),
            'notes' => trim($_POST['notes'] ?? ''),
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'shipping_method' => $method ? $method['name'] : null,
            'total' => $total,
            'payment_method' => 'pagaaqui',
            'payment_status' => 'pendiente',
            'status' => 'pendiente',
        ]);

        foreach ($items as $item) {
            OrderItem::create([
                'order_id' => $orderId,
                'product_id' => $item['product']['id'],
                'product_name' => $item['product']['name'],
                'price' => $item['price'],
                'quantity' => $item['quantity'],
                'subtotal' => $item['subtotal'],
            ]);
            Product::decrementStock((int) $item['product']['id'], (int) $item['quantity']);
        }

        Cart::clear();
        flash('success', 'Pedido ' . $orderNumber . ' registrado. Te contactaremos para coordinar el pago.');
        $_SESSION['last_order'] = [
            'order_number' => $orderNumber,
            'total' => $total,
            'new_customer' => $isNewCustomer,
        ];
        redirect('checkout/gracias');
    }

    public function thanks(): void
    {
        $order = $_SESSION['last_order'] ?? null;
        unset($_SESSION['last_order']);
        $this->view('checkout/thanks', [
            'pageTitle' => 'Gracias por tu pedido — KAMAQ',
            'order' => $order,
            'adsConversionId' => (string) config('ga_ads_conversion_id', ''),
            'breadcrumbs' => [
                ['label' => 'Inicio', 'url' => url('')],
                ['label' => 'Carrito', 'url' => url('carrito')],
                ['label' => 'Finalizar compra', 'url' => url('checkout')],
                ['label' => 'Gracias', 'url' => null],
            ],
        ]);
    }

    private function selectedMethod(int $id): ?array
    {
        foreach (ShippingMethod::active() as $method) {
            if ((int) $method['id'] === $id) {
                return $method;
            }
        }
        return null;
    }
}
