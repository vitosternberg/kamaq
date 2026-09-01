<?php

namespace App\Models;

use App\Core\Model;
use App\Models\Dte;

class Order extends Model
{
    protected static string $table = 'orders';

    public static function recent(int $limit = 5): array
    {
        $stmt = static::db()->prepare('SELECT * FROM orders ORDER BY created_at DESC LIMIT ' . (int) $limit);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function withItems(int $id): ?array
    {
        $order = static::find($id);
        if (!$order) {
            return null;
        }
        $order['items'] = OrderItem::forOrder($id);
        return $order;
    }

    public static function findByToken(string $token): ?array
    {
        $stmt = static::db()->prepare('SELECT * FROM orders WHERE transbank_token = ? LIMIT 1');
        $stmt->execute([$token]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    // Normaliza la fecha de Transbank (ISO 8601, con fracción y zona horaria) a DATETIME MySQL.
    private static function normalizeTbkDate($transactionDate): ?string
    {
        $d = (string) ($transactionDate ?? '');
        if (preg_match('/^(\d{4}-\d{2}-\d{2})[T ](\d{2}:\d{2}:\d{2})/', $d, $m)) {
            return $m[1] . ' ' . $m[2];
        }
        return null;
    }

    public static function markPaid(int $id, array $tbk): void
    {
        static::update($id, [
            'payment_status' => 'pagado',
            'status' => 'pagado',
            'paid_at' => date('Y-m-d H:i:s'),
            'transbank_authorization_code' => $tbk['authorization_code'] ?? null,
            'transbank_payment_type' => $tbk['payment_type_code'] ?? null,
            'transbank_transaction_date' => self::normalizeTbkDate($tbk['transaction_date'] ?? null),
            'transbank_installments' => (int) ($tbk['installments_number'] ?? 0),
        ]);

        // Emisión de DTE no-bloqueante: si falla, no debe romper el pago.
        try {
            Dte::emitForOrder($id);
        } catch (\Throwable $e) {
            error_log('Dte: emisión tras pago falló: ' . $e->getMessage());
        }
    }

    public static function markRejected(int $id): void
    {
        static::update($id, ['payment_status' => 'rechazado']);
    }

    // Devuelve al inventario el stock reservado de un pedido.
    public static function releaseStock(int $orderId): void
    {
        foreach (OrderItem::forOrder($orderId) as $item) {
            if ($item['product_id'] === null) {
                continue;
            }
            Product::incrementStock((int) $item['product_id'], (int) $item['quantity']);
        }
    }

    /**
     * Barrido lazy (se invoca en cada request): expira pedidos pendientes cuyo
     * vencimiento (expires_at) ya pasó, liberando su stock.
     * Usa un UPDATE "claim-first" para evitar doble liberación.
     */
    public static function expireUnpaid(): int
    {
        $stmt = static::db()->query(
            "SELECT id FROM orders
             WHERE payment_status = 'pendiente'
               AND expires_at IS NOT NULL AND expires_at < NOW()"
        );

        $expired = 0;
        $claim = static::db()->prepare(
            "UPDATE orders
             SET payment_status = 'expirado', status = 'cancelado'
             WHERE id = ? AND payment_status = 'pendiente'"
        );

        foreach ($stmt->fetchAll() as $order) {
            $claim->execute([(int) $order['id']]);
            if ($claim->rowCount() === 1) {
                self::releaseStock((int) $order['id']);
                $expired++;
            }
        }

        return $expired;
    }
}
