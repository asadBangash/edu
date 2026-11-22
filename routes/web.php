<?php

use App\Http\Controllers\DemoController;
use App\Http\Controllers\Legacy\CodeIgniterBridgeController;
use App\Http\Controllers\Site\AuthController;
use App\Http\Controllers\SmartSchoolAssetController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DemoController::class, 'index'])->name('demo');

Route::prefix('site')->name('site.')->group(function () {
    Route::match(['get', 'post'], '/login', [AuthController::class, 'staffLogin'])->name('login');
    Route::get('/logout', [AuthController::class, 'staffLogout'])->name('logout');
    Route::match(['get', 'post'], '/userlogin', [AuthController::class, 'userLogin'])->name('userlogin');
    Route::get('/userlogout', [AuthController::class, 'userLogout'])->name('userlogout');
});

Route::get('backend/{path}', [SmartSchoolAssetController::class, 'backend'])
    ->where('path', '.*')
    ->name('edux.backend.asset');

Route::get('theme.css', [SmartSchoolAssetController::class, 'themeCss'])->name('edux.theme.css');

Route::get('uploads/{path}', [SmartSchoolAssetController::class, 'uploads'])
    ->where('path', '.*')
    ->name('edux.uploads');

/*
| Smart School front CMS & public CI routes (materialized runtime under storage/).
*/
$ciBridge = CodeIgniterBridgeController::class;

Route::match(['get', 'post'], '/frontend', $ciBridge)->name('frontend.home');
Route::match(['get', 'post'], '/welcome/{path?}', $ciBridge)->where('path', '.*')->name('welcome');
Route::match(['get', 'post'], '/page/{slug}', $ciBridge)->where('slug', '.*')->name('frontend.page');
Route::match(['get', 'post'], '/read/{slug}', $ciBridge)->where('slug', '.*');

require __DIR__.'/edux.php';
require __DIR__.'/admin.php';
require __DIR__.'/ci-bridge.php';
