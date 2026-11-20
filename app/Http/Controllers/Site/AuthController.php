<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\SchSetting;
use App\Services\StaffAuthService;
use App\Services\StudentAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function staffLogin(Request $request, StaffAuthService $staffAuth): View|RedirectResponse
    {
        if ($staffAuth->loggedIn()) {
            return redirect()->to(url('admin/admin/dashboard'));
        }

        $school = SchSetting::primary();
        $errors = [];

        if ($request->isMethod('post')) {
            $request->validate([
                'username' => 'required|string',
                'password' => 'required|string',
            ]);

            if ($staffAuth->attempt($request->input('username'), $request->input('password'))) {
                $redirect = Session::pull('redirect_to', url('admin/admin/dashboard'));

                return redirect()->to($redirect);
            }

            $errors['login'] = 'Invalid username or password.';
        }

        return view('auth.admin-login', [
            'school' => $school,
            'errors' => $errors,
            'is_captcha' => false,
        ]);
    }

    public function staffLogout(StaffAuthService $staffAuth): RedirectResponse
    {
        $staffAuth->logout();

        return redirect()->route('site.login');
    }

    public function userLogin(Request $request, StudentAuthService $studentAuth): View|RedirectResponse
    {
        $school = SchSetting::primary();

        if ($school && (int) $school->student_panel_login === 0 && (int) $school->parent_panel_login === 0) {
            return redirect()->route('site.login');
        }

        if ($studentAuth->loggedIn()) {
            return redirect()->to($studentAuth->redirectPath());
        }

        $errors = [];

        if ($request->isMethod('post')) {
            $request->validate([
                'username' => 'required|string',
                'password' => 'required|string',
            ]);

            $result = $studentAuth->attempt($request->input('username'), $request->input('password'));
            if ($result['ok']) {
                return redirect()->to('/user/user/dashboard');
            }

            $errors['login'] = $result['error'] ?? 'Login failed.';
        }

        return view('auth.user-login', [
            'school' => $school,
            'errors' => $errors,
            'is_captcha' => false,
        ]);
    }

    public function userLogout(StudentAuthService $studentAuth): RedirectResponse
    {
        $studentAuth->logout();

        return redirect()->route('site.userlogin');
    }
}