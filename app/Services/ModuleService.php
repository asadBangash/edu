<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * CodeIgniter-compatible module toggles (see application/libraries/Module_lib.php).
 */
class ModuleService
{
    /** @var array<string, bool>|null */
    private ?array $modules = null;

    public function hasActive(?string $shortCode): bool
    {
        if ($shortCode === null) {
            return false;
        }

        $this->boot();

        return ($this->modules[$shortCode] ?? false) === true;
    }

    public function hasModule(string $shortCode): bool
    {
        return DB::table('permission_group')->where('short_code', $shortCode)->exists();
    }

    /** @return array<string, bool> */
    public function allActive(): array
    {
        $this->boot();

        return $this->modules ?? [];
    }

    private function boot(): void
    {
        if ($this->modules !== null) {
            return;
        }

        $this->modules = Cache::remember('edux.modules.active', 300, function () {
            $map = [];
            $rows = DB::table('permission_group')->select(['short_code', 'is_active'])->get();
            foreach ($rows as $row) {
                $map[$row->short_code] = (int) $row->is_active === 1;
            }

            return $map;
        });
    }

    public function forgetCache(): void
    {
        Cache::forget('edux.modules.active');
        $this->modules = null;
    }
}