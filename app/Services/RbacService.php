<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

/**
 * CodeIgniter-compatible RBAC (see application/libraries/Rbac.php).
 */
class RbacService
{
    /** @var list<string> */
    public const PERM_CATEGORY = ['can_view', 'can_add', 'can_edit', 'can_delete'];

    public function hasPrivilege(?string $category, ?string $permission): bool
    {
        if ($category === null || $permission === null) {
            return false;
        }

        $admin = Session::get('admin');
        if (! $admin) {
            return false;
        }

        $roles = $admin['roles'] ?? [];
        $roleName = is_array($roles) ? (string) array_key_first($roles) : '';

        if ($roleName === 'Super Admin') {
            return true;
        }

        $roleId = (int) (array_values($roles)[0] ?? 0);
        if ($roleId === 0) {
            return false;
        }

        $row = DB::table('roles_permissions')
            ->join('permission_category', 'permission_category.id', '=', 'roles_permissions.perm_cat_id')
            ->where('roles_permissions.role_id', $roleId)
            ->where('permission_category.short_code', trim($category))
            ->select('roles_permissions.*')
            ->first();

        if (! $row) {
            return false;
        }

        $perms = (array) $row;

        return array_key_exists($permission, $perms) && (int) ($perms[$permission] ?? 0) === 1;
    }

    public function isSuperAdmin(): bool
    {
        $admin = Session::get('admin');
        if (! $admin) {
            return false;
        }

        $roles = $admin['roles'] ?? [];

        return (string) array_key_first($roles) === 'Super Admin';
    }
}