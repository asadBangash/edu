<?php

namespace App\Console\Commands;

use App\Services\EduxAssetSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class EduxSeedPakistanDemoCommand extends Command
{
    protected $signature = 'edux:seed-pakistan-demo';

    protected $description = 'Pakistan (Peshawar) demo data, dashboard charts, and EduX School System branding';

    private const SCHOOL_NAME = 'EduX School System';

    private const SCHOOL_FULL = 'EduX School System — Peshawar Campus';

    public function handle(): int
    {
        $database = config('database.connections.mysql.database');
        if (! $database) {
            $this->error('Configure DB_DATABASE in .env first.');

            return self::FAILURE;
        }

        $this->applyBranding();
        app(EduxAssetSeeder::class)->seedAll();

        putenv('EDUX_SEED_DATABASE='.$database);

        $script = resource_path('smart-school/scripts/edux_dashboard_dummy_data.php');
        if (! is_file($script)) {
            $this->warn('Skipping chart seed — missing '.$script);
            $this->info('Branding and uploads were applied.');

            return self::SUCCESS;

            return self::FAILURE;
        }

        $this->info('Seeding charts, fees, attendance, and enquiries…');
        require $script;

        $this->info('Done. Reload '.url('admin/admin/dashboard'));

        return self::SUCCESS;
    }

    private function applyBranding(): void
    {
        $baseUrl = rtrim((string) config('app.url'), '/').'/';

        DB::table('sch_settings')->where('id', 1)->update([
            'name' => self::SCHOOL_FULL,
            'address' => 'University Town, Peshawar, Khyber Pakhtunkhwa 25120, Pakistan',
            'currency' => 116,
            'currency_symbol' => 'Rs',
            'currency_format' => '####.##',
            'base_url' => $baseUrl,
        ]);

        if (DB::getSchemaBuilder()->hasTable('front_cms_settings')) {
            DB::table('front_cms_settings')->where('id', 1)->update([
                'footer_text' => self::SCHOOL_NAME.' © '.date('Y').' — Peshawar, Pakistan. Powered by Global TechX Solutions.',
            ]);
        }

        $this->info('Branding set to '.self::SCHOOL_NAME);
    }
}
