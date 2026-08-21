<?php

namespace App\Controllers;

use App\Core\Cart;
use App\Core\Controller;
use App\Core\CustomerAuth;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Shipping;

class CheckoutController extends Controller
{
    public function index(): void
    {
        $items = Cart::items();
        if (!$items) {
            redirect('carrito');
        }
        CustomerAuth::requireLogin();

        $customer = CustomerAuth::user();
        $subtotal = Cart::subtotal();
        $tax = Cart::tax();
        $isRm = !empty($customer['is_rm']);
        $options = Shipping::options($isRm, $subtotal);

        $this->view('checkout/index', [
            'pageTitle' => 'Finalizar compra — KAMAQ',
            'items' => $items,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'customer' => $customer,
            'shippingOptions' => $options,
            'shipping' => $options ? $options[0]['price'] : 0.0,
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
        CustomerAuth::requireLogin();
        $customer = CustomerAuth::user();

        $subtotal = Cart::subtotal();
        $tax = Cart::tax();
        $isRm = !empty($customer['is_rm']);
        $shipKey = (string) ($_POST['shipping_option'] ?? '');
        $shipping = Shipping::priceFor($shipKey, $isRm, $subtotal);
        $optionName = $this->optionName($shipKey, $isRm, $subtotal);
        $total = $subtotal + $tax + $shipping;
        $orderNumber = 'KQ-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));

        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            $name = (string) $customer['name'];
        }
        $phone = trim($_POST['phone'] ?? '');
        if ($phone === '') {
            $phone = (string) ($customer['phone'] ?? '');
        }
        $address = trim($_POST['address'] ?? '');
        if ($address === '') {
            $address = (string) ($customer['address'] ?? '');
        }
        $city = trim($_POST['city'] ?? '');
        if ($city === '') {
            $city = (string) ($customer['city'] ?? '');
        }

        $orderId = Order::create([
            'customer_id' => (int) $customer['id'],
            'order_number' => $orderNumber,
            'customer_name' => $name,
            'customer_email' => (string) $customer['email'],
            'customer_phone' => $phone,
            'address' => $address,
            'city' => $city,
            'region' => (string) ($customer['region'] ?? ''),
            'notes' => trim($_POST['notes'] ?? ''),
            'subtotal' => $subtotal,
            'tax' => $tax,
            'shipping' => $shipping,
            'shipping_method' => $optionName,
            'total' => $total,
            'payment_method' => 'pagaaqui',
            'payment_status' => 'pendiente',
            'status' => 'pendiente',
        ]);

        foreach ($items as $item) {
            $productCost = $item['product']['cost'] ?? null;
            OrderItem::create([
                'order_id' => $orderId,
                'product_id' => $item['product']['id'],
                'product_name' => $item['product']['name'],
                'price' => $item['price'],
                'quantity' => $item['quantity'],
                'subtotal' => $item['subtotal'],
                'cost' => ($productCost !== null && $productCost !== '') ? (float) $productCost : null,
                'tax_rate' => tax_rate($item['tax_id']),
            ]);
            Product::decrementStock((int) $item['product']['id'], (int) $item['quantity']);
        }

        Cart::clear();
        flash('success', 'Pedido ' . $orderNumber . ' registrado. Te contactaremos para coordinar el pago.');
        $_SESSION['last_order'] = [
            'order_number' => $orderNumber,
            'total' => $total,
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

    private function optionName(string $key, bool $isRm, float $subtotal): string
    {
        foreach (Shipping::options($isRm, $subtotal) as $option) {
            if ($option['key'] === $key) {
                return $option['name'];
            }
        }
        $all = Shipping::options($isRm, $subtotal);
        return $all ? $all[0]['name'] : 'Envío';
    }
}
