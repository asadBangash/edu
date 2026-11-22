<?php

namespace App\Http\Controllers\Admin;

use App\Models\SchSetting;
use App\Services\SidebarMenuService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

class DashboardController extends BaseAdminController
{
    public function index(SidebarMenuService $menus): View
    {
        if (! config('edux.native_admin_dashboard', true)) {
            abort(404);
        }

        $admin = Session::get('admin');
        $setting = SchSetting::primary();
        $roleId = (int) (array_values($admin['roles'] ?? [])[0] ?? 0);

        $notifications = collect();
        if ($admin && $roleId) {
            $notifications = DB::table('send_notification')
                ->join('notification_roles', 'notification_roles.send_notification_id', '=', 'send_notification.id')
                ->leftJoin('read_notification', function ($join) use ($admin) {
                    $join->on('read_notification.notification_id', '=', 'send_notification.id')
                        ->where('read_notification.staff_id', '=', $admin['id']);
                })
                ->where('send_notification.created_id', '!=', $admin['id'])
                ->where('send_notification.visible_staff', 'yes')
                ->whereNull('read_notification.id')
                ->where('notification_roles.role_id', $roleId)
                ->select('send_notification.*')
                ->orderByDesc('send_notification.id')
                ->limit(10)
                ->get();
        }

        return $this->adminView('admin.dashboard', array_merge([
            'title' => 'Dashboard',
            'role_id' => $roleId,
            'notifications' => $notifications,
            'setting' => $setting,
        ], $this->menuItems($menus)));
    }
}
