<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class MemberController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = User::query()->where('role', 'customer');

        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', filter_var($request->status, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $request->status);
        }

        $members = $query->latest('id')->paginate((int) $request->get('per_page', 10));

        return $this->success($members, 'Members fetched successfully.');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'                  => ['required', 'string', 'max:255'],
            'email'                 => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone'                 => ['nullable', 'string', 'max:20', 'unique:users,phone'],
            'address'               => ['nullable', 'string', 'max:255'],
            'avatar'                => ['nullable', 'string', 'max:255'],
            'status'                => ['nullable', 'boolean'],
            'password'              => ['required', 'string', 'min:6'],
            'password_confirmation' => ['required', 'same:password'],
        ]);

        $member = User::create([
            'name'     => $data['name'],
            'email'    => strtolower(trim($data['email'])),
            'phone'    => $data['phone'] ?? null,
            'address'  => $data['address'] ?? null,
            'avatar'   => $data['avatar'] ?? null,
            'status'   => $data['status'] ?? true,
            'role'     => 'customer',
            'password' => Hash::make($data['password']),
        ]);

        return $this->success($member, 'Member created successfully.', 201);
    }

    public function show(User $member)
    {
        return $this->success($member, 'Member detail fetched successfully.');
    }

    public function update(Request $request, User $member)
    {
        $data = $request->validate([
            'name'     => ['sometimes', 'required', 'string', 'max:255'],
            'email'    => ['sometimes', 'required', 'email', 'max:255', 'unique:users,email,' . $member->id],
            'phone'    => ['nullable', 'string', 'max:20', 'unique:users,phone,' . $member->id],
            'address'  => ['nullable', 'string', 'max:255'],
            'avatar'   => ['nullable', 'string', 'max:255'],
            'status'   => ['nullable', 'boolean'],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        if (isset($data['email'])) {
            $data['email'] = strtolower(trim($data['email']));
        }

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $member->update($data);

        return $this->success($member->fresh(), 'Member updated successfully.');
    }

    public function destroy(User $member)
    {
        $member->delete();

        return $this->success(null, 'Member deleted successfully.');
    }
}