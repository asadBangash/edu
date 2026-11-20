<?php

namespace App\Support;

class EduxBrand
{
    public static function cfg(string $key, mixed $default = ''): mixed
    {
        return config("edux.{$key}", $default) ?: $default;
    }

    public static function logoUrl(): string
    {
        return url(self::cfg('logo_path'));
    }

    public static function faviconUrl(): string
    {
        return url(self::cfg('favicon_path'));
    }

    public static function copyright(): string
    {
        return '© '.date('Y').' Global TechX Solutions. All rights reserved.';
    }
}