<?php

namespace App\Console\Commands;

use App\Services\EduxAssetSeeder;
use App\Support\SmartSchoolPaths;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class EduxDoctorCommand extends Command
{
    protected $signature = 'edux:doctor {--fix : Run edux:seed-assets when branding files are missing}';

    protected $description = 'Verify Smart School runtime (storage), bridge patches, and uploads';

    public function handle(EduxAssetSeeder $seeder): int
    {
        $failures = 0;
        $runtime = SmartSchoolPaths::runtimeRoot();
        $uploads = SmartSchoolPaths::uploadsRoot();
        $source = SmartSchoolPaths::sourceRoot();

        $failures += $this->checkPath($source, 'Licensed Smart School source (SMART_SCHOOL_SRC)');
        $failures += $this->checkPath($runtime, 'Materialized CI runtime');
        $failures += $this->checkPath($runtime.DIRECTORY_SEPARATOR.'index.php', 'runtime index.php');
        $failures += $this->checkPath($runtime.DIRECTORY_SEPARATOR.'backend', 'runtime/backend (admin assets)');
        $failures += $this->checkPath($runtime.DIRECTORY_SEPARATOR.'application', 'runtime/application');
        $failures += $this->checkPath($uploads, 'Writable school uploads');

        $config = $runtime.DIRECTORY_SEPARATOR.'application'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'config.php';
        if (is_file($config)) {
            $contents = file_get_contents($config);
            if (! str_contains($contents, 'EDUX_CI_SESSION_PATH')) {
                $this->error('Bridge patch missing in runtime config (EDUX_CI_SESSION_PATH). Re-run edux:materialize-runtime.');
                $failures++;
            } else {
                $this->info('OK: CodeIgniter config uses Laravel session bridge.');
            }
        }

        $omnipayVendor = $runtime.DIRECTORY_SEPARATOR.'application'.DIRECTORY_SEPARATOR.'third_party'.DIRECTORY_SEPARATOR.'omnipay'.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php';
        if (! is_file($omnipayVendor)) {
            $this->error('Missing omnipay vendor — run: composer install --working-dir='.$runtime.'/application/third_party/omnipay');
            $failures++;
        } else {
            $this->info('OK: Omnipay vendor present.');
        }

        $branding = SmartSchoolPaths::brandingDir();
        foreach (['edux-logo.png', 'edux-logo.svg', 'edux-favicon.svg'] as $logo) {
            $failures += $this->checkPath($branding.DIRECTORY_SEPARATOR.$logo, $logo, optional: true);
        }

        $adminLogo = $uploads.DIRECTORY_SEPARATOR.'school_content'.DIRECTORY_SEPARATOR.'admin_logo'.DIRECTORY_SEPARATOR.'edux-logo.svg';
        if (! is_file($adminLogo) && ! is_file($uploads.DIRECTORY_SEPARATOR.'school_content'.DIRECTORY_SEPARATOR.'admin_logo'.DIRECTORY_SEPARATOR.'edux-logo.png')) {
            $this->warn('Missing branded uploads (school_content/...).');
            $failures++;
            if ($this->option('fix')) {
                try {
                    $result = $seeder->seedAll();
                    $this->info('edux:seed-assets fixed '.$result['files'].' upload file(s).');
                    $failures = max(0, $failures - 1);
                } catch (\Throwable $e) {
                    $this->error($e->getMessage());
                }
            }
        } else {
            $this->info('OK: School upload logos present.');
        }

        $headerImage = $uploads.DIRECTORY_SEPARATOR.'print_headerfooter'.DIRECTORY_SEPARATOR.'general_purpose'.DIRECTORY_SEPARATOR.'header_image.jpg';
        if (! is_file($headerImage)) {
            $login = glob($uploads.DIRECTORY_SEPARATOR.'school_content'.DIRECTORY_SEPARATOR.'login_image'.DIRECTORY_SEPARATOR.'*.jpg') ?: [];
            if ($login !== []) {
                File::ensureDirectoryExists(dirname($headerImage));
                File::copy($login[0], $headerImage);
                $this->info('Created print header image from login background.');
            } else {
                $this->warn('No print_headerfooter/general_purpose/header_image.jpg (PDF export image may warn in console).');
            }
        }

        if (is_dir(base_path('edux'))) {
            $this->warn('Legacy edux/ folder still exists under the project root — safe to delete after materialize-runtime works.');
        }

        if ($failures === 0) {
            $this->info('Running smoke tests…');
            $code = Artisan::call('edux:smoke-test');
            $this->line(Artisan::output());
            if ($code !== 0) {
                $failures++;
            }
        }

        if ($failures > 0) {
            $this->newLine();
            $this->line('Remedy: php artisan edux:materialize-runtime');
            $this->line('         php artisan edux:seed-assets');
            $this->line('         composer install --working-dir='.$runtime.'/application/third_party/omnipay');

            return self::FAILURE;
        }

        $this->info('EduX environment looks complete.');

        return self::SUCCESS;
    }

    private function checkPath(string $path, string $label, bool $optional = false): int
    {
        if (is_file($path) || is_dir($path)) {
            $this->info('OK: '.$label);

            return 0;
        }

        if ($optional) {
            $this->warn('Optional missing: '.$label);

            return 0;
        }

        $this->error('Missing: '.$label.' ('.$path.')');

        return 1;
    }
}
