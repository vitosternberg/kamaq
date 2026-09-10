<?php

namespace App\Controllers;

use App\Core\Cart;
use App\Core\Controller;
use App\Core\CustomerAuth;
use App\Models\CustomerAccount;
use App\Models\Company;

class CustomerAuthController extends Controller
{
    public function showRegister(): void
    {
        if (CustomerAuth::check()) {
            redirect('cuenta');
        }
        $this->view('account/register', [
            'pageTitle' => 'Crear cuenta — delatierra',
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

        $type = ($_POST['customer_type'] ?? 'persona_natural') === 'empresa' ? 'empresa' : 'persona_natural';
        $docType = ($type === 'empresa') ? 'factura' : 'boleta';
        $name = trim($_POST['name'] ?? '');
        $lastname = trim($_POST['lastname'] ?? '');
        $rut = normalize_rut($_POST['rut'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $companyName = trim($_POST['company_name'] ?? '');
        $companyRut = normalize_rut($_POST['company_rut'] ?? '');
        $companyAddress = trim($_POST['company_address'] ?? '');
        $companyEmail = trim($_POST['company_email'] ?? '');
        $companyPhone = trim($_POST['company_phone'] ?? '');
        $giro = trim($_POST['giro'] ?? '');
        $password = (string) ($_POST['password'] ?? '');
        $region = trim($_POST['region'] ?? '');
        $city = trim($_POST['city'] ?? '');

        $errors = [];
        if ($name === '') {
            $errors[] = 'El nombre es obligatorio.';
        }
        if ($lastname === '') {
            $errors[] = 'El apellido es obligatorio.';
        }
        if ($rut !== '' && !valid_rut($rut)) {
            $errors[] = 'Ingresa un RUT válido.';
        } elseif ($rut !== '' && CustomerAccount::rutExists($rut)) {
            $errors[] = 'Ya existe una cuenta con ese RUT.';
        }
        if ($type === 'empresa') {
            if ($companyName === '') {
                $errors[] = 'La razón social es obligatoria.';
            }
            if ($giro === '') {
                $errors[] = 'El giro es obligatorio.';
            }
            if ($companyRut === '' || !valid_rut($companyRut)) {
                $errors[] = 'Ingresa un RUT de empresa válido.';
            } elseif (Company::rutExists($companyRut)) {
                $errors[] = 'Ya existe una empresa con ese RUT.';
            }
        } else {
            if (!in_array($region, chile_regions(), true)) {
                $errors[] = 'Selecciona tu región.';
            }
            if (!is_delivery_commune($city)) {
                $errors[] = 'Lo sentimos, no realizamos despacho a tu comuna. Disponible solo en Providencia, Las Condes, Lo Barnechea, Huechuraba, Vitacura y La Reina.';
            }
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Ingresa un correo válido.';
        }
        if (strlen($password) < 6) {
            $errors[] = 'La contraseña debe tener al menos 6 caracteres.';
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
        $companyId = null;
        if ($type === 'empresa') {
            $companyId = Company::create([
                'rut' => $companyRut,
                'razon_social' => $companyName,
                'giro' => $giro !== '' ? $giro : null,
                'address' => $companyAddress !== '' ? $companyAddress : null,
                'email' => $companyEmail !== '' ? $companyEmail : null,
                'phone' => $companyPhone !== '' ? $companyPhone : null,
            ]);
        }
        CustomerAccount::create([
            'name' => trim($name . ' ' . $lastname),
            'rut' => $rut !== '' ? $rut : null,
            'company_id' => $companyId,
            'doc_type' => $docType,
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
            'Verifica tu cuenta en delatierra',
            "Hola " . trim($name . ' ' . $lastname) . ",\n\nConfirma tu correo abriendo este enlace:\n{$link}\n\nSi no creaste esta cuenta, ignora este mensaje."
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
        redirect(Cart::count() > 0 ? 'checkout' : 'cuenta');
    }

    public function showLogin(): void
    {
        if (CustomerAuth::check()) {
            redirect('cuenta');
        }
        $this->view('account/login', [
            'pageTitle' => 'Iniciar sesión — delatierra',
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
        redirect(Cart::count() > 0 ? 'checkout' : 'cuenta');
    }

    public function logout(): void
    {
        CustomerAuth::logout();
        redirect('');
    }

    public function showForgot(): void
    {
        $this->view('account/forgot', [
            'pageTitle' => 'Recuperar contraseña — delatierra',
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
            $token = CustomerAccount::issueResetToken((int) $account['id']);
            $link = absolute_url('cuenta/recuperar/' . $token);
            send_mail(
                $email,
                'Recupera tu contraseña en delatierra',
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
            'pageTitle' => 'Nueva contraseña — delatierra',
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
        $confirm = (string) ($_POST['password_confirm'] ?? '');
        if (strlen($password) < 6) {
            flash('error', 'La contraseña debe tener al menos 6 caracteres.');
            redirect('cuenta/recuperar/' . $token);
        }
        if ($confirm !== $password) {
            flash('error', 'Las contraseñas no coinciden.');
            redirect('cuenta/recuperar/' . $token);
        }

        CustomerAccount::updatePassword((int) $account['id'], $password);
        flash('success', 'Contraseña actualizada. Inicia sesión.');
        redirect('cuenta/ingresar');
    }
}
