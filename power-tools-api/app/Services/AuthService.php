<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function register(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $avatarPath = null;

            if (isset($data['avatar']) && $data['avatar'] instanceof UploadedFile) {
                $avatarPath = $this->storeAvatar($data['avatar']);
            }

            $user = User::create([
                'name' => $data['name'],
                'email' => mb_strtolower(trim($data['email'])),
                'phone' => $data['phone'] ?? null,
                'avatar' => $avatarPath,
                'address' => $data['address'] ?? null,
                'role' => 'customer',
                'status' => true,
                'password' => $data['password'],
                'email_verified_at' => null,
                'last_login_at' => null,
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            return [
                'user' => $user->fresh(),
                'token' => $token,
            ];
        });
    }

    public function login(array $data): array
    {
        $email = mb_strtolower(trim($data['email']));
        $password = $data['password'];

        $user = User::where('email', $email)->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'email' => ['Email hoặc mật khẩu không chính xác.'],
            ]);
        }

        if (! $user->status) {
            throw ValidationException::withMessages([
                'email' => ['Tài khoản của bạn đã bị khóa hoặc ngừng hoạt động.'],
            ]);
        }

        if (! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email hoặc mật khẩu không chính xác.'],
            ]);
        }

        $user->forceFill([
            'last_login_at' => now(),
        ])->save();

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user->fresh(),
            'token' => $token,
        ];
    }

    public function logout(): void
    {
        $user = request()->user();

        if ($user) {
            $user->currentAccessToken()?->delete();
        }
    }

    public function me(): ?User
    {
        return request()->user();
    }

    public function updateProfile(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            $payload = [
                'name' => $data['name'],
                'email' => mb_strtolower(trim($data['email'])),
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
            ];

            if (isset($data['avatar']) && $data['avatar'] instanceof UploadedFile) {
                $this->deleteAvatarIfExists($user->avatar);
                $payload['avatar'] = $this->storeAvatar($data['avatar']);
            }

            $user->update($payload);

            return $user->fresh();
        });
    }

    public function changePassword(User $user, array $data): User
    {
        if (! Hash::check($data['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Mật khẩu hiện tại không chính xác.'],
            ]);
        }

        $user->update([
            'password' => $data['password'],
        ]);

        return $user->fresh();
    }

    protected function storeAvatar(UploadedFile $file): string
    {
        $path = $file->store('avatars', 'public');

        return '/storage/' . $path;
    }

    protected function deleteAvatarIfExists(?string $avatar): void
    {
        if (! $avatar) {
            return;
        }

        $relativePath = ltrim(str_replace('/storage/', '', $avatar), '/');

        if ($relativePath && Storage::disk('public')->exists($relativePath)) {
            Storage::disk('public')->delete($relativePath);
        }
    }
}