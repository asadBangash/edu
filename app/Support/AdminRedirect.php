<?php

namespace App\Support;

use Illuminate\Http\Request;

final class AdminRedirect
{
    /** @var list<string> */
    private const DENY_PATH_FRAGMENTS = [
        'get_chat_msg_count',
        'get_student_parent_chat_msg_count',
        'getnotifications',
        '/ajax',
        '/json',
    ];

    public static function shouldRemember(Request $request): bool
    {
        if ($request->expectsJson() || $request->ajax()) {
            return false;
        }

        $path = strtolower($request->path());

        foreach (self::DENY_PATH_FRAGMENTS as $fragment) {
            if (str_contains($path, $fragment)) {
                return false;
            }
        }

        if (preg_match('/\/(get_|fetch_|load_|save_|delete_|update_)[a-z0-9_]+$/i', $path) === 1) {
            return false;
        }

        return true;
    }

    public static function sanitizeAfterLogin(?string $url, string $fallback): string
    {
        if ($url === null || $url === '') {
            return $fallback;
        }

        $path = strtolower(parse_url($url, PHP_URL_PATH) ?? '');

        foreach (self::DENY_PATH_FRAGMENTS as $fragment) {
            if (str_contains($path, $fragment)) {
                return $fallback;
            }
        }

        if (preg_match('/\/(get_|fetch_|load_)[a-z0-9_]+$/i', $path) === 1) {
            return $fallback;
        }

        return $url;
    }
}
