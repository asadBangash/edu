<?php

namespace App\Services;

use App\Support\MenuPermission;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Admin sidebar from sidebar_menus / sidebar_sub_menus (CI sidebarmenu_model).
 */
class SidebarMenuService
{
    public function __construct(
        private readonly RbacService $rbac,
        private readonly ModuleService $modules,
    ) {}

    /**
     * @return Collection<int, object>
     */
    public function adminMenus(int $sidebarDisplay = 1): Collection
    {
        $menus = DB::table('sidebar_menus')
            ->leftJoin('permission_group', 'permission_group.id', '=', 'sidebar_menus.permission_group_id')
            ->where('sidebar_menus.sidebar_display', $sidebarDisplay)
            ->where('sidebar_menus.is_active', 1)
            ->orderBy('level')
            ->select([
                'sidebar_menus.*',
                'permission_group.name as permission_group_name',
                'permission_group.short_code as short_code',
            ])
            ->get();

        return $menus->filter(fn ($menu) => $this->menuVisible($menu))
            ->map(function ($menu) use ($sidebarDisplay) {
                $menu->submenus = $this->submenusForMenu((int) $menu->id, $sidebarDisplay)
                    ->filter(fn ($sub) => $this->submenuVisible($sub))
                    ->values();

                return $menu;
            })
            ->values();
    }

  /**
     * @return Collection<int, object>
     */
    private function submenusForMenu(int $menuId, int $sidebarDisplay): Collection
    {
        $query = DB::table('sidebar_sub_menus')
            ->leftJoin('permission_group', 'permission_group.id', '=', 'sidebar_sub_menus.permission_group_id')
            ->where('sidebar_sub_menus.sidebar_menu_id', $menuId)
            ->where('sidebar_sub_menus.is_active', 1)
            ->orderBy('level');

        if ($sidebarDisplay === 1) {
            $query->where('sidebar_sub_menus.sidebar_display', $sidebarDisplay);
        }

        return $query->select([
            'sidebar_sub_menus.*',
            'permission_group.name as permission_group_name',
            'permission_group.short_code as short_code',
        ])->get();
    }

    private function menuVisible(object $menu): bool
    {
        $shortCode = $menu->short_code ?? '';
        if ($shortCode !== '') {
            if (! $this->modules->hasModule($shortCode) || ! $this->modules->hasActive($shortCode)) {
                return false;
            }
        }

        return $this->hasAnyAccessPermission($menu->access_permissions ?? '');
    }

    private function submenuVisible(object $submenu): bool
    {
        $shortCode = $submenu->short_code ?? '';
        if ($shortCode !== '' && ! empty($submenu->permission_group_id)) {
            if (! $this->modules->hasActive($shortCode)) {
                return false;
            }
        }

        return $this->hasAnyAccessPermission($submenu->access_permissions ?? '');
    }

    private function hasAnyAccessPermission(string $accessPermissions): bool
    {
        if ($this->rbac->isSuperAdmin()) {
            return true;
        }

        foreach (MenuPermission::splitOrPermissions($accessPermissions) as $chunk) {
            [$category, $perm] = MenuPermission::splitCategoryPermission($chunk);
            if ($category !== '' && $this->rbac->hasPrivilege($category, $perm)) {
                return true;
            }
        }

        return $accessPermissions === '';
    }
}