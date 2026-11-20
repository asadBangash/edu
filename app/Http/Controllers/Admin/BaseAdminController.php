<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchSetting;
use App\Services\EduxCustomLibService;
use App\Services\RbacService;
use App\Services\SidebarMenuService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

abstract class BaseAdminController extends Controller
{
    protected function shareAdminContext(): array
    {
        return [
            'admin' => Session::get('admin'),
            'setting' => SchSetting::primary(),
        ];
    }

    protected function adminView(string $view, array $data = []): View
    {
        return view($view, array_merge($this->shareAdminContext(), $data));
    }

    protected function requirePrivilege(
        RbacService $rbac,
        string $category,
        string $permission = 'can_view',
    ): ?RedirectResponse {
        if ($rbac->hasPrivilege($category, $permission)) {
            return null;
        }

        return redirect()->to(url('admin/admin/unauthorized'));
    }

    protected function menuItems(SidebarMenuService $menus): array
    {
        return [
            'sidebar_menus' => $menus->adminMenus(1),
            'customlib' => app(EduxCustomLibService::class),
            'rbac' => app(RbacService::class),
        ];
    }
}