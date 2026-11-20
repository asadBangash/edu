<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class EduxSyncSmartSchoolCommand extends Command
{
    protected $signature = 'edux:sync-smart-school
                            {--source= : Override Smart School src path}
                            {--dry-run : List actions without copying}
                            {--force : Copy even when paths look identical}';

    protected $description = 'Sync Smart School 7.x source from E:\\school management ssyte\\smart_school_src into edu-x/edux';

    /** @var list<string> */
    private array $syncDirs = ['application', 'backend', 'system'];

    /** @var list<string> */
    private array $syncFiles = ['index.php', '.htaccess'];

    /** @var list<string> */
    private array $excludeRelative = [
        'application/config/database.php',
        'application/config/config.php',
        'application/logs',
        'application/cache',
        // EduX bridge fixes (do not overwrite when syncing Codecanyon src)
        'application/libraries/Rbac.php',
        'application/libraries/Customlib.php',
        'application/views/admin/dashboard.php',
        'application/views/admin/login.php',
        'application/views/layout/header.php',
        'application/views/userlogin.php',
        'application/core/MY_Controller.php',
        'application/controllers/Site.php',
    ];

    public function handle(): int
    {
        $source = $this->option('source') ?: config('edux.smart_school_src');
        $target = base_path('edux');

        if (! is_dir($source)) {
            $this->error("Source not found: {$source}");
            $this->line('Set SMART_SCHOOL_SRC in .env or pass --source=');

            return self::FAILURE;
        }

        if (! is_dir($target)) {
            $this->error("Target not found: {$target}");

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $copied = 0;
        $skipped = 0;

        foreach ($this->syncDirs as $dir) {
            $from = $source.DIRECTORY_SEPARATOR.$dir;
            $to = $target.DIRECTORY_SEPARATOR.$dir;

            if (! is_dir($from)) {
                $this->warn("Skip missing source dir: {$dir}");

                continue;
            }

            if (! is_dir($to) && ! $dryRun) {
                File::makeDirectory($to, 0755, true);
            }

            $this->info("Syncing {$dir}/ …");
            [$c, $s] = $this->mirrorDirectory($from, $to, $dir, $dryRun);
            $copied += $c;
            $skipped += $s;
        }

        foreach ($this->syncFiles as $file) {
            $from = $source.DIRECTORY_SEPARATOR.$file;
            $to = $target.DIRECTORY_SEPARATOR.$file;

            if (! is_file($from)) {
                continue;
            }

            if ($this->isExcluded($file) || ($this->filesEqual($from, $to) && ! $this->option('force'))) {
                $skipped++;

                continue;
            }

            if ($dryRun) {
                $this->line("  would copy {$file}");
                $copied++;
            } else {
                File::copy($from, $to);
                $this->line("  copied {$file}");
                $copied++;
            }
        }

        $this->newLine();
        $this->info($dryRun ? "Dry run: {$copied} file(s) would update, {$skipped} skipped." : "Done: {$copied} file(s) updated, {$skipped} skipped.");
        $this->line('uploads/ is not synced (school-specific). Run edux:seed-assets or copy manually if needed.');
        $this->line('After sync, re-test: php artisan edux:smoke-test');

        return self::SUCCESS;
    }

    /**
     * @return array{0: int, 1: int} copied, skipped
     */
    private function mirrorDirectory(string $from, string $to, string $prefix, bool $dryRun): array
    {
        $copied = 0;
        $skipped = 0;
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($from, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if (! $file->isFile()) {
                continue;
            }

            $relative = str_replace('\\', '/', substr($file->getPathname(), strlen($from) + 1));
            $relKey = $prefix.'/'.$relative;

            if ($this->isExcluded($relKey)) {
                $skipped++;

                continue;
            }

            $dest = $to.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relative);
            $destDir = dirname($dest);

            if (! $dryRun && ! is_dir($destDir)) {
                File::makeDirectory($destDir, 0755, true);
            }

            if (is_file($dest) && $this->filesEqual($file->getPathname(), $dest) && ! $this->option('force')) {
                $skipped++;

                continue;
            }

            if ($dryRun) {
                $copied++;
            } else {
                File::copy($file->getPathname(), $dest);
                $copied++;
            }
        }

        return [$copied, $skipped];
    }

    private function isExcluded(string $relativePath): bool
    {
        $relativePath = str_replace('\\', '/', $relativePath);

        foreach ($this->excludeRelative as $ex) {
            if ($relativePath === $ex || str_starts_with($relativePath, $ex.'/')) {
                return true;
            }
        }

        return false;
    }

    private function filesEqual(string $a, string $b): bool
    {
        if (! is_file($a) || ! is_file($b)) {
            return false;
        }

        return filesize($a) === filesize($b) && hash_file('xxh128', $a) === hash_file('xxh128', $b);
    }
}