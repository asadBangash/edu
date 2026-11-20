<?php

namespace App\Services;

use App\Models\SchSetting;
use App\Models\Staff;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class StaffAuthService
{
    public function __construct(
        private readonly EncLibService $encLib,
    ) {}

    public function attempt(string $email, string $password): bool
    {
        $record = DB::table('staff')
            ->leftJoin('languages', 'languages.id', '=', 'staff.lang_id')
            ->leftJoin('currencies', 'currencies.id', '=', 'staff.currency_id')
            ->select([
                'staff.*',
                'languages.language',
                'languages.short_code',
                'languages.id as language_id',
                'languages.is_rtl',
                DB::raw('IFNULL(currencies.name,0) as currency_name'),
                DB::raw('IFNULL(currencies.symbol,0) as symbol'),
                DB::raw('IFNULL(currencies.base_price,0) as base_price'),
                DB::raw('IFNULL(currencies.id,0) as currency'),
            ])
            ->where('staff.email', $email)
            ->first();

        if (! $record || ! $this->encLib->passHashVerify($password, $record->password)) {
            return false;
        }

        $roleRow = DB::table('staff_roles')
            ->join('roles', 'roles.id', '=', 'staff_roles.role_id')
            ->where('staff_roles.staff_id', $record->id)
            ->select('roles.name', 'roles.id as role_id')
            ->first();

        if (! $roleRow || ! $record->is_active) {
            return false;
        }

        $setting = SchSetting::primary();
        if (! $setting) {
            return false;
        }

        $roles = [$roleRow->name => $roleRow->role_id];
        $langArray = $record->language_id
            ? [
                'lang_id' => (int) $record->language_id,
                'lang_short_code' => $record->short_code,
                'language' => $record->language,
            ]
            : [
                'lang_id' => (int) $setting->lang_id,
                'lang_short_code' => $setting->short_code ?? 'en',
                'language' => $setting->language ?? 'English',
            ];

        $isRtl = ($record->language_id ? $record->is_rtl : $setting->is_rtl) == 1 ? 'enabled' : 'disabled';
        $logUsername = trim($record->name.' '.($record->surname ?? ''));

        $printHeader = DB::table('print_headerfooter')
            ->where('print_type', 'general_purpose')
            ->value('header_image');

        $currencyBasePrice = (float) $record->base_price;
        if ($currencyBasePrice <= 0) {
            $currencyId = (int) ($record->currency ?: $setting->currency ?: 0);
            if ($currencyId > 0) {
                $currencyBasePrice = (float) (DB::table('currencies')->where('id', $currencyId)->value('base_price') ?? 1);
            }
            if ($currencyBasePrice <= 0) {
                $currencyBasePrice = 1.0;
            }
        }

        $sessionAdmin = [
            'id' => $record->id,
            'username' => $logUsername,
            'email' => $record->email,
            'image' => $record->image,
            'roles' => $roles,
            'date_format' => $setting->date_format,
            'currency' => ($record->currency == 0) ? $setting->currency : $record->currency,
            'currency_base_price' => $currencyBasePrice,
            'currency_format' => $setting->currency_format,
            'currency_symbol' => ($record->symbol == '0') ? $setting->currency_symbol : $record->symbol,
            'currency_place' => $setting->currency_place,
            'start_month' => $setting->start_month,
            'start_week' => date('w', strtotime($setting->start_week)),
            'school_name' => $setting->name,
            'timezone' => $setting->timezone,
            'sch_name' => $setting->name,
            'language' => $langArray,
            'is_rtl' => $isRtl,
            'theme' => [
                'theme_color' => $setting->theme_color,
                'theme_shadow' => $setting->theme_shadow,
                'theme_background' => $setting->theme_background,
                'theme_content' => $setting->theme_content,
                'theme_type' => $setting->theme_type,
                'theme_navigation' => $setting->theme_navigation,
                'theme_font_color' => $setting->theme_font_color,
            ],
            'gender' => $record->gender,
            'db_array' => [
                'base_url' => rtrim((string) config('app.url'), '/').'/',
                'folder_path' => $setting->folder_path,
                'db_group' => 'default',
            ],
            'session_array' => $setting->current_session,
            'superadmin_restriction' => $setting->superadmin_restriction,
            'saas_key' => $setting->saas_key,
            'admin_panel_whatsapp' => $setting->admin_panel_whatsapp,
            'admin_panel_whatsapp_mobile' => $setting->admin_panel_whatsapp_mobile,
            'admin_panel_whatsapp_from' => $setting->admin_panel_whatsapp_from,
            'admin_panel_whatsapp_to' => $setting->admin_panel_whatsapp_to,
            'print_heder_image' => $printHeader ?: '',
        ];

        Session::put('admin', $sessionAdmin);
        app(CodeIgniterSessionBridge::class)->handoffStaffSession($sessionAdmin);

        return true;
    }

    public function loggedIn(): bool
    {
        return Session::has('admin');
    }

    public function logout(): void
    {
        app(CodeIgniterSessionBridge::class)->destroyCiSession();
        Session::forget('admin');
        Session::invalidate();
        Session::regenerateToken();
    }

    public function staff(): ?Staff
    {
        $admin = Session::get('admin');
        if (! $admin) {
            return null;
        }

        return Staff::query()->find($admin['id']);
    }
}