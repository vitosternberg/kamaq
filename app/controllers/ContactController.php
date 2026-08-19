<?php

namespace App\Controllers;

use App\Core\Controller;

class ContactController extends Controller
{
    public function index(): void
    {
        $this->view('contact/index', [
            'pageTitle' => 'Contacto — KAMAQ',
            'metaDescription' => 'Contáctanos para cotizar regalos personalizados y corporativos.',
            'breadcrumbs' => [
                ['label' => 'Inicio', 'url' => url('')],
                ['label' => 'Contacto', 'url' => null],
            ],
        ]);
    }

    public function store(): void
    {
        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            flash('error', 'Sesión inválida.');
            redirect('contacto');
        }

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $message = trim($_POST['message'] ?? '');

        if ($name === '' || $email === '' || $message === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            remember_old($_POST);
            flash('error', 'Revisa los campos: nombre, correo y mensaje son obligatorios.');
            redirect('contacto');
        }

        $to = (string) config('contact_email', 'contacto@kamaq.cl');
        $subject = 'Contacto desde el sitio — ' . $name;
        $body = "Nombre: {$name}\nEmail: {$email}\n\n{$message}";
        $headers = 'From: ' . $email . "\r\nReply-To: " . $email;
        @mail($to, $subject, $body, $headers);

        flash('success', 'Gracias por escribirnos. Te responderemos pronto.');
        redirect('contacto');
    }
}
