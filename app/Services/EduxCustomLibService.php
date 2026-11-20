<?php

namespace App\Services;

use App\Models\SchSetting;
use Illuminate\Support\Facades\Session;

/**
 * Subset of CodeIgniter Customlib helpers used by admin chrome.
 */
class EduxCustomLibService
{
    public function getAppName(): string
    {
        $admin = Session::get('admin');

        return (string) ($admin['sch_name'] ?? SchSetting::primary()?->name ?? config('edux.product_name'));
    }

    public function getRTL(): string
    {
        $admin = Session::get('admin');
        $isRtl = ($admin['is_rtl'] ?? 'disabled') === 'enabled';

        return $isRtl ? 'dir="rtl"' : '';
    }

    public function getStaffRoleJson(): string
    {
        $admin = Session::get('admin');
        $roles = $admin['roles'] ?? [];
        $name = (string) array_key_first($roles);
        $id = (int) (array_values($roles)[0] ?? 0);

        return json_encode((object) ['name' => $name, 'id' => $id]);
    }

    public function getStaffId(): int
    {
        return (int) (Session::get('admin.id') ?? Session::get('admin')['id'] ?? 0);
    }

    /** @return array<string, mixed> */
    public function getCurrentThemeSetting(): array
    {
        $theme = Session::get('admin.theme') ?? [];

        return is_array($theme) ? $theme : [];
    }
}