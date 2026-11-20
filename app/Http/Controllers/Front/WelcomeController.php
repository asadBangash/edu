<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\SchSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class WelcomeController extends Controller
{
    public function index(): View
    {
        $setting = SchSetting::primary();
        $front = DB::table('front_cms_settings')->first();

        $menu = DB::table('cms_menu')->where('slug', 'main-menu')->first();
        $menuItems = $menu
            ? DB::table('cms_menuitems')->where('menu_id', $menu->id)->orderBy('menu_order')->get()
            : collect();

        $homeSlug = $menuItems->first()->page_slug ?? 'home';
        $page = DB::table('cms_pages')->where('slug', $homeSlug)->first();

        return view('front.home', [
            'setting' => $setting,
            'front' => $front,
            'page' => $page,
            'menuItems' => $menuItems,
        ]);
    }

    public function page(string $slug): View
    {
        $setting = SchSetting::primary();
        $page = DB::table('cms_pages')->where('slug', $slug)->firstOrFail();

        return view('front.page', [
            'setting' => $setting,
            'page' => $page,
        ]);
    }
}