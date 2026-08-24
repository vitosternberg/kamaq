<?php

namespace App\Models;

use App\Core\Model;

/**
 * Cuentas de clientes de la tienda (tabla `customers`).
 * Distinto del modelo `Customer`, que agrega métricas desde `orders`.
 */
class CustomerAccount extends Model
{
    protected static string $table = 'customers';

    public static function findByEmail(string $email): ?array
    {
        $stmt = static::db()->prepare('SELECT * FROM customers WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public static function emailExists(string $email): bool
    {
        return static::findByEmail($email) !== null;
    }

    public static function findByVerifyToken(string $token): ?array
    {
        $stmt = static::db()->prepare('SELECT * FROM customers WHERE verify_token = ? LIMIT 1');
        $stmt->execute([$token]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public static function findByResetToken(string $token): ?array
    {
        $stmt = static::db()->prepare(
            'SELECT * FROM customers WHERE reset_token = ? LIMIT 1'
        );
        $stmt->execute([$token]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public static function findByRut(string $rut): ?array
    {
        $stmt = static::db()->prepare('SELECT * FROM customers WHERE rut = ? LIMIT 1');
        $stmt->execute([$rut]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public static function rutExists(string $rut): bool
    {
        return static::findByRut($rut) !== null;
    }

    // Primer representante (persona) vinculado a una empresa.
    public static function findByCompany(int $companyId): ?array
    {
        $stmt = static::db()->prepare('SELECT * FROM customers WHERE company_id = ? LIMIT 1');
        $stmt->execute([$companyId]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }
}
