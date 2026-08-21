<?php

namespace App\Core;

use App\Models\CustomerAccount;

class CustomerAuth
{
    public static function check(): bool
    {
        return !empty($_SESSION['customer_id']);
    }

    public static function id(): ?int
    {
        return self::check() ? (int) $_SESSION['customer_id'] : null;
    }

    public static function user(): ?array
    {
        $id = self::id();
        return $id !== null ? CustomerAccount::find($id) : null;
    }

    public static function login(int $id): void
    {
        $_SESSION['customer_id'] = $id;
        session_regenerate_id(true);
    }

    public static function logout(): void
    {
        unset($_SESSION['customer_id']);
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            flash('error', 'Inicia sesión o regístrate para continuar.');
            redirect('cuenta/ingresar');
        }
    }
}
