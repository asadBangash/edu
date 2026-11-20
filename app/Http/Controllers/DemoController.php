<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class DemoController extends Controller
{
    public function index(): View
    {
        return view('demo.landing');
    }
}