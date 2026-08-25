<?php

namespace App\Models;

use App\Core\Model;

/**
 * Leads capturados desde el botón de WhatsApp (nombre + teléfono).
 */
class WhatsappContact extends Model
{
    protected static string $table = 'whatsapp_contacts';
}
