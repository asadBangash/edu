<?php

namespace App\Http\Controllers\Admin;

use Illuminate\View\View;

class UnauthorizedController extends BaseAdminController
{
    public function index(): View
    {
        return $this->adminView('admin.unauthorized', [
            'title' => 'Unauthorized',
        ]);
    }
}