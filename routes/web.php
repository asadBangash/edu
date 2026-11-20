<?php

use App\Http\Controllers\DemoController;
use App\Http\Controllers\Legacy\CodeIgniterBridgeController;
use App\Http\Controllers\Site\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DemoController::class, 'index'])->name('demo');

Route::prefix('site')->name('site.')->group(function () {
    Route::match(['get', 'post'], '/login', [AuthController::class, 'staffLogin'])->name('login');
    Route::get('/logout', [AuthController::class, 'staffLogout'])->name('logout');
    Route::match(['get', 'post'], '/userlogin', [AuthController::class, 'userLogin'])->name('userlogin');
    Route::get('/userlogout', [AuthController::class, 'userLogout'])->name('userlogout');
});

/*
| Smart School front CMS & public CI routes (full Welcome/theme stack from edux/).
*/
$ciBridge = CodeIgniterBridgeController::class;

Route::match(['get', 'post'], '/frontend', $ciBridge)->name('frontend.home');
Route::match(['get', 'post'], '/welcome/{path?}', $ciBridge)->where('path', '.*')->name('welcome');
Route::match(['get', 'post'], '/page/{slug}', $ciBridge)->where('slug', '.*')->name('frontend.page');
Route::match(['get', 'post'], '/read/{slug}', $ciBridge)->where('slug', '.*');

Route::get('/uploads/{path}', function (string $path) {
    $candidates = [
        base_path('edux/uploads/'.$path),
        'D:/laragon/www/edux/uploads/'.$path,
    ];

    foreach ($candidates as $file) {
        if (is_file($file)) {
            return response()->file($file);
        }
    }

    abort(404);
})->where('path', '.*')->name('edux.uploads');

require __DIR__.'/edux.php';
require __DIR__.'/ci-bridge.php';