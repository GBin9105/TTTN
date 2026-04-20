<?php

namespace App\Services;

use App\Models\Contact;

class ContactService
{
    public function store(array $data): Contact
    {
        return Contact::create([
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'subject' => $data['subject'] ?? null,
            'message' => $data['message'],
            'status' => 'new',
            'reply_message' => null,
            'replied_by' => null,
            'replied_at' => null,
        ]);
    }
}