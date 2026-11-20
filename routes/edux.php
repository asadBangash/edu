<?php

/**
 * EduX legacy URI compatibility routes.
 * Mapped from CodeIgniter `application/config/routes.php` and controller inventory.
 * Unregistered controllers remain listed in docs/MIGRATION_CHECKLIST.md for porting.
 */

use Illuminate\Support\Facades\Route;

Route::get('/online_admission', fn () => redirect('/welcome/admission'))->name('legacy.online_admission');
Route::get('/examresult', fn () => redirect('/welcome/examresult'));
Route::get('/cbseexam', fn () => redirect('/welcome/cbseexam'));
Route::get('/annual_calendar', fn () => redirect('/welcome/annual_calendar'));