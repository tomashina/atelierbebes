<?php

namespace App\Support;

use App\Services\Settings\SystemSettingsService;

class StorefrontTheme
{
    public static function usesHerreraLayout(?string $storeName = null): bool
    {
        $theme = strtolower(trim((string) config('storefront.theme', 'auto')));

        if ($theme !== '' && $theme !== 'auto') {
            return $theme === 'herrera';
        }

        if ($storeName === null) {
            try {
                $storeName = (string) app(SystemSettingsService::class)
                    ->get('store_brand_name', config('app.name', 'AG Shop'));
            } catch (\Throwable) {
                $storeName = (string) config('app.name', 'AG Shop');
            }
        }

        return str_contains(strtolower($storeName), 'herrera');
    }
}
