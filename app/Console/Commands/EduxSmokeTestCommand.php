<?php

namespace App\Console\Commands;

use App\Services\CodeIgniterSessionBridge;
use App\Services\StaffAuthService;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class EduxSmokeTestCommand extends Command
{
    protected $signature = 'edux:smoke-test {--json=storage/app/edux-smoke-report.json}';

    protected $description = 'HTTP smoke test for Laravel + CodeIgniter bridged routes';

    /** @var list<array{method: string, uri: string, expect: int, name: string}> */
    private array $cases = [
        ['GET', '/', 200, 'Demo landing'],
        ['GET', '/site/login', 200, 'Staff login form'],
        ['GET', '/frontend', 200, 'Front CMS home'],
        ['GET', '/theme.css', 200, 'Theme CSS route'],
        ['GET', '/welcome', 200, 'Welcome (CI front)'],
        ['GET', '/online_admission', 200, 'Online admission entry'],
        ['GET', '/admin/admin/dashboard', 302, 'Dashboard (unauthenticated redirect)'],
    ];

    /** @var list<array{method: string, uri: string, expect: int, name: string}> */
    private array $adminCases = [
        ['GET', '/admin/admin/dashboard', 200, 'Admin dashboard'],
        ['GET', '/student/search', 200, 'Student list'],
        ['GET', '/admin/staff', 200, 'Staff list'],
        ['GET', '/admin/income', 200, 'Income list'],
        ['GET', '/admin/expense', 200, 'Expense list'],
        ['GET', '/admin/feemaster', 200, 'Fee master'],
        ['GET', '/admin/stuattendence', 200, 'Student attendance'],
        ['GET', '/admin/examgroup', 200, 'Exam groups'],
        ['GET', '/admin/onlineexam', 200, 'Online exam'],
        ['GET', '/admin/book', 200, 'Library books'],
        ['GET', '/admin/transport', 200, 'Transport'],
        ['GET', '/admin/payroll', 200, 'Payroll'],
        ['GET', '/admin/front/cms', 200, 'CMS settings'],
        ['GET', '/admin/enquiry', 200, 'Front office enquiry'],
        ['GET', '/admin/notification', 200, 'Notifications'],
        ['GET', '/admin/schsettings', 200, 'School settings'],
    ];

    public function handle(StaffAuthService $staffAuth, CodeIgniterSessionBridge $ciSession): int
    {
        $report = [
            'generated_at' => now()->toIso8601String(),
            'base_url' => config('app.url'),
            'public' => [],
            'authenticated' => [],
            'assets' => [],
            'passed' => 0,
            'failed' => 0,
        ];

        foreach ($this->cases as $case) {
            [$method, $uri, $expect, $name] = $case;
            $report['public'][] = $this->runCase($method, $uri, $expect, $name);
        }

        if (! $staffAuth->attempt('superadmin@gmail.com', 'Demo@123')) {
            $this->error('Could not authenticate superadmin@gmail.com / Demo@123');

            return self::FAILURE;
        }

        Session::save();
        $sessionCookie = config('session.cookie').'='.Session::getId();

        foreach ($this->adminCases as $case) {
            [$method, $uri, $expect, $name] = $case;
            $report['authenticated'][] = $this->runCase($method, $uri, $expect, $name, $sessionCookie);
        }

        $staffAuth->logout();

        $assetUrls = [
            '/backend/dist/css/AdminLTE.min.css',
            '/backend/plugins/chartjs/Chart.min.js',
            '/backend/custom/jquery.min.js',
            '/uploads/school_content/admin_logo/edux-logo.svg',
        ];

        foreach ($assetUrls as $uri) {
            $status = $this->httpStatus((string) config('app.url').$uri);
            $report['assets'][] = [
                'area' => 'asset',
                'name' => 'Asset '.$uri,
                'uri' => $uri,
                'expect' => 200,
                'status' => $status,
                'ok' => $status === 200,
            ];
        }

        foreach (array_merge($report['public'], $report['authenticated'], $report['assets']) as $row) {
            if ($row['ok'] ?? false) {
                $report['passed']++;
            } else {
                $report['failed']++;
            }
        }

        $path = base_path((string) $this->option('json'));
        file_put_contents($path, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $this->table(
            ['Area', 'Test', 'HTTP', 'OK'],
            collect(array_merge($report['public'], $report['authenticated'], $report['assets']))
                ->map(fn ($r) => [
                    $r['area'] ?? 'asset',
                    $r['name'],
                    (string) ($r['status'] ?? $r['expect'] ?? ''),
                    ($r['ok'] ?? false) ? 'yes' : 'no',
                ])
                ->all()
        );

        $this->info("Report: {$path}");
        $this->info("Passed: {$report['passed']}  Failed: {$report['failed']}");

        return $report['failed'] === 0 ? self::SUCCESS : self::FAILURE;
    }

    private function runCase(string $method, string $uri, int $expect, string $name, ?string $sessionCookie = null): array
    {
        $useHttp = in_array($uri, [
            '/frontend',
            '/theme.css',
            '/welcome',
            '/online_admission',
            '/page/home',
        ], true) || ($sessionCookie !== null && $method === 'GET');

        $status = $useHttp && $method === 'GET'
            ? $this->httpStatus(rtrim((string) config('app.url'), '/').$uri, $sessionCookie)
            : $this->dispatch($method, $uri);

        $ok = $status === $expect
            || ($expect === 200 && $status >= 200 && $status < 400);

        return [
            'area' => str_starts_with($uri, '/admin') ? 'admin' : 'public',
            'name' => $name,
            'uri' => $uri,
            'expect' => $expect,
            'status' => $status,
            'ok' => $ok,
        ];
    }

    private function dispatch(string $method, string $uri): int
    {
        if (function_exists('header_remove')) {
            foreach (headers_list() as $header) {
                $name = strtok($header, ':');
                if (is_string($name) && $name !== '') {
                    header_remove($name);
                }
            }
        }

        $symfony = SymfonyRequest::create($uri, $method);
        $request = Request::createFromBase($symfony);
        $session = app('session.store');
        if (method_exists($session, 'isStarted') && ! $session->isStarted()) {
            $session->start();
        }
        $request->setLaravelSession($session);

        $route = Route::getRoutes()->match($request);
        $request->setRouteResolver(fn () => $route);

        $kernel = app(\Illuminate\Contracts\Http\Kernel::class);
        $response = $kernel->handle($request);
        $kernel->terminate($request, $response);

        return $response->getStatusCode();
    }

    private function httpStatus(string $url, ?string $cookieHeader = null): int
    {
        $ch = curl_init($url);
        if ($ch === false) {
            return 0;
        }

        $opts = [
            CURLOPT_NOBODY => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 20,
        ];

        if ($cookieHeader !== null && $cookieHeader !== '') {
            $opts[CURLOPT_HTTPHEADER] = ['Cookie: '.$cookieHeader];
        }

        curl_setopt_array($ch, $opts);
        curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $status;
    }
}