<?php

namespace App\Models;

use App\Core\Model;

class Quote extends Model
{
    protected static string $table = 'quotes';

    public const STATUSES = ['borrador', 'enviada', 'aceptada', 'rechazada'];

    public static function withItems(int $id): ?array
    {
        $quote = static::find($id);
        if (!$quote) {
            return null;
        }
        $quote['items'] = QuoteItem::forQuote($id);
        return $quote;
    }

    // Número correlativo por año: COT-2026-0001, COT-2026-0002, ...
    public static function nextNumber(): string
    {
        $year = date('Y');
        $prefix = 'COT-' . $year . '-';
        $stmt = static::db()->prepare(
            'SELECT quote_number FROM quotes WHERE quote_number LIKE ? ORDER BY quote_number DESC LIMIT 1'
        );
        $stmt->execute([$prefix . '%']);
        $last = $stmt->fetchColumn();
        $n = 1;
        if ($last) {
            $n = (int) substr((string) $last, strlen($prefix)) + 1;
        }
        return $prefix . str_pad((string) $n, 4, '0', STR_PAD_LEFT);
    }
}
