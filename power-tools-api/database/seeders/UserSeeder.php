<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@powertools.com'],
            [
                'name' => 'Administrator',
                'phone' => '0900000001',
                'avatar' => null,
                'address' => 'TP. Hồ Chí Minh',
                'role' => 'admin',
                'status' => true,
                'password' => Hash::make('12345678'),
                'email_verified_at' => now(),
                'last_login_at' => now(),
            ]
        );

        $customers = [
            [
                'name' => 'Nguyễn Văn An',
                'email' => 'an@example.com',
                'phone' => '0900000002',
                'address' => 'Quận 1, TP. Hồ Chí Minh',
            ],
            [
                'name' => 'Trần Thị Bình',
                'email' => 'binh@example.com',
                'phone' => '0900000003',
                'address' => 'Thủ Đức, TP. Hồ Chí Minh',
            ],
            [
                'name' => 'Lê Hoàng Cường',
                'email' => 'cuong@example.com',
                'phone' => '0900000004',
                'address' => 'Biên Hòa, Đồng Nai',
            ],
        ];

        foreach ($customers as $customer) {
            User::updateOrCreate(
                ['email' => $customer['email']],
                [
                    'name' => $customer['name'],
                    'phone' => $customer['phone'],
                    'avatar' => null,
                    'address' => $customer['address'],
                    'role' => 'customer',
                    'status' => true,
                    'password' => Hash::make('12345678'),
                    'email_verified_at' => now(),
                    'last_login_at' => null,
                ]
            );
        }
    }
}