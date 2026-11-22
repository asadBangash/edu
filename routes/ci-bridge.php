<?php

use App\Http\Controllers\Legacy\CodeIgniterBridgeController;
use Illuminate\Support\Facades\Route;

/**
 * CodeIgniter Smart School 7.x — served from storage via Laravel until native modules ship.
 */
if (! config('edux.ci_bridge_enabled', true)) {
    return;
}

$bridge = CodeIgniterBridgeController::class;

Route::get('fronttheme.css', $bridge);

/** Public / unauthenticated CI entry points */
Route::any('cron/{path?}', $bridge)->where('path', '.*');
Route::any('app/{path?}', $bridge)->where('path', '.*');
Route::any('biometric/{path?}', $bridge)->where('path', '.*');
Route::any('webhooks/{path?}', $bridge)->where('path', '.*');
Route::any('common/{path?}', $bridge)->where('path', '.*');
Route::any('online_admission', $bridge);
Route::any('examresult', $bridge);
Route::any('cbseexam', $bridge);
Route::any('online_course/{path?}', $bridge)->where('path', '.*');
Route::any('annual_calendar', $bridge);
Route::any('download-receipt/{token}', $bridge)->where('token', '.*');
Route::any('download-marksheet/{token}', $bridge)->where('token', '.*');
Route::any('download-exam-marksheet/{token}', $bridge)->where('token', '.*');

/** Root-level admin modules (not under admin/ prefix) */
Route::middleware('staff.auth')->group(function () use ($bridge) {
    Route::any('admin/{path}', $bridge)->where('path', '.*');
    Route::any('student/{path?}', $bridge)->where('path', '.*');
    Route::any('studentfee/{path?}', $bridge)->where('path', '.*');
    Route::any('homework/{path?}', $bridge)->where('path', '.*');
    Route::any('schsettings/{path?}', $bridge)->where('path', '.*');
    Route::any('report/{path?}', $bridge)->where('path', '.*');
    Route::any('financereports/{path?}', $bridge)->where('path', '.*');
    Route::any('attendencereports/{path?}', $bridge)->where('path', '.*');
    Route::any('balancefees/{path?}', $bridge)->where('path', '.*');
    Route::any('emailconfig/{path?}', $bridge)->where('path', '.*');
    Route::any('smsconfig/{path?}', $bridge)->where('path', '.*');
    Route::any('classes/{path?}', $bridge)->where('path', '.*');
});

/** User / role panels (student session) */
Route::any('user/{path}', $bridge)->where('path', '.*');
Route::any('teacher/{path}', $bridge)->where('path', '.*');
Route::any('accountant/{path}', $bridge)->where('path', '.*');
Route::any('librarian/{path}', $bridge)->where('path', '.*');
Route::any('parent/{path}', $bridge)->where('path', '.*');

Route::any('onlineadmission/{path?}', $bridge)->where('path', '.*');

/**
 * Remaining CodeIgniter URIs. Laravel routes registered earlier take precedence.
 */
Route::fallback($bridge);
