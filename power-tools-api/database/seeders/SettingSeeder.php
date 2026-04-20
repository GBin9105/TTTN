<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'group' => 'general',
                'key' => 'site_name',
                'value' => 'Power Tools Store',
                'type' => 'text',
                'label' => 'Tên website',
                'description' => 'Tên website hiển thị ở frontend',
                'is_public' => true,
            ],
            [
                'group' => 'general',
                'key' => 'site_logo',
                'value' => '/storage/logo.png',
                'type' => 'image',
                'label' => 'Logo website',
                'description' => 'Logo chính của website',
                'is_public' => true,
            ],
            [
                'group' => 'general',
                'key' => 'site_description',
                'value' => 'Website bán dụng cụ điện cầm tay, máy khoan, máy cắt, máy mài và phụ kiện chính hãng.',
                'type' => 'textarea',
                'label' => 'Mô tả website',
                'description' => 'Mô tả ngắn cho website',
                'is_public' => true,
            ],
            [
                'group' => 'contact',
                'key' => 'hotline',
                'value' => '1900 6868',
                'type' => 'text',
                'label' => 'Hotline',
                'description' => 'Số hotline liên hệ',
                'is_public' => true,
            ],
            [
                'group' => 'contact',
                'key' => 'contact_email',
                'value' => 'support@powertools.com',
                'type' => 'text',
                'label' => 'Email liên hệ',
                'description' => 'Email hỗ trợ khách hàng',
                'is_public' => true,
            ],
            [
                'group' => 'contact',
                'key' => 'address',
                'value' => '123 Nguyễn Văn Linh, Quận 7, TP. Hồ Chí Minh',
                'type' => 'textarea',
                'label' => 'Địa chỉ',
                'description' => 'Địa chỉ cửa hàng',
                'is_public' => true,
            ],
            [
                'group' => 'contact',
                'key' => 'map_embed',
                'value' => '<iframe src="https://www.google.com/maps/embed?..."></iframe>',
                'type' => 'textarea',
                'label' => 'Bản đồ nhúng',
                'description' => 'Mã iframe Google Maps',
                'is_public' => true,
            ],
            [
                'group' => 'social',
                'key' => 'social_links',
                'value' => json_encode([
                    'facebook' => 'https://facebook.com/powertools',
                    'zalo' => 'https://zalo.me/0900000001',
                    'youtube' => 'https://youtube.com/@powertools',
                ], JSON_UNESCAPED_UNICODE),
                'type' => 'json',
                'label' => 'Liên kết mạng xã hội',
                'description' => 'Danh sách link mạng xã hội',
                'is_public' => true,
            ],
            [
                'group' => 'seo',
                'key' => 'default_meta_title',
                'value' => 'Power Tools Store - Dụng cụ điện cầm tay chính hãng',
                'type' => 'text',
                'label' => 'Meta title mặc định',
                'description' => 'Tiêu đề SEO mặc định',
                'is_public' => true,
            ],
            [
                'group' => 'seo',
                'key' => 'default_meta_description',
                'value' => 'Chuyên cung cấp máy khoan, máy cắt, máy mài, máy bắt vít, phụ kiện chính hãng, giá tốt.',
                'type' => 'textarea',
                'label' => 'Meta description mặc định',
                'description' => 'Mô tả SEO mặc định',
                'is_public' => true,
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}