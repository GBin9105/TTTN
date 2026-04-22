<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $settings = Setting::query()->orderBy('key')->get();

        return $this->success($settings, 'Settings fetched successfully.');
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'settings'         => ['required', 'array'],
            'settings.*.key'   => ['required', 'string', 'max:255'],
            'settings.*.value' => ['nullable'],
        ]);

        foreach ($data['settings'] as $item) {
            Setting::updateOrCreate(
                ['key' => $item['key']],
                ['value' => $item['value'] ?? null]
            );
        }

        $settings = Setting::query()->orderBy('key')->get();

        return $this->success($settings, 'Settings updated successfully.');
    }
}