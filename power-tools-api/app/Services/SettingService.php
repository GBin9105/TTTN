<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\DB;

class SettingService
{
    public function index(bool $publicOnly = false): array
    {
        $query = Setting::query()->orderBy('group')->orderBy('id');

        if ($publicOnly) {
            $query->where('is_public', true);
        }

        $settings = $query->get();

        $grouped = [];

        foreach ($settings as $setting) {
            $grouped[$setting->group][] = [
                'id' => $setting->id,
                'key' => $setting->key,
                'value' => $this->castValue($setting->value, $setting->type),
                'type' => $setting->type,
                'label' => $setting->label,
                'description' => $setting->description,
                'is_public' => (bool) $setting->is_public,
            ];
        }

        return $grouped;
    }

    public function update(array $data): array
    {
        return DB::transaction(function () use ($data) {
            foreach ($data as $item) {
                if (empty($item['key'])) {
                    continue;
                }

                $setting = Setting::where('key', $item['key'])->first();

                if ($setting) {
                    $setting->update([
                        'value' => is_array($item['value'] ?? null)
                            ? json_encode($item['value'], JSON_UNESCAPED_UNICODE)
                            : ($item['value'] ?? null),
                        'type' => $item['type'] ?? $setting->type,
                        'label' => $item['label'] ?? $setting->label,
                        'description' => $item['description'] ?? $setting->description,
                        'is_public' => $item['is_public'] ?? $setting->is_public,
                        'group' => $item['group'] ?? $setting->group,
                    ]);
                } else {
                    Setting::create([
                        'group' => $item['group'] ?? 'general',
                        'key' => $item['key'],
                        'value' => is_array($item['value'] ?? null)
                            ? json_encode($item['value'], JSON_UNESCAPED_UNICODE)
                            : ($item['value'] ?? null),
                        'type' => $item['type'] ?? 'text',
                        'label' => $item['label'] ?? null,
                        'description' => $item['description'] ?? null,
                        'is_public' => $item['is_public'] ?? false,
                    ]);
                }
            }

            return $this->index(false);
        });
    }

    protected function castValue(?string $value, string $type): mixed
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($value, true) ?? $value,
            default => $value,
        };
    }
}