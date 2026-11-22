<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UnauthorizedController;
use Illuminate\Support\Facades\Route;

/**
 * Native Laravel admin routes (ported from Smart School). Registered before the CI bridge.
 */
Route::middleware('staff.auth')->prefix('admin/admin')->group(function () {
    if (config('edux.native_admin_dashboard', false)) {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    }

    Route::get('unauthorized', [UnauthorizedController::class, 'index'])->name('admin.unauthorized');
});
