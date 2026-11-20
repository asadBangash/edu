<?php

namespace App\Http\Middleware;

use App\Services\RbacService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStaffPrivilege
{
    public function __construct(private readonly RbacService $rbac) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, string $category, string $permission = 'can_view'): Response
    {
        if ($this->rbac->hasPrivilege($category, $permission)) {
            return $next($request);
        }

        return redirect()->to(url('admin/admin/unauthorized'));
    }
}