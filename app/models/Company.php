<?php

namespace App\Models;

use App\Core\Model;

/**
 * Empresas (personas jurídicas). El representante vive en `customers`
 * vinculado vía `customers.company_id`.
 */
class Company extends Model
{
    protected static string $table = 'companies';

    public static function findByRut(string $rut): ?array
    {
        $stmt = static::db()->prepare('SELECT * FROM companies WHERE rut = ? LIMIT 1');
        $stmt->execute([$rut]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public static function rutExists(string $rut): bool
    {
        return static::findByRut($rut) !== null;
    }
}
