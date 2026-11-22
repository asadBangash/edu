<?php

namespace App\Http\Controllers\Admin;

use App\Services\SidebarMenuService;
use Illuminate\View\View;

class UnauthorizedController extends BaseAdminController
{
    public function index(SidebarMenuService $menus): View
    {
        return $this->adminView('admin.unauthorized', array_merge([
            'title' => 'Unauthorized',
        ], $this->menuItems($menus)));
    }
}
