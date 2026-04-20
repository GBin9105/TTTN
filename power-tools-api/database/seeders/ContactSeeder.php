<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Database\Seeder;

class ContactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();

        $contacts = [
            [
                'name' => 'Phạm Quốc Dũng',
                'email' => 'dung@example.com',
                'phone' => '0901111111',
                'subject' => 'Hỏi về máy khoan Bosch',
                'message' => 'Cho tôi hỏi máy khoan Bosch GBH 2-26 DRE còn hàng không và bảo hành bao lâu?',
                'status' => 'replied',
                'reply_message' => 'Sản phẩm hiện còn hàng, bảo hành chính hãng 12 tháng.',
                'replied_by' => $admin?->id,
                'replied_at' => now()->subDay(),
            ],
            [
                'name' => 'Ngô Minh Khoa',
                'email' => 'khoa@example.com',
                'phone' => '0902222222',
                'subject' => 'Tư vấn máy bắt vít',
                'message' => 'Mình cần máy bắt vít dùng pin cho đội thi công nội thất, nhờ shop tư vấn.',
                'status' => 'new',
                'reply_message' => null,
                'replied_by' => null,
                'replied_at' => null,
            ],
        ];

        foreach ($contacts as $contact) {
            Contact::updateOrCreate(
                [
                    'email' => $contact['email'],
                    'subject' => $contact['subject'],
                ],
                $contact
            );
        }
    }
}