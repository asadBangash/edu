<?php

namespace App\Support;

/**
 * Smart School paths: licensed source on disk (read-only) and materialized CI runtime (storage).
 * The Laravel project does not ship or require a top-level edux/ folder.
 */
final class SmartSchoolPaths
{
    public static function sourceRoot(): string
    {
        $path = (string) config('edux.smart_school_src');

        return rtrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path), DIRECTORY_SEPARATOR);
    }

    public static function runtimeRoot(): string
    {
        $path = (string) config('edux.codeigniter_path');

        return rtrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path), DIRECTORY_SEPARATOR);
    }

    public static function uploadsRoot(): string
    {
        $path = (string) config('edux.uploads_path');

        return rtrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path), DIRECTORY_SEPARATOR);
    }

    public static function runtimeReady(): bool
    {
        return is_file(self::runtimeRoot().DIRECTORY_SEPARATOR.'index.php');
    }

    public static function patchesRoot(): string
    {
        return resource_path('smart-school/patches');
    }

    public static function brandingDir(): string
    {
        $candidates = [
            self::runtimeRoot().DIRECTORY_SEPARATOR.'backend'.DIRECTORY_SEPARATOR.'img'.DIRECTORY_SEPARATOR.'edux',
            public_path('backend'.DIRECTORY_SEPARATOR.'img'.DIRECTORY_SEPARATOR.'edux'),
            self::sourceRoot().DIRECTORY_SEPARATOR.'backend'.DIRECTORY_SEPARATOR.'img'.DIRECTORY_SEPARATOR.'edux',
        ];

        foreach ($candidates as $dir) {
            if (is_dir($dir) && count(glob($dir.DIRECTORY_SEPARATOR.'*') ?: []) > 0) {
                return $dir;
            }
        }

        return $candidates[0];
    }

    public static function runtimeUploadsPath(): string
    {
        return self::runtimeRoot().DIRECTORY_SEPARATOR.'uploads';
    }
}
