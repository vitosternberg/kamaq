<?php

namespace App\Controllers;

use App\Core\Cart;
use App\Core\Controller;
use App\Core\CustomerAuth;
use App\Core\Transbank;
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
        $region = (string) ($customer['region'] ?? '');
        $weight = Cart::weight();
        $options = Shipping::options($region, $weight);

        $this->view('checkout/index', [
            'pageTitle' => 'Finalizar compra — KAMAQ',
            'items' => $items,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'customer' => $customer,
            'shippingOptions' => $options,
            'shipping' => $options ? $options[0]['price'] : 0.0,
            'weight' => $weight,
            'tier' => $options ? $options[0]['tier'] : 'XS',
            'zone' => $options ? $options[0]['zone'] : 'celeste',
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
        $region = (string) ($customer['region'] ?? '');
        $weight = Cart::weight();
        $shipKey = (string) ($_POST['shipping_option'] ?? '');
        $shipping = Shipping::priceFor($shipKey, $region, $weight);
        $optionName = $this->optionName($shipKey, $region, $weight);
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

        $now = date('Y-m-d H:i:s');
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
            'payment_method' => 'webpay',
            'payment_status' => 'pendiente',
            'status' => 'pendiente',
            'reserved_at' => $now,
            'expires_at' => date('Y-m-d H:i:s', time() + 15 * 60),
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

        // Reserva confirmada en el carrito; el pago ocurre en Webpay.
        $result = \App\Core\Transbank::create(
            $orderNumber,
            (string) $customer['id'],
            (int) round((float) $total),
            absolute_url('transbank/retorno')
        );

        if ($result === null || !isset($result['url']) || !isset($result['token'])) {
            Order::update($orderId, ['payment_status' => 'rechazado']);
            Order::releaseStock($orderId);
            flash('error', 'No se pudo iniciar el pago. Intenta nuevamente.');
            redirect('checkout');
        }

        Order::update($orderId, ['transbank_token' => $result['token']]);
        header('Location: ' . $result['url']);
        exit;
    }

    public function retorno(): void
    {
        $token = trim((string) ($_GET['token_ws'] ?? ''));
        if ($token === '') {
            flash('error', 'El pago no se completó. Puedes volver a intentarlo.');
            redirect('checkout');
        }

        $order = Order::findByToken($token);
        if ($order === null) {
            flash('error', 'No encontramos el pedido asociado al pago.');
            redirect('checkout');
        }

        if ($order['payment_status'] === 'pagado') {
            $this->goToThanks($order);
        }

        $res = \App\Core\Transbank::commit($token);
        if ($res === null) {
            flash('error', 'No pudimos confirmar el pago. Intenta nuevamente.');
            redirect('checkout');
        }

        $authorized = (int) ($res['response_code'] ?? 1) === 0
            && ($res['status'] ?? '') === 'AUTHORIZED'
            && (int) ($res['amount'] ?? 0) === (int) round((float) $order['total']);

        if ($authorized) {
            Order::markPaid((int) $order['id'], $res);
            Cart::clear();
            $_SESSION['last_order'] = [
                'order_number' => $order['order_number'],
                'total' => $order['total'],
            ];
            $this->goToThanks($order);
        }

        Order::markRejected((int) $order['id']);
        Order::releaseStock((int) $order['id']);
        flash('error', 'El pago fue rechazado. Puedes intentar nuevamente.');
        redirect('checkout');
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

    // Redirige a la vista de gracias usando la lógica de sesión last_order.
    private function goToThanks(array $order): void
    {
        redirect('checkout/gracias');
    }

    private function optionName(string $key, string $region, float $weight): string
    {
        foreach (Shipping::options($region, $weight) as $option) {
            if ($option['key'] === $key) {
                return $option['name'];
            }
        }
        $all = Shipping::options($region, $weight);
        return $all ? $all[0]['name'] : 'Envío';
    }
}
