<?php

$storageRuntime = storage_path('app'.DIRECTORY_SEPARATOR.'smart-school-runtime');
$projectEdux = base_path('edux');
$projectEduxReady = is_file($projectEdux.DIRECTORY_SEPARATOR.'index.php');
$defaultCiRoot = $projectEduxReady ? $projectEdux : $storageRuntime;
$defaultUploads = is_dir($projectEdux.DIRECTORY_SEPARATOR.'uploads')
    ? $projectEdux.DIRECTORY_SEPARATOR.'uploads'
    : storage_path('app/school-uploads');

return [
    'product_name' => 'EduX School System',
    'tagline' => 'Smart School Management — Peshawar, Pakistan',
    'product_full' => 'EduX School System',
    'company' => 'Global TechX Solutions',
    'company_url' => 'https://globaltechxsolutions.com/',
    'powered_by' => 'EduX School System',
    'developed_by' => 'Developed by Global TechX Solutions',
    'primary' => '#1565D8',
    'secondary' => '#00B4FF',
    'accent' => '#FF8C00',
    'logo_path' => 'backend/img/edux/edux-logo.png',
    'logo_compact_path' => 'backend/img/edux/edux-icon.svg',
    'favicon_path' => 'backend/img/edux/edux-favicon.svg',
    'source_database' => env('EDUX_SOURCE_DB', 'sms'),

    /** Licensed Smart School package (read-only reference). Never modified by EduX. */
    'smart_school_src' => env('SMART_SCHOOL_SRC', 'E:\\school management ssyte\\smart_school_src'),

    /**
     * Materialized CI runtime for the bridge (gitignored under storage/).
     * Build with: php artisan edux:materialize-runtime
     */
    'codeigniter_path' => env('EDUX_CI_PATH', $defaultCiRoot),

    /** Writable school uploads (logos, documents). */
    'uploads_path' => env('EDUX_UPLOADS_PATH', $defaultUploads),

    /** Full Smart School dashboard via bridge until Laravel dashboard is feature-complete. */
    'native_admin_dashboard' => env('EDUX_NATIVE_ADMIN', false),

    /** When false, unmigrated modules are not served via the CI bridge. */
    'ci_bridge_enabled' => env('EDUX_CI_BRIDGE', true),

    'codeigniter_url' => env('EDUX_CODEIGNITER_URL', 'http://edux.test'),
];
