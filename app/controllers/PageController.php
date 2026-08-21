<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Category;
use App\Models\Shipping;

class PageController extends Controller
{
    private string $projectUploadDir;

    public function __construct()
    {
        $this->projectUploadDir = BASE_PATH . '/public/uploads/projects';
    }

    public function shipping(): void
    {
        $this->view('pages/shipping', [
            'pageTitle' => 'Política de envío — KAMAQ',
            'shipping' => Shipping::settings(),
            'breadcrumbs' => [
                ['label' => 'Inicio', 'url' => url('')],
                ['label' => 'Política de envío', 'url' => null],
            ],
        ]);
    }

    public function privacy(): void
    {
        $this->view('pages/privacy', [
            'pageTitle' => 'Protección de datos — KAMAQ',
            'breadcrumbs' => [
                ['label' => 'Inicio', 'url' => url('')],
                ['label' => 'Protección de datos', 'url' => null],
            ],
        ]);
    }

    public function howToBuy(): void
    {
        $this->view('pages/how-to-buy', [
            'pageTitle' => 'Cómo comprar — KAMAQ',
            'breadcrumbs' => [
                ['label' => 'Inicio', 'url' => url('')],
                ['label' => 'Cómo comprar', 'url' => null],
            ],
        ]);
    }

    public function projects(): void
    {
        $this->view('pages/projects', [
            'pageTitle' => 'Proyectos — KAMAQ',
            'metaDescription' => 'Tráenos tu diseño y nosotros nos encargamos. Creamos junto contigo el proyecto para tu ocasión especial.',
            'categories' => Category::roots(),
            'breadcrumbs' => [
                ['label' => 'Inicio', 'url' => url('')],
                ['label' => 'Proyectos', 'url' => null],
            ],
        ]);
    }

    public function projectsStore(): void
    {
        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            flash('error', 'Sesión inválida.');
            redirect('proyectos');
        }

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $title = trim($_POST['project_title'] ?? '');
        $categoryId = (int) ($_POST['category_id'] ?? 0);
        $details = trim($_POST['details'] ?? '');

        if ($name === '' || $email === '' || $title === '' || $details === '' || $categoryId <= 0 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            remember_old($_POST);
            flash('error', 'Revisa los campos: nombre, email, título, categoría y detalles son obligatorios.');
            redirect('proyectos');
        }

        $category = Category::find($categoryId);

        $files = $this->saveProjectFiles($_FILES['attachments'] ?? null);

        $to = (string) config('contact_email', 'contacto@kamaq.cl');
        $subject = 'Nuevo proyecto — ' . $title;
        $body = "Nombre: {$name}\n"
            . "Email: {$email}\n"
            . "Teléfono: " . ($phone !== '' ? $phone : '—') . "\n"
            . "Título del proyecto: {$title}\n"
            . "Categoría: " . ($category ? $category['name'] : '—') . "\n\n"
            . "Detalles:\n{$details}\n\n"
            . "Archivos adjuntos: " . (count($files) ? implode(', ', $files) : 'ninguno');
        $headers = 'From: ' . $email . "\r\nReply-To: " . $email;
        @mail($to, $subject, $body, $headers);

        flash('success', 'Gracias por enviarnos tu proyecto. Te responderemos con una cotización.');
        redirect('proyectos');
    }

    private function saveProjectFiles(?array $files): array
    {
        if (!$files || empty($files['name'][0])) {
            return [];
        }
        if (!is_dir($this->projectUploadDir)) {
            @mkdir($this->projectUploadDir, 0755, true);
        }

        $saved = [];
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];
        $maxBytes = 2 * 1024 * 1024;
        $count = count($files['name']);

        for ($i = 0; $i < $count; $i++) {
            if (($files['error'][$i] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                continue;
            }
            if ($files['size'][$i] > $maxBytes) {
                continue;
            }
            $ext = strtolower(pathinfo((string) $files['name'][$i], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed, true)) {
                continue;
            }
            $filename = 'proyecto-' . date('Ymd-His') . '-' . bin2hex(random_bytes(6)) . '.' . $ext;
            if (move_uploaded_file($files['tmp_name'][$i], $this->projectUploadDir . '/' . $filename)) {
                $saved[] = $filename;
            }
        }

        return $saved;
    }
}
