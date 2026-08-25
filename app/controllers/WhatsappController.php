<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\WhatsappContact;

class WhatsappController extends Controller
{
    public function store(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $data = json_decode(file_get_contents('php://input'), true);
        $name = trim((string) ($data['name'] ?? ''));
        $phone = preg_replace('/\D/', '', (string) ($data['phone'] ?? ''));

        if ($name === '') {
            echo json_encode(['success' => false, 'message' => 'Nombre requerido']);
            return;
        }
        if (strlen($phone) !== 9) {
            echo json_encode(['success' => false, 'message' => 'Teléfono inválido (9 dígitos)']);
            return;
        }

        $id = WhatsappContact::create([
            'name' => $name,
            'phone' => $phone,
        ]);

        echo json_encode(['success' => true, 'contact_id' => $id]);
    }
}
