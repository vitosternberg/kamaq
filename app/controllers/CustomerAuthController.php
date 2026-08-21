<?php

namespace App\Controllers;

use App\Core\Cart;
use App\Core\Controller;
use App\Core\CustomerAuth;
use App\Models\CustomerAccount;

class CustomerAuthController extends Controller
{
    public function showRegister(): void
    {
        if (CustomerAuth::check()) {
            redirect('checkout');
        }
        $this->view('account/register', [
            'pageTitle' => 'Crear cuenta — KAMAQ',
            'breadcrumbs' => [
                ['label' => 'Inicio', 'url' => url('')],
                ['label' => 'Crear cuenta', 'url' => null],
            ],
        ]);
    }

    public function register(): void
    {
        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            flash('error', 'Sesión inválida.');
            redirect('cuenta/registro');
        }

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = (string) ($_POST['password'] ?? '');
        $region = trim($_POST['region'] ?? '');
        $city = trim($_POST['city'] ?? '');

        $errors = [];
        if ($name === '') {
            $errors[] = 'El nombre es obligatorio.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Ingresa un correo válido.';
        }
        if (strlen($password) < 6) {
            $errors[] = 'La contraseña debe tener al menos 6 caracteres.';
        }
        if (!in_array($region, chile_regions(), true)) {
            $errors[] = 'Selecciona tu región.';
        }
        if (CustomerAccount::emailExists($email)) {
            $errors[] = 'Ya existe una cuenta con ese correo.';
        }

        if ($errors) {
            remember_old($_POST);
            flash('error', implode(' ', $errors));
            redirect('cuenta/registro');
        }

        $verifyToken = bin2hex(random_bytes(32));
        CustomerAccount::create([
            'name' => $name,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'phone' => trim($_POST['phone'] ?? ''),
            'region' => $region,
            'is_rm' => is_rm_region($region) ? 1 : 0,
            'city' => $city,
            'address' => trim($_POST['address'] ?? ''),
            'email_verified' => 0,
            'verify_token' => $verifyToken,
        ]);

        $link = absolute_url('cuenta/verificar/' . $verifyToken);
        send_mail(
            $email,
            'Verifica tu cuenta en KAMAQ',
            "Hola {$name},\n\nConfirma tu correo abriendo este enlace:\n{$link}\n\nSi no creaste esta cuenta, ignora este mensaje."
        );

        flash('success', 'Te enviamos un correo para verificar tu cuenta. Revisa tu bandeja.');
        redirect('cuenta/ingresar');
    }

    public function verify(string $token): void
    {
        $account = CustomerAccount::findByVerifyToken($token);
        if (!$account) {
            flash('error', 'El enlace de verificación no es válido.');
            redirect('cuenta/ingresar');
        }

        CustomerAccount::update((int) $account['id'], [
            'email_verified' => 1,
            'verify_token' => null,
        ]);
        CustomerAuth::login((int) $account['id']);
        flash('success', 'Cuenta verificada. ¡Bienvenido!');
        redirect('checkout');
    }

    public function showLogin(): void
    {
        if (CustomerAuth::check()) {
            redirect('checkout');
        }
        $this->view('account/login', [
            'pageTitle' => 'Iniciar sesión — KAMAQ',
            'breadcrumbs' => [
                ['label' => 'Inicio', 'url' => url('')],
                ['label' => 'Iniciar sesión', 'url' => null],
            ],
        ]);
    }

    public function login(): void
    {
        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            flash('error', 'Sesión inválida.');
            redirect('cuenta/ingresar');
        }

        $email = trim($_POST['email'] ?? '');
        $password = (string) ($_POST['password'] ?? '');
        $account = CustomerAccount::findByEmail($email);

        if (!$account || !password_verify($password, $account['password_hash'])) {
            flash('error', 'Credenciales incorrectas.');
            redirect('cuenta/ingresar');
        }
        if (empty($account['email_verified'])) {
            flash('error', 'Debes verificar tu correo antes de iniciar sesión.');
            redirect('cuenta/ingresar');
        }

        CustomerAuth::login((int) $account['id']);
        redirect(Cart::count() > 0 ? 'checkout' : '');
    }

    public function logout(): void
    {
        CustomerAuth::logout();
        redirect('');
    }

    public function showForgot(): void
    {
        $this->view('account/forgot', [
            'pageTitle' => 'Recuperar contraseña — KAMAQ',
            'breadcrumbs' => [
                ['label' => 'Inicio', 'url' => url('')],
                ['label' => 'Recuperar contraseña', 'url' => null],
            ],
        ]);
    }

    public function forgot(): void
    {
        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            flash('error', 'Sesión inválida.');
            redirect('cuenta/olvide');
        }

        $email = trim($_POST['email'] ?? '');
        $account = CustomerAccount::findByEmail($email);
        if ($account) {
            $token = bin2hex(random_bytes(32));
            CustomerAccount::update((int) $account['id'], [
                'reset_token' => $token,
                'reset_token_expires' => date('Y-m-d H:i:s', time() + 86400),
            ]);
            $link = absolute_url('cuenta/recuperar/' . $token);
            send_mail(
                $email,
                'Recupera tu contraseña en KAMAQ',
                "Hola,\n\nPara cambiar tu contraseña abre este enlace:\n{$link}\n\nSi no lo pediste, ignora este mensaje."
            );
        }

        flash('success', 'Si el correo está registrado, te enviamos un enlace para recuperar tu contraseña.');
        redirect('cuenta/ingresar');
    }

    public function showReset(string $token): void
    {
        $account = CustomerAccount::findByResetToken($token);
        if (!$account) {
            flash('error', 'El enlace expiró o no es válido.');
            redirect('cuenta/olvide');
        }
        $this->view('account/reset', [
            'pageTitle' => 'Nueva contraseña — KAMAQ',
            'token' => $token,
            'breadcrumbs' => [
                ['label' => 'Inicio', 'url' => url('')],
                ['label' => 'Nueva contraseña', 'url' => null],
            ],
        ]);
    }

    public function reset(string $token): void
    {
        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            flash('error', 'Sesión inválida.');
            redirect('cuenta/recuperar/' . $token);
        }

        $account = CustomerAccount::findByResetToken($token);
        if (!$account) {
            flash('error', 'El enlace expiró o no es válido.');
            redirect('cuenta/olvide');
        }

        $password = (string) ($_POST['password'] ?? '');
        if (strlen($password) < 6) {
            flash('error', 'La contraseña debe tener al menos 6 caracteres.');
            redirect('cuenta/recuperar/' . $token);
        }

        CustomerAccount::update((int) $account['id'], [
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'reset_token' => null,
            'reset_token_expires' => null,
        ]);
        flash('success', 'Contraseña actualizada. Inicia sesión.');
        redirect('cuenta/ingresar');
    }
}
