<?php

namespace App\Http\Middleware;

use App\Support\AdminRedirect;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class EnsureStaffAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        $admin = Session::get('admin');
        if (! $admin) {
            if (AdminRedirect::shouldRemember($request)) {
                Session::put('redirect_to', $request->fullUrl());
            }

            return redirect()->route('site.login');
        }

        $isActive = DB::table('staff')->where('id', $admin['id'])->value('is_active');
        if ((int) $isActive !== 1) {
            Session::forget('admin');
            if (AdminRedirect::shouldRemember($request)) {
                Session::put('redirect_to', $request->fullUrl());
            }

            return redirect()->route('site.login');
        }

        return $next($request);
    }
}
