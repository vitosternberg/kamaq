<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;

class AuthController extends Controller
{
    public function showLogin(): void
    {
        if (Auth::check()) {
            redirect('/admin');
        }
        $this->view('admin/auth/login', ['pageTitle' => 'Iniciar sesión'], 'admin');
    }

    public function login(): void
    {
        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            flash('error', 'Sesión inválida.');
            redirect('/admin/login');
        }
        $email = trim($_POST['email'] ?? '');
        $password = (string) ($_POST['password'] ?? '');
        if (Auth::attempt($email, $password)) {
            redirect('/admin');
        }
        flash('error', 'Credenciales incorrectas.');
        redirect('/admin/login');
    }

    public function logout(): void
    {
        Auth::logout();
        redirect('/admin/login');
    }
}
