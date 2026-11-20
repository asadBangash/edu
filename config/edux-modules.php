<?php

/**
 * Smart School module porting map (Laravel status).
 * CI implementation lives in edu-x/edux; native Laravel controllers are added incrementally.
 *
 * @see php artisan edux:ci-inventory
 */
return [
    'phases' => [
        'foundation' => ['site', 'admin/admin', 'common', 'theme', 'welcome'],
        'students' => ['student', 'admin/onlinestudent', 'admin/onlineadmission', 'user/user'],
        'fees' => ['studentfee', 'admin/feemaster', 'user/gateway'],
        'academics' => ['admin/timetable', 'admin/lessonplan', 'homework'],
        'exams' => ['admin/examgroup', 'admin/onlineexam'],
        'hr' => ['admin/staff', 'admin/payroll'],
        'cms' => ['admin/frontcms', 'welcome', 'admin/front'],
        'reports' => ['report', 'financereports', 'attendencereports', 'balancefees'],
        'integrations' => ['app', 'biometric', 'webhooks', 'cron', 'onlineadmission'],
    ],

    'addons' => [
        'cbse_examination' => 'pending',
        'two_factor_authentication' => 'pending',
        'multi_branch' => 'pending',
        'behavior_records' => 'pending',
        'online_course' => 'pending',
        'zoom_live_class' => 'pending',
        'gmeet_live_class' => 'pending',
        'qr_code_attendance' => 'pending',
        'quick_fees_create' => 'pending',
        'thermal_print' => 'pending',
        'whatsapp_messaging' => 'pending',
    ],
];