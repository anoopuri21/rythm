<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ContactMessage;

/**
 * Contact form handling: validated payload → persisted message.
 */
final class ContactService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function store(array $data): ContactMessage
    {
        return ContactMessage::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'subject' => $data['subject'] ?? null,
            'message' => $data['message'],
            'status' => 'new',
        ]);
    }
}
