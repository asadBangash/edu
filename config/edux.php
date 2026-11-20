<?php

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

    /** Bundled Smart School CI tree used by the Laravel bridge (edu-x/edux). */
    'codeigniter_path' => env('EDUX_CI_PATH', base_path('edux')),

    /** Codecanyon / reference source for sync (Smart School 7.2.0). */
    'smart_school_src' => env('SMART_SCHOOL_SRC', 'E:\\school management ssyte\\smart_school_src'),

    /**
     * Legacy site URL (still on CodeIgniter) for modules not yet ported in Laravel.
     * Used in docs and optional bridging; set in .env if different from edux.test.
     */
    'codeigniter_url' => env('EDUX_CODEIGNITER_URL', 'http://edux.test'),
];