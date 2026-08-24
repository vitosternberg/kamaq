<?php

namespace App\Models;

use App\Core\Model;

class QuoteItem extends Model
{
    protected static string $table = 'quote_items';

    public static function forQuote(int $quoteId): array
    {
        $stmt = static::db()->prepare('SELECT * FROM quote_items WHERE quote_id = ? ORDER BY id ASC');
        $stmt->execute([$quoteId]);
        return $stmt->fetchAll();
    }

    public static function deleteForQuote(int $quoteId): void
    {
        $stmt = static::db()->prepare('DELETE FROM quote_items WHERE quote_id = ?');
        $stmt->execute([$quoteId]);
    }
}
