<?php

namespace App\Controllers;

use App\Core\Cart;
use App\Core\Controller;
use App\Core\CustomerAuth;
use App\Core\Transbank;
use App\Models\Company;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Setting;
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

        // Documento del cliente: fijo en la cuenta (boleta/factura), no editable en checkout.
        $doc = ['doc_type' => (string) ($customer['doc_type'] ?? 'boleta')];
        if ($doc['doc_type'] === 'factura' && !empty($customer['company_id'])) {
            $company = Company::find((int) $customer['company_id']);
            if ($company) {
                $doc['doc_rut'] = (string) ($company['rut'] ?? '');
                $doc['doc_company'] = (string) ($company['razon_social'] ?? '');
                $doc['doc_giro'] = (string) ($company['giro'] ?? '');
            }
        } else {
            $doc['doc_rut'] = (string) ($customer['rut'] ?? '');
        }

        $this->view('checkout/index', [
            'pageTitle' => 'Finalizar compra — KAMAQ',
            'items' => $items,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'customer' => $customer,
            'paymentMethods' => payment_methods(),
            'doc' => $doc,
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

        $method = ($_POST['payment_method'] ?? 'webpay') === 'transferencia' ? 'transferencia' : 'webpay';

        // Snapshot del documento (boleta/factura) tal como quedó definido en la cuenta.
        $docType = (string) ($customer['doc_type'] ?? 'boleta');
        $docRut = null;
        $docCompany = null;
        $docGiro = null;
        if ($docType === 'factura' && !empty($customer['company_id'])) {
            $company = Company::find((int) $customer['company_id']);
            if ($company) {
                $docRut = $company['rut'] ?? null;
                $docCompany = $company['razon_social'] ?? null;
                $docGiro = $company['giro'] ?? null;
            }
        } else {
            $docRut = $customer['rut'] ?? null;
        }

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
            'payment_method' => $method,
            'doc_type' => $docType,
            'doc_rut' => $docRut,
            'doc_company' => $docCompany,
            'doc_giro' => $docGiro,
            'payment_status' => 'pendiente',
            'status' => 'pendiente',
            'reserved_at' => $now,
            'expires_at' => $method === 'transferencia'
                ? date('Y-m-d H:i:s', time() + 24 * 60 * 60)
                : date('Y-m-d H:i:s', time() + 15 * 60),
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

        // Transferencia: sin Transbank; el pedido queda reservado 24 h (mensaje al cliente: 30 min).
        if ($method === 'transferencia') {
            Cart::clear();
            $_SESSION['last_order'] = [
                'order_number' => $orderNumber,
                'total' => $total,
                'payment_method' => 'transferencia',
            ];
            redirect('checkout/gracias');
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
        header('Location: ' . $result['url'] . '?token_ws=' . rawurlencode((string) $result['token']));
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
                'payment_method' => 'webpay',
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
        $paymentMethod = is_array($order) ? (string) ($order['payment_method'] ?? 'webpay') : 'webpay';
        $transfer = [
            'holder' => (string) Setting::get('transfer_holder', ''),
            'rut' => (string) Setting::get('transfer_rut', ''),
            'bank' => (string) Setting::get('transfer_bank', ''),
            'account_type' => (string) Setting::get('transfer_account_type', ''),
            'account_number' => (string) Setting::get('transfer_account_number', ''),
            'email' => (string) Setting::get('transfer_email', ''),
        ];
        $this->view('checkout/thanks', [
            'pageTitle' => 'Gracias por tu pedido — KAMAQ',
            'order' => $order,
            'paymentMethod' => $paymentMethod,
            'transfer' => $transfer,
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
