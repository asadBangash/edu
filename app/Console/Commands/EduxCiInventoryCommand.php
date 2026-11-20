<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class EduxCiInventoryCommand extends Command
{
    protected $signature = 'edux:ci-inventory {--json=storage/app/edux-ci-inventory.json : Output path}';

    protected $description = 'Scan CodeIgniter controllers and report Laravel porting progress';

    public function handle(): int
    {
        $ciRoot = config('edux.codeigniter_path') ?: base_path('edux');
        $controllersDir = $ciRoot.DIRECTORY_SEPARATOR.'application'.DIRECTORY_SEPARATOR.'controllers';

        if (! is_dir($controllersDir)) {
            $this->error("Controllers directory not found: {$controllersDir}");

            return self::FAILURE;
        }

        $laravelRoutes = collect(\Illuminate\Support\Facades\Route::getRoutes())
            ->map(fn ($route) => trim($route->uri(), '/'))
            ->filter()
            ->values();

        $controllers = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($controllersDir));

        foreach ($iterator as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $relative = Str::after($file->getPathname(), $controllersDir.DIRECTORY_SEPARATOR);
            $relative = str_replace('\\', '/', $relative);
            $segments = explode('/', $relative);
            $classFile = array_pop($segments);
            $class = Str::before($classFile, '.php');
            $folder = implode('/', $segments);

            $uri = $folder !== '' ? strtolower($folder.'/'.$class) : strtolower($class);
            $uri = str_replace('_', '', $uri);

            $controllers[] = [
                'file' => str_replace('\\', '/', $file->getPathname()),
                'class' => $class,
                'folder' => $folder,
                'legacy_uri' => $uri,
                'ported' => $this->guessPorted($uri, $laravelRoutes),
            ];
        }

        usort($controllers, fn ($a, $b) => strcmp($a['legacy_uri'], $b['legacy_uri']));

        $total = count($controllers);
        $ported = count(array_filter($controllers, fn ($c) => $c['ported']));

        $payload = [
            'generated_at' => now()->toIso8601String(),
            'codeigniter_path' => $ciRoot,
            'total_controllers' => $total,
            'ported_estimate' => $ported,
            'controllers' => $controllers,
        ];

        $jsonPath = str_starts_with($this->option('json'), DIRECTORY_SEPARATOR)
            ? $this->option('json')
            : base_path($this->option('json'));
        File::ensureDirectoryExists(dirname($jsonPath));
        File::put($jsonPath, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $this->info("Controllers: {$total}");
        $this->info("Laravel route overlap (estimate): {$ported}");
        $this->info("Written: {$jsonPath}");

        return self::SUCCESS;
    }

    /**
     * @param  \Illuminate\Support\Collection<int, string>  $laravelRoutes
     */
    private function guessPorted(string $uri, $laravelRoutes): bool
    {
        $needles = [
            $uri,
            str_replace('admin/', 'admin/admin/', $uri),
        ];

        foreach ($needles as $needle) {
            if ($laravelRoutes->contains(fn ($r) => Str::startsWith($r, $needle))) {
                return true;
            }
        }

        return false;
    }
}