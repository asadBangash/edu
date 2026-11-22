<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\SchSetting;
use App\Services\CodeIgniterSessionBridge;
use App\Services\StudentAuthService;
use App\Support\SmartSchoolPaths;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * Serves unmigrated Smart School routes from the materialized CI runtime (storage/) until native Laravel modules ship.
 */
class CodeIgniterBridgeController extends Controller
{
    public function __invoke(Request $request, CodeIgniterSessionBridge $sessionBridge): SymfonyResponse
    {
        if (! config('edux.ci_bridge_enabled', true)) {
            abort(503, 'CodeIgniter bridge is disabled. Enable EDUX_CI_BRIDGE or migrate this module to Laravel.');
        }

        $ciRoot = SmartSchoolPaths::runtimeRoot();
        $index = $ciRoot.DIRECTORY_SEPARATOR.'index.php';
        if (! is_file($index)) {
            abort(503, 'Smart School runtime missing. Run: php artisan edux:materialize-runtime');
        }

        $this->clearPendingPhpHeaders();

        $ciSessionId = null;

        if (Session::has('admin')) {
            $admin = Session::get('admin');
            $admin = $this->ensureStaffRoles($admin);
            Session::put('admin', $admin);
            $ciSessionId = $sessionBridge->handoffStaffSession($admin);
        } elseif (Session::has('student')) {
            $student = Session::get('student');
            $student = $this->ensureStudentSession($student);
            Session::put('student', $student);
            $studentAuth = app(StudentAuthService::class);
            if (! Session::has('current_class')) {
                $class = $studentAuth->resolveDefaultClass($student);
                if ($class !== null) {
                    Session::put('current_class', $class);
                }
            }
            $ciSessionId = $sessionBridge->handoffStudentSession($student);

            $path = strtolower(trim($request->path(), '/'));
            if ($path === 'user/user/choose' && Session::has('current_class')) {
                return $this->attachCiSessionCookie(
                    redirect('/user/user/dashboard'),
                    $ciSessionId,
                    $request->secure()
                );
            }
        }

        $dbName = (string) config('database.connections.mysql.database', 'edu_x');
        $baseUrl = $request->getSchemeAndHttpHost().'/';

        $sessionPath = storage_path('framework/ci-sessions');
        if (! is_dir($sessionPath)) {
            mkdir($sessionPath, 0755, true);
        }

        putenv('EDUX_CI_DATABASE='.$dbName);
        putenv('EDUX_CI_BASE_URL='.$baseUrl);
        putenv('EDUX_CI_SESSION_PATH='.$sessionPath);
        putenv('EDUX_CI_UPLOADS_PATH='.SmartSchoolPaths::uploadsRoot());

        $path = trim($request->path(), '/');
        $uri = '/'.$path;
        if ($request->getQueryString()) {
            $uri .= '?'.$request->getQueryString();
        }

        $_SERVER['REQUEST_URI'] = $uri;
        $_SERVER['QUERY_STRING'] = (string) $request->getQueryString();
        $_SERVER['REQUEST_METHOD'] = $request->method();
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['PHP_SELF'] = '/index.php';
        $_SERVER['HTTP_HOST'] = $request->getHost();
        $_SERVER['SERVER_NAME'] = $request->getHost();
        $_SERVER['HTTPS'] = $request->secure() ? 'on' : 'off';
        $_SERVER['SERVER_PORT'] = $request->getPort() ?: ($request->secure() ? 443 : 80);

        $_GET = $request->query->all();
        $_POST = $request->request->all();
        $_REQUEST = array_merge($_GET, $_POST);
        $_FILES = $request->files->all();

        $previousDir = getcwd() ?: base_path();
        chdir($ciRoot);

        ob_start();
        $status = Response::HTTP_OK;
        try {
            require $index;
        } finally {
            chdir($previousDir);
        }

        $body = ob_get_clean() ?: '';
        $code = http_response_code();
        if (is_int($code) && $code >= 400) {
            $status = $code;
        }

        foreach (headers_list() as $header) {
            if (stripos($header, 'Location:') === 0) {
                $target = trim(substr($header, strlen('Location:')));
                if ($target !== '') {
                    if (str_starts_with($target, 'http')) {
                        $parsed = parse_url($target);
                        $target = ($parsed['path'] ?? '/').(isset($parsed['query']) ? '?'.$parsed['query'] : '');
                    }

                    return $this->attachCiSessionCookie(
                        redirect($target),
                        $ciSessionId,
                        $request->secure()
                    );
                }
            }
        }

        $response = response($body, $status);

        $this->clearPendingPhpHeaders();

        return $this->attachCiSessionCookie($response, $ciSessionId, $request->secure());
    }

    private function clearPendingPhpHeaders(): void
    {
        if (! function_exists('headers_list') || ! function_exists('header_remove')) {
            return;
        }

        foreach (headers_list() as $header) {
            $name = strtok($header, ':');
            if (is_string($name) && $name !== '') {
                header_remove($name);
            }
        }

    }

    private function attachCiSessionCookie(SymfonyResponse $response, ?string $ciSessionId, bool $secure): SymfonyResponse
    {
        if ($ciSessionId === null || $ciSessionId === '') {
            return $response;
        }

        return $response->withCookie(cookie(
            'ci_session',
            $ciSessionId,
            60 * 24 * 7,
            '/',
            null,
            $secure,
            true,
            false,
            'Lax'
        ));
    }

    /** @param  array<string, mixed>  $admin */
    private function ensureStaffRoles(array $admin): array
    {
        $admin = $this->ensureStaffCurrencyBasePrice($admin);
        $admin = $this->ensureStaffCalendarFields($admin);

        if (! empty($admin['roles']) && is_array($admin['roles']) && key($admin['roles']) !== null) {
            return $admin;
        }

        $staffId = (int) ($admin['id'] ?? 0);
        if ($staffId === 0) {
            return $admin;
        }

        $roleRow = DB::table('staff_roles')
            ->join('roles', 'roles.id', '=', 'staff_roles.role_id')
            ->where('staff_roles.staff_id', $staffId)
            ->select('roles.name', 'roles.id as role_id')
            ->first();

        if ($roleRow) {
            $admin['roles'] = [$roleRow->name => (int) $roleRow->role_id];
        }

        return $admin;
    }

    /** @param  array<string, mixed>  $student */
    private function ensureStudentSession(array $student): array
    {
        if (! empty($student['student_id']) && ! empty($student['login_username'])) {
            return $student;
        }

        $usersId = (int) ($student['id'] ?? 0);
        if ($usersId === 0) {
            return $student;
        }

        $setting = SchSetting::primary();
        if (! $setting) {
            return $student;
        }

        $built = app(StudentAuthService::class)->buildCodeIgniterStudentSession($usersId, $setting);
        if ($built === null) {
            return $student;
        }

        return array_merge($student, $built);
    }

    /**
     * @param  array<string, mixed>  $admin
     * @return array<string, mixed>
     */
    private function ensureStaffCalendarFields(array $admin): array
    {
        if (isset($admin['start_week']) && $admin['start_week'] !== '') {
            $startWeekSet = true;
        } else {
            $startWeekSet = false;
        }

        if (! isset($admin['is_rtl']) || $admin['is_rtl'] === '') {
            $admin['is_rtl'] = 'disabled';
        }

        if ($startWeekSet) {
            return $admin;
        }

        $setting = SchSetting::primary();
        if ($setting && $setting->start_week) {
            $admin['start_week'] = (int) date('w', strtotime($setting->start_week));
        } else {
            $admin['start_week'] = 0;
        }

        return $admin;
    }

    /** @param  array<string, mixed>  $admin */
    private function ensureStaffCurrencyBasePrice(array $admin): array
    {
        $price = (float) ($admin['currency_base_price'] ?? 0);
        if ($price > 0) {
            return $admin;
        }

        $currencyId = (int) ($admin['currency'] ?? 0);
        if ($currencyId <= 0) {
            $currencyId = (int) (DB::table('sch_settings')->value('currency') ?? 0);
        }

        if ($currencyId > 0) {
            $price = (float) (DB::table('currencies')->where('id', $currencyId)->value('base_price') ?? 1);
        }

        $admin['currency_base_price'] = $price > 0 ? $price : 1.0;

        return $admin;
    }
}
