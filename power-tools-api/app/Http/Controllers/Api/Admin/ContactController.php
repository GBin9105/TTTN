<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Models\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = Contact::query();

        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%")
                    ->orWhere('subject', 'like', "%{$q}%");
            });
        }

        $contacts = $query->latest('id')->paginate((int) $request->get('per_page', 10));

        return $this->success($contacts, 'Contacts fetched successfully.');
    }

    public function show(Contact $contact)
    {
        return $this->success($contact, 'Contact detail fetched successfully.');
    }

    public function reply(Request $request, Contact $contact)
    {
        $data = $request->validate([
            'reply_content' => ['required', 'string'],
            'replied_at'    => ['nullable', 'date'],
        ]);

        $contact->update([
            'reply_content' => $data['reply_content'],
            'replied_at'    => $data['replied_at'] ?? now(),
            'is_replied'    => true,
        ]);

        return $this->success($contact->fresh(), 'Contact replied successfully.');
    }

    public function destroy(Contact $contact)
    {
        $contact->delete();

        return $this->success(null, 'Contact deleted successfully.');
    }
}