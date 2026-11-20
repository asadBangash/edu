<?php

namespace App\Providers;

use App\Services\EduxCustomLibService;
use App\Services\RbacService;
use App\Services\SidebarMenuService;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        View::composer(['layouts.admin', 'partials.admin.sidebar'], function ($view) {
            if (! Session::has('admin')) {
                return;
            }

            $view->with([
                'admin' => Session::get('admin'),
                'rbac' => app(RbacService::class),
                'customlib' => app(EduxCustomLibService::class),
                'sidebar_menus' => app(SidebarMenuService::class)->adminMenus(1),
            ]);
        });
    }
}
