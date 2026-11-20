<?php

namespace App\Support;

final class MenuPermission
{
    /** @return list<string> */
    public static function splitOrPermissions(string $accessPermissions): array
    {
        if ($accessPermissions === '') {
            return [];
        }

        return array_map(
            'trim',
            explode('||', preg_replace('/\(\'|\'|\)/', '', $accessPermissions))
        );
    }

    /** @return array{0: string, 1: string} */
    public static function splitCategoryPermission(string $value): array
    {
        $parts = array_map('trim', explode(',', preg_replace('/\s+/', '', $value)));

        return [$parts[0] ?? '', $parts[1] ?? 'can_view'];
    }
}