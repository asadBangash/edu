<?php

namespace App\Services;

use App\Models\SchSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class StudentAuthService
{
    public function attempt(string $username, string $password): array
    {
        $setting = SchSetting::primary();
        if (! $setting) {
            return ['ok' => false, 'error' => 'School settings are not configured.'];
        }

        $studentLogin = json_decode($setting->student_login ?? '[]', true) ?: [];
        $parentLogin = json_decode($setting->parent_login ?? '[]', true) ?: [];

        $user = $this->findUser($username, $studentLogin, $parentLogin);
        if (! $user || ! $this->verifyPassword($password, $user->password)) {
            return ['ok' => false, 'error' => 'Invalid username or password.'];
        }

        if ($user->is_active !== 'yes') {
            return ['ok' => false, 'error' => 'Your account is suspended.'];
        }

        $loginToken = bin2hex(random_bytes(32));
        DB::table('users')->where('id', $user->id)->update(['login_token' => $loginToken]);

        $sessionStudent = $this->buildCodeIgniterStudentSession((int) $user->id, $setting, $loginToken);
        if ($sessionStudent === null) {
            return ['ok' => false, 'error' => 'Unable to load student profile.'];
        }

        $currentClass = $this->resolveDefaultClass($sessionStudent);
        Session::put('student', $sessionStudent);
        if ($currentClass !== null) {
            Session::put('current_class', $currentClass);
        }

        app(CodeIgniterSessionBridge::class)->handoffStudentSession($sessionStudent);

        return ['ok' => true, 'role' => $sessionStudent['role']];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function buildCodeIgniterStudentSession(int $usersId, SchSetting $setting, ?string $loginToken = null): ?array
    {
        $user = DB::table('users')->where('id', $usersId)->first();
        if (! $user) {
            return null;
        }

        $profileQuery = DB::table('users')
            ->leftJoin('languages', 'languages.id', '=', 'users.lang_id')
            ->leftJoin('currencies', 'currencies.id', '=', 'users.currency_id')
            ->where('users.id', $usersId);

        if ($user->role === 'student') {
            $profileQuery->join('students', 'students.id', '=', 'users.user_id');
        } else {
            $profileQuery->leftJoin('students', 'students.parent_id', '=', 'users.id');
        }

        $row = $profileQuery->select([
            'users.*',
            'languages.language',
            'languages.short_code as language_short_code',
            'languages.is_rtl as user_lang_rtl',
            'students.firstname',
            'students.middlename',
            'students.lastname',
            'students.image',
            'students.guardian_name',
            'students.gender',
            'students.father_pic',
            'students.mother_pic',
            'students.guardian_pic',
            'students.guardian_is',
            DB::raw('IFNULL(currencies.short_name, 0) as currency_name'),
            DB::raw('IFNULL(currencies.symbol, 0) as symbol'),
            DB::raw('IFNULL(currencies.base_price, 0) as base_price'),
            DB::raw('IFNULL(currencies.id, 0) as currency_row_id'),
        ])->first();

        if (! $row) {
            return null;
        }

        $currencyId = (int) ($row->currency_row_id ?: ($row->currency_id ?? 0) ?: ($setting->currency ?? 0));
        $currencyBasePrice = (float) ($row->base_price ?: 0);
        if ($currencyBasePrice <= 0 && $currencyId > 0) {
            $currencyBasePrice = (float) (DB::table('currencies')->where('id', $currencyId)->value('base_price') ?? 1);
        }
        if ($currencyBasePrice <= 0) {
            $currencyBasePrice = 1.0;
        }

        $langId = (int) ($row->lang_id ?: $setting->lang_id);
        $language = [
            'lang_id' => $langId,
            'lang_short_code' => $row->language_short_code ?? 'en',
            'language' => $row->language ?? 'English',
        ];

        $isRtl = (($row->user_lang_rtl ?? $setting->is_rtl) == 1) ? 'enabled' : 'disabled';

        $displayName = $row->username;
        $image = '';
        if ($row->role === 'student') {
            $displayName = trim($row->firstname.' '.($row->middlename ?? '').' '.$row->lastname);
            $image = (string) ($row->image ?? '');
        } elseif ($row->role === 'parent') {
            $displayName = (string) ($row->guardian_name ?: $row->username);
            if ($row->guardian_is === 'father') {
                $image = (string) ($row->father_pic ?? '');
            } elseif ($row->guardian_is === 'mother') {
                $image = (string) ($row->mother_pic ?? '');
            } else {
                $image = (string) ($row->guardian_pic ?? '');
            }
        }

        $symbol = $row->symbol && $row->symbol !== '0' ? $row->symbol : ($setting->currency_symbol ?? 'Rs');

        return [
            'id' => (int) $row->id,
            'login_username' => $row->username,
            'student_id' => (int) $row->user_id,
            'role' => $row->role,
            'username' => $displayName,
            'currency' => $currencyId,
            'currency_base_price' => $currencyBasePrice,
            'currency_format' => $setting->currency_format ?? '####.##',
            'currency_symbol' => $symbol,
            'currency_name' => $row->currency_name && $row->currency_name !== '0' ? $row->currency_name : 'PKR',
            'currency_place' => $setting->currency_place ?? 'before',
            'date_format' => $setting->date_format ?? 'd-m-Y',
            'start_week' => date('w', strtotime($setting->start_week ?? 'monday')),
            'timezone' => $setting->timezone ?? 'Asia/Karachi',
            'sch_name' => $setting->name,
            'language' => $language,
            'is_rtl' => $isRtl,
            'theme' => [
                'theme_color' => $setting->theme_color ?? '#1565D8',
                'theme_shadow' => $setting->theme_shadow ?? '',
                'theme_background' => $setting->theme_background ?? '',
                'theme_content' => $setting->theme_content ?? '',
                'theme_type' => $setting->theme_type ?? 'custom',
                'theme_navigation' => $setting->theme_navigation ?? '',
                'theme_font_color' => $setting->theme_font_color ?? '',
            ],
            'image' => $image,
            'gender' => $row->gender ?? '',
            'db_array' => [
                'base_url' => rtrim((string) config('app.url'), '/').'/',
                'folder_path' => $setting->folder_path ?? '',
                'db_group' => 'default',
            ],
            'superadmin_restriction' => $setting->superadmin_restriction ?? '',
            'session_array' => (int) $setting->session_id,
            'admin_panel_whatsapp' => $setting->admin_panel_whatsapp ?? '',
            'admin_panel_whatsapp_mobile' => $setting->admin_panel_whatsapp_mobile ?? '',
            'admin_panel_whatsapp_from' => $setting->admin_panel_whatsapp_from ?? '',
            'admin_panel_whatsapp_to' => $setting->admin_panel_whatsapp_to ?? '',
            'saas_key' => $setting->saas_key ?? '',
            'print_heder_image' => '',
            'login_token' => $loginToken ?? (string) $row->login_token,
        ];
    }

    /**
     * @param  array<string, mixed>  $sessionStudent
     * @return array<string, int>|null
     */
    public function resolveDefaultClass(array $sessionStudent): ?array
    {
        $role = $sessionStudent['role'] ?? '';
        $sessionId = (int) ($sessionStudent['session_array'] ?? 0);

        if ($role === 'student') {
            $studentId = (int) ($sessionStudent['student_id'] ?? 0);
            if ($studentId === 0) {
                return null;
            }

            $query = DB::table('student_session')
                ->where('student_id', $studentId)
                ->when($sessionId > 0, fn ($q) => $q->where('session_id', $sessionId))
                ->orderByDesc('default_login')
                ->orderByDesc('id');

            $row = $query->first();
            if (! $row) {
                $row = DB::table('student_session')->where('student_id', $studentId)->orderByDesc('id')->first();
            }

            if (! $row) {
                return null;
            }

            $classCount = DB::table('student_session')
                ->where('student_id', $studentId)
                ->when($sessionId > 0, fn ($q) => $q->where('session_id', $sessionId))
                ->count();

            if (! (int) $row->default_login && $classCount !== 1) {
                return null;
            }

            if ($classCount === 1 && ! (int) $row->default_login) {
                DB::table('student_session')->where('id', $row->id)->update(['default_login' => 1]);
            }

            return [
                'session_id' => (int) $row->session_id,
                'class_id' => (int) $row->class_id,
                'section_id' => (int) $row->section_id,
                'student_session_id' => (int) $row->id,
            ];
        }

        if ($role === 'parent') {
            $parentUserId = (int) ($sessionStudent['id'] ?? 0);
            $children = DB::table('student_session')
                ->join('students', 'students.id', '=', 'student_session.student_id')
                ->where('students.parent_id', $parentUserId)
                ->when($sessionId > 0, fn ($q) => $q->where('student_session.session_id', $sessionId))
                ->select('student_session.*', 'students.id as student_pk')
                ->orderByDesc('student_session.default_login')
                ->orderByDesc('student_session.id')
                ->get();

            $child = $children->first();
            if (! $child) {
                return null;
            }

            if (! (int) $child->default_login && $children->count() !== 1) {
                return null;
            }

            return [
                'session_id' => (int) $child->session_id,
                'class_id' => (int) $child->class_id,
                'section_id' => (int) $child->section_id,
                'student_session_id' => (int) $child->id,
            ];
        }

        return null;
    }

    private function verifyPassword(string $plain, string $stored): bool
    {
        if (password_verify($plain, $stored)) {
            return true;
        }

        return hash_equals((string) $stored, $plain);
    }

    private function findUser(string $username, array $studentLogin, array $parentLogin): ?object
    {
        $base = DB::table('users')
            ->join('students', 'students.id', '=', 'users.user_id')
            ->select('users.id', 'users.user_id', 'users.username', 'users.password', 'users.role', 'users.is_active', 'users.lang_id')
            ->where('users.username', $username);

        if (in_array('admission_no', $studentLogin, true)) {
            $base->orWhere('students.admission_no', $username);
        }
        if (in_array('mobile_number', $studentLogin, true)) {
            $base->orWhere('students.mobileno', $username);
        }
        if (in_array('email', $studentLogin, true)) {
            $base->orWhere('students.email', $username);
        }

        $user = $base->first();
        if ($user) {
            return $user;
        }

        $parent = DB::table('users')
            ->join('students', 'students.parent_id', '=', 'users.id')
            ->select('users.id', 'users.user_id', 'users.username', 'users.password', 'users.role', 'users.is_active', 'users.lang_id')
            ->where('users.username', $username);

        if (in_array('mobile_number', $parentLogin, true)) {
            $parent->orWhere('students.guardian_phone', $username);
        }
        if (in_array('email', $parentLogin, true)) {
            $parent->orWhere('students.guardian_email', $username);
        }

        return $parent->first();
    }

    public function loggedIn(): bool
    {
        return Session::has('student');
    }

    public function logout(): void
    {
        app(CodeIgniterSessionBridge::class)->destroyCiSession();
        Session::forget('student');
        Session::forget('current_class');
        Session::invalidate();
        Session::regenerateToken();
    }

    public function redirectPath(): string
    {
        if (Session::has('current_class')) {
            return '/user/user/dashboard';
        }

        return '/user/user/choose';
    }
}