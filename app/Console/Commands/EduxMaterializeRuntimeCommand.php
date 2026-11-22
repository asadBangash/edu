<?php

namespace App\Console\Commands;

use App\Support\SmartSchoolPaths;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class EduxMaterializeRuntimeCommand extends Command
{
    protected $signature = 'edux:materialize-runtime
                            {--source= : Override licensed Smart School src path (read-only)}
                            {--project : Copy into project edux/ (for Git deploy) instead of storage/}
                            {--force : Re-copy even when runtime exists}';

    protected $description = 'Copy Smart School from SMART_SCHOOL_SRC into edux/ or storage. Does not modify the source folder.';

    /** @var list<string> */
    private array $syncDirs = ['application', 'backend', 'system'];

    /** @var list<string> */
    private array $syncFiles = ['index.php', '.htaccess'];

    public function handle(): int
    {
        $source = $this->option('source') ?: SmartSchoolPaths::sourceRoot();
        $legacyEdux = base_path('edux');
        $inProject = (bool) $this->option('project');

        if ($inProject) {
            $target = $legacyEdux;
            $uploads = $legacyEdux.DIRECTORY_SEPARATOR.'uploads';
        } else {
            $target = SmartSchoolPaths::runtimeRoot();
            $uploads = SmartSchoolPaths::uploadsRoot();

            if (realpath($target) !== false && realpath($target) === realpath($legacyEdux)) {
                $this->error('EDUX_CI_PATH must not point at project edux/. Run: php artisan config:clear');

                return self::FAILURE;
            }
        }

        if (! is_dir($source)) {
            $this->error("Source not found: {$source}");
            $this->line('Set SMART_SCHOOL_SRC in .env to your licensed package (e.g. E:\\school management ssyte\\smart_school_src).');

            return self::FAILURE;
        }

        if (is_dir($target) && ! $this->option('force') && is_file($target.DIRECTORY_SEPARATOR.'index.php')) {
            $this->warn("Target already exists: {$target}");
            $this->line('Use --force to refresh from source.');
        } else {
            if ($inProject) {
                File::ensureDirectoryExists($target);
            } elseif (is_dir($target)) {
                File::deleteDirectory($target);
                File::makeDirectory($target, 0755, true);
            } else {
                File::makeDirectory($target, 0755, true);
            }

            foreach ($this->syncDirs as $dir) {
                $from = $source.DIRECTORY_SEPARATOR.$dir;
                if (! is_dir($from)) {
                    $this->warn("Skip missing: {$dir}");

                    continue;
                }
                $this->mirrorDirectory($from, $target.DIRECTORY_SEPARATOR.$dir);
                $this->info("Copied {$dir}/");
            }

            foreach ($this->syncFiles as $file) {
                $from = $source.DIRECTORY_SEPARATOR.$file;
                if (is_file($from)) {
                    File::copy($from, $target.DIRECTORY_SEPARATOR.$file);
                }
            }
        }

        $this->applyPatches($target);
        $this->seedBrandingAssets($target);

        if (! is_dir($uploads)) {
            File::makeDirectory($uploads, 0755, true);
        }
        $srcUploads = $source.DIRECTORY_SEPARATOR.'uploads';
        if ($inProject && is_dir($srcUploads) && (count(glob($uploads.'/*') ?: []) === 0)) {
            $this->mirrorDirectory($srcUploads, $uploads);
            $this->info('Copied uploads/ into project edux/uploads (gitignored).');
        } elseif (! $inProject && count(glob($uploads.'/*') ?: []) === 0) {
            if (is_dir($legacyEdux.DIRECTORY_SEPARATOR.'uploads')) {
                $this->mirrorDirectory($legacyEdux.DIRECTORY_SEPARATOR.'uploads', $uploads);
                $this->info('Seeded writable uploads from legacy project edux/uploads.');
            } elseif (is_dir($srcUploads)) {
                $this->mirrorDirectory($srcUploads, $uploads);
                $this->info('Seeded writable uploads from source.');
            }
            $this->linkRuntimeUploads($target, $uploads);
        }

        $omnipayDir = $target.DIRECTORY_SEPARATOR.'application'.DIRECTORY_SEPARATOR.'third_party'.DIRECTORY_SEPARATOR.'omnipay';
        if (is_dir($omnipayDir) && ! is_file($omnipayDir.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php')) {
            $this->warn('Run: composer install --working-dir='.$omnipayDir);
        }

        $this->line('Install omnipay: composer install --working-dir='.$omnipayDir);
        if ($inProject) {
            $this->call('edux:sanitize-secrets', ['--edux' => $target]);
        }

        $this->info("Smart School ready at {$target}");
        if ($inProject) {
            $this->line('Set EDUX_CI_PATH or remove it — config auto-uses edux/ when present. Clear config cache after deploy.');
        }

        return self::SUCCESS;
    }

    private function applyPatches(string $target): void
    {
        $patchRoot = SmartSchoolPaths::patchesRoot();
        if (! is_dir($patchRoot)) {
            $this->warn('No Laravel patch set in resources/smart-school/patches.');

            return;
        }

        $count = 0;
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($patchRoot, \FilesystemIterator::SKIP_DOTS)
        );
        foreach ($iterator as $file) {
            if (! $file->isFile()) {
                continue;
            }
            $relative = str_replace('\\', '/', substr($file->getPathname(), strlen($patchRoot) + 1));
            $dest = $target.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relative);
            File::ensureDirectoryExists(dirname($dest));
            File::copy($file->getPathname(), $dest);
            $count++;
        }
        $this->info("Applied {$count} Laravel bridge patch file(s).");
    }

    private function linkRuntimeUploads(string $runtime, string $uploads): void
    {
        $link = $runtime.DIRECTORY_SEPARATOR.'uploads';
        if (file_exists($link)) {
            return;
        }

        if (PHP_OS_FAMILY === 'Windows') {
            $command = 'cmd /C mklink /J '.escapeshellarg($link).' '.escapeshellarg($uploads);
            exec($command, $output, $code);
            if ($code === 0) {
                $this->info('Linked runtime/uploads → school-uploads (junction).');

                return;
            }
        }

        if (@symlink($uploads, $link)) {
            $this->info('Linked runtime/uploads → school-uploads (symlink).');

            return;
        }

        if (! is_dir($link)) {
            $this->mirrorDirectory($uploads, $link);
            $this->warn('Copied uploads into runtime (could not symlink). Prefer junction on Windows.');
        }
    }

    private function seedBrandingAssets(string $target): void
    {
        $publicBrand = public_path('backend'.DIRECTORY_SEPARATOR.'img'.DIRECTORY_SEPARATOR.'edux');
        if (! is_dir($publicBrand)) {
            return;
        }

        $dest = $target.DIRECTORY_SEPARATOR.'backend'.DIRECTORY_SEPARATOR.'img'.DIRECTORY_SEPARATOR.'edux';
        $this->mirrorDirectory($publicBrand, $dest);
        $this->info('Copied EduX branding into runtime backend/img/edux.');
    }

    private function mirrorDirectory(string $from, string $to): void
    {
        if (! is_dir($to)) {
            File::makeDirectory($to, 0755, true);
        }

        if (PHP_OS_FAMILY === 'Windows') {
            $command = 'robocopy '.escapeshellarg($from).' '.escapeshellarg($to)
                .' /E /NFL /NDL /NJH /NJS /nc /ns /np /R:1 /W:1';
            exec($command, $output, $code);
            if ($code >= 8) {
                throw new \RuntimeException("Failed to copy {$from} → {$to} (robocopy exit {$code})");
            }

            return;
        }

        File::copyDirectory($from, $to);
    }
}
